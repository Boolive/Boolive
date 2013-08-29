<?php
/**
 * Вид
 * Базовый объект для создания элементов интерфейса
 *
 * @version 1.0
 */
namespace Library\views\View;

use Boolive\cache\Cache;
use Boolive\data\Entity,
    Boolive\commands\Commands,
    Boolive\values\Check,
    Boolive\values\Rule,
    Boolive\input\Input;
use Boolive\develop\Benchmark;
use Boolive\develop\Trace;

class View extends Entity
{
    protected $_is_init = false;
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

    public function defineInputRule()
    {
        $this->_input_rule = Rule::null()->ignore('null');
    }

    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    public function getInputRule()
    {
        if (!isset($this->_input_rule)) $this->defineInputRule();
        return $this->_input_rule;
    }

    /**
     * Фильтр и установка входящих данных
     * @param $input Неотфильтрованные данные
     * @return mixed Отфильтрованные данные
     */
    protected function initInput($input)
    {
        $this->_input = Check::filter($input, $this->getInputRule(), $this->_input_error);
    }

    /**
     * Фильтр и установка входящих данных для подчиненных объектов
     * @param $input Неотфильтрованные данные
     * @return mixed
     */
    protected function initInputChild($input)
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
    public function start(Commands $commands, $input)
    {
//        $key = $this->uri().':'.microtime().rand();
//        Trace::groups('VIEWS')->group($key)->set(0);
//        Benchmark::start($key);
        //Проверка возможности работы
        if ($this->canWork($commands, $input)){

            if ($this->cache->value()){
                $key = 'views'.$this->uri().'/'.$this->date().'&'.md5(Cache::getId($this->_input));
                if ($result = Cache::get($key)){
                    $result = json_decode($result, true);
                    $commands->setList($result[1]);
                    return $result[0];
                }
                $commands->group($key);
            }

            $this->initInputChild($input);
            //Выполнение подчиненных
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

//        $this->_children = array();
        //$this->_commands = null;
//        $this->_input = null;
//        $this->_input_child = null;
//        $this->_is_init = false;
//        $this->_rule = null;
//        $this->_parent = null;
//        $this->_proto = null;
//        Trace::groups('VIEWS')->group($key)->set(Benchmark::stop($key, true));
        return $result;
    }

    /**
     * Проверка возможности работы.
     * По умолчанию проверяются отсутствие ошибок во входящих данных по правилу на входящие данные
     * @param \Boolive\commands\Commands $commands Входящие и исходящие команды
     * @param \Boolive\input\Input $input Входящие данные
     * @return bool Признак, может ли работать вид или нет
     */
    public function canWork(Commands $commands, $input)
    {
        // Инициализация
        if (!$this->_is_init){
            $this->init();
            $this->_is_init = true;
        }
        // Команды и входящие данные запоминаем, чтобы использовать их и передавать подчиненным по требованию
        $this->_commands = $commands;
        $this->initInput($input);
        // Может работать, если нет ошибок во входящих данных
        return !isset($this->_input_error);
    }

    /**
     * Работа.
     * Обработка запроса и формирование вывода.
     * Результат выводится функциями echo, print или возвращается через return
     * @return string|void Результат работы. Вместо return можно использовать вывод строк (echo, print,...)
     */
    public function work(){}

    /**
     * Запуск подчиненного по имени
     * @param $name Имя подчиненного
     * @return null|string
     */
    public function startChild($name)
    {
        $child = $this->linked(false)->{$name}->linked(true);
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
    public function startChildren($all = true, $result = array())
    {
        $list = $this->linked(false)->find(array('key'=>'name', 'comment' => 'read views for startChildren'));
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

    public function exportedProperties()
    {
        $names = parent::exportedProperties();
        $names[] = 'cache';
        return $names;
    }
}