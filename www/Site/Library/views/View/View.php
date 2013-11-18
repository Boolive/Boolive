<?php
/**
 * Вид
 * Базовый объект для создания элементов интерфейса
 *
 * @version 1.0
 */
namespace Library\views\View;

use Boolive\cache\Cache,
    Boolive\data\Entity,
    Boolive\commands\Commands,
    Boolive\values\Check,
    Boolive\values\Rule;

class View extends Entity
{
    protected $_is_init = false;
    /** @var  array Все входящие неотфильтрованные! данные. Используются для запуска подчиненных видов */
    protected $_input_all;
    /** @var mixed Отфильтрованные входящих данных по правилу на входящие данные */
    protected $_input;
    /** @var Rule Правило на входящие данные */
    protected $_input_rule;
    /** @var \Boolive\errors\Error Ошибки при проверки входящих данных */
    protected $_input_error;
    /** @var mixed Входящие данные для подчиненных объектов */
    protected $_input_child;
    /** @var \Boolive\commands\Commands Команды, передающиеся по всем исполняемым объектам. Инициализируется в методе start() */
    protected $_commands;

    /**
     * Инициализация
     */
    protected function init()
    {

    }

    function startRule()
    {
        return Rule::null()->ignore('null');
    }

    /**
     * Фильтр и установка входящих данных
     * @param $input Неотфильтрованные данные
     * @return mixed Отфильтрованные данные
     */
    function startInit($input)
    {
        $this->_input_all = $input;
        if (!$this->_input_rule) $this->_input_rule = $this->startRule();
        $this->_input = Check::filter($input, $this->_input_rule, $this->_input_error);
    }

    /**
     * Фильтр и установка входящих данных для подчиненных объектов
     * @param $input Неотфильтрованные данные
     * @return mixed
     */
    function startInitChild($input)
    {
        $this->_input_child = $input;
        $this->_input_child['previous'] = false;
    }

    /**
     * Запуск для обработки запроса и формирования ответа (вида)
     * @param \Boolive\commands\Commands $commands Команды для исполнения в соответствующих сущностях
     * @param mixed $input Входящие данные
     * @return null|string Результат выполнения контроллера
     */
    function start(Commands $commands, $input)
    {
        //Проверка возможности работы
        if ($this->startCheck($commands, $input)){

            if ($this->cache->value()){
                $key = 'views'.$this->uri().'/'.$this->date().'&'.md5(Cache::getId($this->_input));
                if ($result = Cache::get($key)){
                    $result = json_decode($result, true);
                    $commands->setList($result[1]);
                    return $result[0];
                }
                $commands->group($key);
            }

            $this->startInitChild($input);
            ob_start();
                // Выполнение своей работы
                $result = $this->work();
                if (!($result === false || is_array($result))){
                    $result = ob_get_contents().$result;
                }
            ob_end_clean();
            $this->_input_child = null;
            if (isset($key)){
                Cache::set($key, json_encode(array($result, $commands->getGroup($key))));
                $commands->ungroup($key);
            }
        }else{
            $result = false;
        }
        return $result;
    }

    /**
     * Проверка возможности работы.
     * По умолчанию проверяются отсутствие ошибок во входящих данных по правилу на входящие данные
     * @param \Boolive\commands\Commands $commands Входящие и исходящие команды
     * @param \Boolive\input\Input $input Входящие данные
     * @return bool Признак, может ли работать вид или нет
     */
    function startCheck(Commands $commands, $input)
    {
        // Инициализация
        if (!$this->_is_init){
            $this->init();
            $this->_is_init = true;
        }
        // Команды и входящие данные запоминаем, чтобы использовать их и передавать подчиненным по требованию
        $this->_commands = $commands;
        $this->startInit($input);
        // Может работать, если нет ошибок во входящих данных
        return !isset($this->_input_error);
    }

    /**
     * Работа.
     * Обработка запроса и формирование вывода.
     * @return string|void Результат работы. Вместо return можно использовать вывод строк (echo, print,...)
     */
    function work(){}

    /**
     * Запуск подчиненного по имени
     * @param $name Имя подчиненного
     * @return null|string
     */
    function startChild($name)
    {
        $child = $this->{$name}->linked(true);
        if ($child instanceof View){
            $result = $child->start($this->_commands, $this->_input_child);
            if ($result!==false){
                $this->_input_child['previous'] = true;
            }
            return $result;
        }else{
            return null;
        }
    }

    /**
     * Запуск всех подчиненных объектов
     * @param bool $all Признак, запускать все подчиенные (true), или пока не возвратится результат от одного из запущенных (false)
     * @param array $result Значения-заглушки для подчиненных видов. Если в массиве есть ключ с именем вида, то этот вид не исполняется, а испольщуется указанное в элементе значение.
     * @return array Результаты подчиненных объектов. Ключи массива - названия объектов.
     */
    function startChildren($all = true, $result = array())
    {
        $list = $this->find(array('key'=>'name', 'comment' => 'read views for startChildren'));
        foreach ($list as $key => $child){
            /** @var $child \Boolive\data\Entity */
            $child = $child->linked(true);
            if ($child instanceof View){
                if (!isset($result[$key])){
                    $out = $child->start($this->_commands, $this->_input_child);
                    if ($out!==false){
                        $result[$key] = $out;
                        $this->_input_child['previous'] = true;
                        if (!$all) return $result;
                    }
                }
            }
        }
        return $result;
    }

    function exportedProperties()
    {
        $names = parent::exportedProperties();
        $names[] = 'cache';
        return $names;
    }
}