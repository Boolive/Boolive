<?php
/**
 * Класс отложенного вызова методов
 * Используется для линейности алгоритмов, действия которых должны быть отменены в случаи исключений
 * Особенности:
 * 1. Отложенный вызов добавленных в очередь методов
 * 2. Многоуровневая очередь
 * 3. Исполенние после подтверждения первого уровня. При этом могут быть отменены вложенные уровни
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\calls;

use Boolive\events\Events;

class Calls
{
    /** @var Многомерная очередь с информацией для вызова методов */
    private static $commands = array();
    /** @var int Текущий уровень ожидания исполнения */
    private static $level = 0;

    /**
     * Активация
     */
    static function activate()
    {
        // Регистрация на событие системной ошибки
        Events::addHandler('error', '\\Boolive\\calls\\Calls', 'onError');
    }
    /**
     * Перовод в состояние ожидания.
     * Создание нового уровня вложенности очереди
     * Если вызывает первый раз или после полной отмены, то создаётся первый уровень очереди
     */
    static function wait()
    {
        self::$level++;
        if (!isset(self::$commands[self::$level])){
            self::$commands[self::$level] = array();
        }
    }

    /**
     * Подтверждение текущего уровня очереди на исполнение
     * Выполнение происходит, если подтвердится первый уровень очереди
     */
    static function commit()
    {
        if (self::$level > 0){
            self::$level--;
            if (self::$level == 0){
                // Выполнение очереди команд
                foreach (self::$commands as $commands){
                    foreach ($commands as $com){
                        call_user_func_array($com[0], $com[1]);
                    }
                }
                self::$commands = array();
            }
        }
    }

    /**
     * Отмена исполнения текущей и всех вложенных очередей
     */
    static function cancel()
    {
        if (self::$level > 0){
            self::$level--;
            // Отмена очереди команд
            array_splice(self::$commands, self::$level);
        }
    }

    /**
     * Проверка, включено ли ожидание
     * @return bool
     */
    static function isWait()
    {
        return self::$level > 0;
    }

    /**
     * Добавление функции в текущую очередь на выполнение
     * Если очереди нет, то функция сразу исполняется
     * @param array $callback Имя функции или массив из имени класса или объекта и именем метода
     * @param array $args Значения аргументов метода
     * @return mixed
     */
    static function pull($callback, $args)
    {
        if (self::$level > 0){
            self::$commands[self::$level][] = array($callback, $args);
            return true;
        }else{
            return call_user_func_array($callback, $args);
        }
    }

    /**
     * Добавление метода в текущую очередь на выполнение
     * Если очереди нет, то метод сразу исполняется
     * @param $class_or_object Имя класса или объект
     * @param $method Имя метода
     * @param array $args Значения аргументов метода
     * @return mixed
     */
    static function pullMethod($class_or_object, $method, $args)
    {
        return self::pull(array($class_or_object, $method), $args);
    }

    /**
     * Обработчик системной ошибки.
     * Отмена всех очередей
     */
    static function onError()
    {
        self::$level = 0;
        self::$commands = array();
    }
}