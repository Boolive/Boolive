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
 * // создание команды htmlHead с двумя аргументами
 * $c->htmlHead('link', array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'style.css'));
 * @link http://boolive.ru/createcms/processing-request
 * @version 1.0
 */
namespace Boolive\commands;
/**
 * @method null redirect($url) HTTP редирект на указанный http url адрес
 * @method null htmlHead($tag, $args = array(), $unique = false) Добавление тега в &lt;head&gt; Содержимое тега указывается аргументом "text"
 */
class Commands
{
    /**
     * @var array Ассоциативный массив команд
     * Первое измерение (ассоциативное) - название команд, второе (числовое) - команды, третье - аргументы команд
     */
    private $commands = array();

    private $groups = array();

    /**
     * Создание команды через вызов одноименной функции.
     * @param string $name Название команды
     * @param array $args аргументы команды
     * @return void
     */
    function __call($name, $args)
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
    function set($name, $args, $push = true)
    {
        if (!isset($this->commands[$name])){
            $this->commands[$name] = array();
        }
        if ($push){
            $this->commands[$name][] = $args;
        }else{
            array_unshift($this->commands[$name], $args);
        }
        // Добавление команды в группы
        foreach ($this->groups as $key => $g){
            $this->groups[$key][] = array($name, $args, $push);
        }
    }

    /**
     * Добавление списка команд
     * @param array $list Массив команд
     */
    function setList($list)
    {
        foreach ($list as $com) $this->set($com[0], $com[1], $com[2]);
    }

    /**
     * Выбор команд по имени.
     * Под одним именем может быть несколько команд
     * @param string $name Название команды
     * @param bool $unique
     * @return array Массив одноименных команд с их аргументами. Первое измерение (числовое) соответсвует командам, второе - аргументам
     */
    function get($name, $unique = true)
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
    function getAll()
    {
        return $this->commands;
    }

    /**
     * Удаление команд с соответствующим названием
     * @param string $name Название команды
     * @return void
     */
    function clear($name)
    {
        unset($this->commands[$name]);
    }

    /**
     * Удаление всех команд
     * @return void
     */
    function clearAll()
    {
        $this->commands = array();
    }

    /**
     * Проверка существования команды
     * @param string $name Название команды
     * @return bool
     */
    function isExist($name = null)
    {
        if (empty($name)){
            return !empty($this->commands);
        }
        return !empty($this->commands[$name]);
    }

    function group($name)
    {
        $this->groups[$name] = array();
    }

    function ungroup($name)
    {
        unset($this->groups[$name]);
    }

    function getGroup($name)
    {
        return $this->groups[$name];
    }
}
