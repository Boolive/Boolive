<?php
/**
 * Модуль управления задачами
 * Реализует очередь задач и их обработку
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\tasks;

use Boolive\data\Data;
use Boolive\database\DB;
use Boolive\errors\Error;
use Boolive\events\Events;
use Boolive\functions\F;

class Tasks
{
    const CONFIG_FILE = 'config.tasks.php';
    const LOCK_FILE = 'lock.task.txt';
    const STATUS_WAIT = 0;
    const STATUS_PROCESS = 1;
    const STATUS_ERROR = 2;
    const STATUS_SUCCESS = 3;
    /** @var DB */
    static private $db;
    static private $config;
    static private $lock = false;

    static function activate()
    {
        self::$config = F::loadConfig(DIR_SERVER.self::CONFIG_FILE);
        self::$db = DB::connect(self::$config['db']);
        Events::on('Boolive::deactivate', '\\Boolive\\tasks\\Tasks', 'deactivate');
    }

    static function deactivate()
    {
        self::unlock();
    }

    /**
     * Добавление задачи в очередь
     * @param int $priority Приоритет задачи. Чем меньше значение, тем важнее задача
     * @param string $handler Строка, по которой будет определяться обработчик
     * @param string $group Группа задач
     * @param string $title Заголовок задачи
     * @param array $params Параметры задачи, используемые в обработчике
     * @return int Идентификатор задачи
     */
    static function add($priority, $handler, $group, $title, $params = array())
    {
        self::$db->beginTransaction();
        $q = self::$db->prepare('
            INSERT INTO {tasks} (`id`, `priority`, `status`, `handler`, `group`, `title`, `params`)
            VALUES (null, ?, 0, ?, ?, ?, ?)');
        $q->execute(array($priority, $handler, $group, $title, json_encode($params)));
        self::$db->commit();
        return intval(self::$db->lastInsertId());
    }

    static function find($handler = null, $group = null, $status = null, $start = 0, $count = null)
    {
        $limit = $count > 0 ? ' LIMIT '.intval($start).', '.intval($count) : '';
        $where = '';
        $binds = array();
        if (isset($status)){
            $where.= '`status` = :s';
            $binds[':s'] = intval($status);
        }
        if (isset($handler)){
            if ($where) $where.=' AND ';
            $where.= '`handler` LIKE :h';
            $binds[':h'] = $handler;
        }
        if (isset($group)){
            if ($where) $where.=' AND ';
            $where.= '`group` LIKE :g';
            $binds[':g'] = $group;
        }
        if ($where) $where = 'WHERE '.$where;
        $q = self::$db->prepare("SELECT {tasks}.* FROM {tasks} $where ORDER BY `priority` ASC $limit");
        $q->execute($binds);
        return $q->fetchAll(DB::FETCH_ASSOC);
    }

    static function report($id, $message)
    {
        $q = self::$db->prepare('UPDATE {tasks} SET report = ? WHERE id = ?');
        $q->execute(array($message, $id));
    }

    static function percent($id, $percent)
    {
        $q = self::$db->prepare('UPDATE {tasks} SET percent = ? WHERE id = ?');
        $q->execute(array(intval($percent), $id));
    }

    static function clear($id = null, $handler = null, $group = null, $status = array(self::STATUS_SUCCESS, self::STATUS_ERROR))
    {
        $binds = array();
        $where = '';
        if (isset($id)){
            $where.=' `id` = ?';
            $binds[] = $id;
        }else
        if (isset($handler)){
            $where.=' `handler` LIKE ?';
            $binds[] = $handler;
            if (isset($group)){
                $where.=' AND `group` LIKE ?';
                $binds[] = $group;
            }
            if (!empty($status)){
                $where.=' AND `status` IN ('.rtrim(str_repeat('?,',count($status)),',').')';
                $binds = array_merge($binds, $status);
            }
        }
        $q = self::$db->prepare('DELETE FROM {tasks} WHERE '.$where);
        $q->execute($binds);
        return true;
    }

    /**
     * Выполнение задач из очереди
     */
    static function execute($x = '')
    {
        if (!self::is_lock()){
            self::lock();
            $start = time();
            while (time() - $start < self::$config['execute_time_limit']*2){
                // Выбор задач по приоритету
                $q = self::$db->query('SELECT {tasks}.* FROM {tasks} WHERE status = 0 ORDER BY `priority` ASC LIMIT 0, 3');
                $u = self::$db->prepare('UPDATE {tasks} SET status = ? WHERE id = ?');
                $tasks = $q->fetchAll(DB::FETCH_ASSOC);
                foreach ($tasks as $task){
                    $u->execute(array(self::STATUS_PROCESS, $task['id']));
                    try{
                        // Обработка задачи
                        $handler = explode('::', $task['handler']);
                        if (isset($handler[0])){
                            $obj = Data::read($handler[0]);
                        }
                        if (!isset($handler[1])) $handler[1] = 'task';
                        if (isset($obj) && $obj->isExist() && method_exists($obj, $handler[1])){
                            $obj->$handler[1]($task['id'], json_decode($task['params'], true));
                        }
                        $u->execute(array(self::STATUS_SUCCESS, $task['id']));
                    }catch (Error $e){
                        $u->execute(array(self::STATUS_ERROR, $task['id']));
                        self::report($task['id'], F::toJSON($e->toArrayCompact(), true));
                    }catch (\Exception $e){
                        $u->execute(array(self::STATUS_ERROR, $task['id']));
                        self::report($task['id'], $e->getMessage().' '.$e->getFile().' '.$e->getLine());
                    }
                }
                if (count($tasks) == 0) $start = 0;
            }
            self::unlock();
        }
    }

    /**
     * Фоновый запуск обработчика задач
     */
    static function executeBackground()
    {
        if (!(self::$config['only_cron'] || self::is_lock())){
            if (substr(php_uname(), 0, 7) == "Windows"){
                pclose(popen("start /B ".self::$config['php'].' '.DIR_SERVER.'index.php tasks', "r"));
            }else{
                exec(self::$config['php'].' '.DIR_SERVER."index.php tasks > /dev/null &");
            }
        }
    }

    /**
     * Проверка возможности запуска обработчика задач
     */
    static function is_lock()
    {
        if (self::$lock) return true;
        return (is_file(DIR_SERVER_TEMP.self::LOCK_FILE));
//        $q = self::$db->query('SELECT 1 FROM {tasks_locks} WHERE `key`="tasks" LIMIT 0,1 FOR UPDATE');
//        if ($q->fetch(DB::FETCH_ASSOC)){
//            return true;
//        }
//        return false;
    }

    static function lock()
    {
        if (!self::$lock && !self::is_lock()){
            $f = fopen(DIR_SERVER_TEMP.self::LOCK_FILE, 'w+');
            fwrite($f, time());
            fclose($f);
//            self::$db->beginTransaction();
//            self::$db->exec('INSERT IGNORE INTO {tasks_locks} (`key`) VALUES ("tasks")');
//            self::$db->commit();
            self::$lock = true;
        }
    }

    static function unlock()
    {
        if (self::$lock){
            unlink(DIR_SERVER_TEMP.self::LOCK_FILE);
            clearstatcache(DIR_SERVER_TEMP, self::LOCK_FILE);
//            self::$db->beginTransaction();
//            self::$db->exec('DELETE FROM {tasks_locks} WHERE `key`="tasks"');
//            self::$db->commit();
            self::$lock = false;
        }
    }

    /**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements()
    {
		$requirements = array();
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')){
			$requirements[] = 'Требуется расширение <code>pdo_mysql</code> для PHP';
		}
        if (file_exists(DIR_SERVER.self::CONFIG_FILE) && !is_writable(DIR_SERVER.self::CONFIG_FILE)){
			$requirements[] = 'Установите права на запись для файла: <code>'.DIR_SERVER.self::CONFIG_FILE.'</code>';
		}
		if (!file_exists(DIR_SERVER.'Boolive/tasks/tpl.'.self::CONFIG_FILE)){
			$requirements[] = 'Отсутствует установочный файл <code>'.DIR_SERVER.'Boolive/tasks/tpl.'.self::CONFIG_FILE.'</code>';
		}
		return $requirements;
	}
}