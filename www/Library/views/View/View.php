<?php
/**
 * Вид
 * Базовый объект для создания элементов интерфейса
 *
 * @version 1.0
 */
namespace Library\views\View;

use Boolive\data\Entity,
    Boolive\commands\Commands,
    Boolive\values\Check,
    Boolive\values\Rule,
    Boolive\input\Input;

class View extends Entity
{
    /**
     * Отфильтрованные входящих данных.
     * Инициализируется в методе start()
     * В качестве правила по умолчанию используется $this->getInputRule()
     * @var mixed
     */
    protected $_input;
    /**
     * Ошибки при проверки входящих данных
     * @var \Boolive\errors\Error
     */
    protected $_input_error;
    /**
     * Команды, передающиеся по всем исполняемым объектам.
     * Инициализируется в методе start()
     * @var \Boolive\commands\Commands
     */
    protected $_commands;
    /**
     * Входящие данные для подчиенных объектов
     * @var mixed
     */
    protected $_input_child;

    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    public function getInputRule()
    {
        return Rule::any();
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
        // Команды и входящие данные запоминаем, чтобы использовать их и передавать подчиненным по требованию
        $this->_commands = $commands;
        $this->initInput($input);
        //Проверка возможности работы
        if ($this->canWork()){
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
        }else{
            $result = null;
        }
        $this->_input = null;
        $this->_commands = null;
        return $result;
    }

    /**
     * Проверка возможности работы.
     * По умолчанию проверяются отсутствие ошибок во входящих данных по правилу на входящие данные
     * @return bool Признак, может ли работать объект или нет
     */
    public function canWork()
    {
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
        if ($result = $this->{$name}->start($this->_commands, $this->_input_child)){
            $this->_input_child['previous'] = true;
        }
        return $result;
    }

    /**
     * Запуск всех подчиненных объектов
     * @return array Результаты подчиненных объектов. Ключи массива - названия объектов.
     */
    public function startChildren()
    {
        $result = array();
        $list = $this->findAll2(array(
            'where' => array(
                array('attr', 'is_history', '=', 0),
                array('attr', 'is_delete', '=', 0)
            ),
            'order' => array(
                array('order', 'ASC')
            )
        ));
        foreach ($list as $key => $child){
            /** @var $child \Boolive\data\Entity */
            $out = $child->start($this->_commands, $this->_input_child);
            if ($out){
                $result[$key] = $out;
                $this->_input_child['previous'] = true;
            }
        }
        return $result;
    }
}