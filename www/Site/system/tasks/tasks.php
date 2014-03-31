<?php
/**
 * Задачи
 * Планировщик задач. Выполняет в фоновом режиме задачи из списка.
 * @version 1.0
 */
namespace Site\system\tasks;

use Boolive\errors\Error;
use Boolive\functions\F;
use Boolive\values\Rule;
use Site\library\views\View\View;

class tasks extends View
{
    const CONFIG_FILE = 'config.tasks.php';
    const LOCK_FILE = 'lock.task.txt';
    const STATUS_WAIT = 0;
    const STATUS_PROCESS = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS = 3;
    const execute_time_limit = 60;
    private $_lock = false;

    function __destruct()
    {
        $this->unlock();
    }

    /**
     * Правило на входящие данные - условие работы
     * @return null|\Boolive\values\Rule
     */
    function startRule()
    {
        return Rule::arrays(array(
            'ARG' => Rule::arrays(array(
                'tasks' => Rule::int(),
            ))
        ));
    }

    function work()
    {
        if (isset($this->_input['ARG']['tasks'])){
            $this->execute();
            return true;
        }else{
            $this->executeBackground();
            return false;
        }
    }

    /**
     * Выполнение задач
     */
    private function execute()
    {
        if (!$this->is_lock()){
            $this->lock();
            $start = time();
            while (time() - $start < self::execute_time_limit * 2){
                // Выбор задач из очереди
                $tasks = $this->find(array(
                    'where' => array(
                        array('attr', 'is_property', '=', 0),
                        array('attr', 'value', '=', self::STATUS_WAIT)
                    ),
                    'limit' => array(0,3),
                    'cache' => 0
                ));
                foreach ($tasks as $task){
                    /** @var View $task */
                    try{
                        $task->value(self::STATUS_PROCESS);
                        $task->save(false, false);
                        $task->start($this->_commands, $this->_input_child);
                        $task->value(self::STATUS_SUCCESS);
                        $task->report->value('Успешно выполнена');
                    }catch (Error $e){
                        $task->value(self::STATUS_ERROR);
                        $task->report->value(F::toJSON($e->toArrayCompact(), true));
                    }catch (\Exception $e){
                        $task->value(self::STATUS_ERROR);
                        $task->report->value($e->getMessage().' '.$e->getFile().' '.$e->getLine());
                    }
                    $task->save(true, false);
                }
                if (count($tasks) == 0) $start = 0;
            }
            $this->unlock();
        }
    }

    /**
     * Фоновый запуск выполнения задач
     */
    private function executeBackground()
    {
        if (!(/*$this->only_cron->value() || */$this->is_lock())){
            if (substr(php_uname(), 0, 7) == "Windows"){
                pclose(popen("start /B ".PHP.' '.DIR_SERVER.'index.php tasks', "r"));
            }else{
                exec(PHP.' '.DIR_SERVER."index.php tasks > /dev/null &");
            }
        }
    }

    /**
     * Проверка возможности запуска обработчика задач
     */
    private function is_lock()
    {
        if ($this->_lock) return true;
        return (is_file(DIR_SERVER_TEMP.self::LOCK_FILE));
    }

    /**
     * Блокировка запуска обработчика задач
     */
    private function lock()
    {
        if (!$this->_lock && !self::is_lock()){
            $f = fopen(DIR_SERVER_TEMP.self::LOCK_FILE, 'w+');
            fwrite($f, time());
            fclose($f);
            $this->_lock = true;
        }
    }

    /**
     * Разблокировка запуска обработчика задач
     */
    private function unlock()
    {
        if ($this->_lock){
            unlink(DIR_SERVER_TEMP.self::LOCK_FILE);
            clearstatcache(DIR_SERVER_TEMP, self::LOCK_FILE);
            $this->_lock = false;
        }
    }
}