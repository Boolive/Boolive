<?php
/**
 * Класс отложенного вызова методов
 * Используется для линейности алгоритмов, действия которых должны быть отменены в случаи исключений
 * Особенности:
 * 1. Отложенный вызов добавленных в очередь методов
 * 2. Многоуровневая очередь
 * 3. Исполенние после подверждения первого уровня. При этом могут быть отменены вложенные уровни
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Events;

class Calls {
	/** @var Многомерная очередь с информацией для вызова методов */
	private static $commands = array();
	/** @var int Текущий уровень ожидания исполнения */
	private static $level = 0;

	/**
	 * Активация
	 */
	static function Activate(){
		// Регистрация на событие системной ошибки
		Events::AddHandler('ERRORS_SYSTEM', '\\Engine\\Calls', 'OnError');
	}
	/**
	 * Перовод в состояние ожидания.
	 * Создание нового уровня вложенности очереди
	 * Если вызывает первый раз или после полной отмены, то создаётся первый уровень очереди
	 */
	static function Wait(){
		self::$level++;
		if (!isset(self::$commands[self::$level])){
			self::$commands[self::$level] = array();
		}
	}

	/**
	 * Подтверждение текущего уровня очереди на исполнение
	 * Выполнение происходит, если подтвердится первый уровень очереди
	 */
	static function Commit(){
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
	static function Cancel(){
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
	static function IsWait(){
		return self::$level > 0;
	}

	/**
	 * Размещение метода в очередь на выполнение в текущей транзакции
	 * Если транзакций нет, то возвращается false
	 * @param $class_or_object
	 * @param $method
	 * @param array $args Значения аргументов метода
	 * @internal param string $name Имя метода класса File
	 * @return bool
	 */
	static function Pull($class_or_object, $method, $args){
		if (self::$level > 0){
			self::$commands[self::$level][] = array(array($class_or_object, $method), $args);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Обработчик системной ошибки.
	 * Отмена всех очередей
	 */
	static function OnError(){
		self::$level = 0;
		self::$commands = array();
	}
}
