<?php
/**
 * Управление событиями
 * Обеспечивает взаимодействие классов системы
 *
 * @version	1.0
 */
namespace Engine;

use Engine\Classes,
	Engine\EventResult;

class Events{
	const FILE_HANDLERS = 'config.events.php';
	private static $handlers = array();
	private static $save = array();
	private static $is_change = false;
	private static $once = array();

	/**
	 * Активация модуля
	 */
	static function Activate(){
		self::LoadHandlers(ROOT_DIR_ENGINE.self::FILE_HANDLERS);
	}

	/**
	 * Добавляет обработчик события с определенным имененм
	 * от определённого источника (класса)
	 *
	 * @param string $event_name Имя события
	 * @param string $handler_module Имя модуля обработчика события
	 * @param string $handler_method Имя метода класса обработчика события
	 * @param bool $save Автоматически сохранять регистрацию
	 * @param bool $once Одноразовая обработка события
	 */
	static function AddHandler($event_name, $handler_module, $handler_method, $save=true, $once = false){
		self::$handlers[$event_name][] = array($handler_module, $handler_method);
		if ($once) self::$once[$event_name][$handler_module.$handler_method] = true;
		self::$save[$event_name][] = $save;
		if ($save) self::$is_change = true;
	}

	/**
	 * Удаление обработчика события
	 *
	 * @param string $event_name Имя события
	 * @param string $handler_module Имя класса обработчика события
	 * @param string $handler_method Имя метода модуля обработчика события
	 */
	static function RemoveHandler($event_name, $handler_module, $handler_method){
		if (isset(self::$handlers[$event_name])){
			$list = self::$handlers[$event_name];
			$cnt = sizeof($list) - 1;
			for ($i = $cnt; $i >= 0; $i--){
				if (($list[$i][0] == $handler_module) && ($list[$i][1] == $handler_method)){
					$key = self::$handlers[$event_name][$i][0].self::$handlers[$event_name][$i][1];
					if (isset(self::$once[$event_name]) && isset(self::$once[$event_name][$key])){
						unset(self::$once[$event_name][$key]);
						if (sizeof(self::$once[$event_name]) == 0) unset(self::$once[$event_name]);
					}
				}else{
					self::$is_change = true;
				}
				array_splice(self::$handlers[$event_name], $i, 1);
			}
		}
	}

	/**
	 * Генерация события.
	 *
	 * @param string $event_name Имя события
	 * @param array $params Параметры события
	 * @return EventResult Объект события с результатами его обработки
	 */
	static function Send($event_name, $params=array()){
		$r = new EventResult();
		if (isset(self::$handlers[$event_name])){
			$cnt = sizeof(self::$handlers[$event_name]);
			for ($i = 0; $i < $cnt; $i++){
				if (!is_array($params)){
					$params = array($params);
				}
				if (!Classes::IsIncluded(self::$handlers[$event_name][$i][0])){
					Classes::Activate(self::$handlers[$event_name][$i][0]);
				}
				$r->value = call_user_func_array(self::$handlers[$event_name][$i], $params);
				$r->count++;
				if (isset(self::$once[$event_name]) && isset(self::$once[$event_name][self::$handlers[$event_name][$i][0].self::$handlers[$event_name][$i][1]])){
					self::RemoveHandler($event_name, self::$handlers[$event_name][$i][0], self::$handlers[$event_name][$i][1]);
				}
			}
		}
		return $r;
	}

	/**
	 * Загрузка информации об обработчиков собыйти из файла
	 * @param $config_file
	 */
	private static function LoadHandlers($config_file){
		include $config_file;
		if (isset($events)){
			self::$handlers = array_merge(self::$handlers, $events);
		}
		self::$is_change = false;
	}
}
