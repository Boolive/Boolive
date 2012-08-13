<?php
/**
 * Класс команд.
 * Команда имеет название и аргументы.
 * Команды используются для отложенного вызова соответсвующих функций/методов, при этом не указывается,
 * какая имено функция и у какого класса должна быть выполнена. Когда управление доходит до опреденных классов или
 * объектов и им передается список команд, их задачей становится исполнение тех команд, которые они понимают и считают
 * нужными исполнить.
 * Используется в контроллерах для распредления обработки запросов и подготовик результата (вывода).
 *
 * @example
 * $c = new Commands();
 * // создание команды addHtml с двумя аргументами
 * $c->addHtml('link', array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'style.css'));
 * @link http://boolive.ru/createcms/processing-request
 * @version 1.0
 */
namespace Boolive\commands;

class Commands
{
    /**
     * @var array Ассоциативный массив команд
     * Первое измерение (ассоциативное) - название команд, второе (числовое) - команды, третье - аргументы команд
     */
    private $commands = array();

    /**
     * Создание команды через вызов одноименной функции.
     * @param string $name Название команды
     * @param array $args аргументы команды
     * @return void
     */
    public function __call($name, $args)
    {
        $this->set($name, $args);
    }

    /**
     * Создание команды
     * @param string $name Имя команды
     * @param array $args Аргументы команды
     * @param bool $push Признак, добавлять команду в конец очереди (true) или в начало (false)
     * @return void
     */
    public function set($name, $args, $push = true)
    {
        if (!isset($this->commands[$name])){
            $this->commands[$name] = array();
        }
        if ($push){
            $this->commands[$name][] = $args;
        }else{
            array_unshift($this->commands[$name], $args);
        }

    }

    /**
     * Выбор команд по имени.
     * Под одним именем может быть несколько команд
     * @param $name Название команды
     * @param bool $unique
     * @return array Аргументы команд. Первое измерение (числовое) соответсвует командам, второе - аргументам
     */
    public function get($name, $unique = true)
    {
        if (!isset($this->commands[$name])){
            $this->commands[$name] = array();
        }
        if ($unique){
            $keys = array();
            $result = array();
            foreach ($this->commands[$name] as $com){
                $key = serialize($com);
                if (!isset($keys[$key])){
                    $result[] = $com;
                    $keys[$key] = true;
                }
            }
            unset($keys);
            return $result;
        }
        return $this->commands[$name];
    }

    /**
     * Выбор всех команд
     * @return array Команды и их аргументы.
     * Первое измерение (ассоциативное) - название команд, второе (числовое) - команды, третье - аргументы команд
     */
    public function getAll()
    {
        return $this->commands;
    }

    /**
     * Удаление команд с соответствующим названием
     * @param $name Название команды
     * @return void
     */
    public function clear($name)
    {
        unset($this->commands[$name]);
    }

    /**
     * Удаление всех команд
     * @return void
     */
    public function clearAll()
    {
        $this->commands = array();
    }

    /**
     * Проверка существования команды
     * @param string $name Название команды
     * @return bool
     */
    public function isExist($name = null)
    {
        if (empty($name)){
            return !empty($this->commands);
        }
        return !empty($this->commands[$name]);
    }
}
