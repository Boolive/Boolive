<?php
/**
 * Базовый класс исключений (ошибок)
 * Позволяет:
 * - группировать и одновременно вызовать множество исключений
 * - формируеть иерархию (к любому ичключению можно добавить множество подчиненных исключений)
 * - получать пользовательское сообщение об исключении
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */

namespace Engine;

use Exception;

class Error extends Exception{
	/** @var \Engine\Error Родительское  исключение */
	protected $parent;
	/** @var array Массив исключений */
	private $list;
	/** @var array Массив временных подчиенных исключений */
	private $temp_list;
	/** @var bool Признак, является ли исключение временным */
	private $is_temp;
	/** @var array Аргументы для вставки в текст сообщения */
	private $args;

	/**
	 * @param string|array $message Текст сообщения (имя исключения). С помощью массива передаётся текст сообщения и
	 * вставляемые в текст переменные
	 * @example new Error(array('Text %s incorrect', $text));
	 * @param int|string $code Код исключения
	 * @param Error $previous Предыдущее исключение. Используется при создания цепочки исключений
	 */
	function __construct($message = '', $code = 0, Error $previous = null){
		if (is_array($message)){
			if (sizeof($message)>0){
				$m = array_shift($message);
				if (!empty($message)){
					if (is_array($message[0])){
						$this->args = $message[0];
					}else{
						$this->args = $message;
					}
				}
				$message = $m;
			}else{
				$message = '';
			}
		}
		parent::__construct($message, $code, $previous);
		$this->parent = null;
		$this->list = array();
		$this->temp_list = array();
		$this->is_temp = false;
	}

	/**
	 * Перегрузка метода получения исключения. @example $e = $error->user->min;
	 * Всегда возвращется \Engine\Error, даже если нет запрашиваемого исключения (возвратитя временный \Engine\Error)
	 * @param string $name Имя параметра
	 * @return \Engine\Error
	 */
	public function __get($name){
		return $this->get($name);
	}

	/**
	 * Перегрузка установки исключения: @example $error->user = "min";
	 * Итогом будет цепочка из трех исключений.
	 * @param string $name Имя подчиеннего исключения
	 * @param string|\Engine\Error $error Добавляемое исключение
	 */
	public function __set($name, $error){
		// Создание подчиенненого списка исключений $name
		if (!isset($this->list[$name])){
			$this->add($name);
		}
		$this->list[$name]->add($error);
	}

	/**
	 * Добавление исключениея
	 * @param \Engine\Error|string $error Название (сообщение) исключения или объект исключения
	 * @return array|\Engine\Error |\Engine\Error
	 */
	public function add($error){
		// Если был временным
		$this->untemp();
		// Добавление подчиненного
		if (is_scalar($error)){
			$this->list[$error] = new Error($error);
			$this->list[$error]->parent = $this;
			return $this->list[$error];
		}
		if ($error instanceof Error){
			return $this->list[$error->message] = $error;
		}
		return $this;
	}

	/**
	 * Удаление признака временности исключения
	 */
	protected function untemp(){
		if ($this->is_temp){
			$this->is_temp = false;
			if (isset($this->parent)){
				// В родитле пермещаем себя в основной список
				$this->parent->list[$this->message] = $this;
				unset($this->parent->temp_list[$this->message]);
				// Возможно, родитель тоже временный
				$this->parent->untemp();
			}
		}
	}

	/**
	 * Получение исключения с указнным именем (ключом)
	 * @param string $name Название (ключ) исключения
	 * @return \Engine\Error
	 */
	public function get($name){
		if (isset($this->list[$name])){
			$this->list[$name];
		}else
		if (isset($this->temp_list[$name])){
			$this->temp_list[$name];
		}else{
			// Делавем временный подчиненный список исключений
			$this->temp_list[$name] = new Error($name);
			$this->temp_list[$name]->is_temp = true;
			$this->temp_list[$name]->parent = $this;
			return $this->temp_list[$name];
		}
		return $this->list[$name];
	}

	/**
	 * Возвращает все исключения
	 * @return array
	 */
	public function getAll(){
		return $this->list;
	}

	/**
	 * Возвращает многомерный массив сообщений всех ошибок
	 * Сообщения используются в качестве ключей элементов массива
	 * @return array
	 */
	public function getMessageTree(){
		$result = array();
		foreach ($this->list as $key => $e){
			$result[$key] = $e->getMessageTree();
		}
		return $result;
	}

	/**
	 * Проверка на наличие исключений
	 * @param string $name Название (ключ) исключения
	 * @return bool
	 */
	public function isExist($name = null){
		if (isset($name)){
			return isset($this->list[$name]);
		}
		return !empty($this->list);
	}

	/**
	 * Удаление всех подчиенных исключений
	 */
	public function clear(){
		unset($this->list, $this->temp_list);
		$this->list = array();
		$this->temp_list = array();
	}

	/**
	 * Удаление подчиенного исключения
	 * @param $name Название (ключ) исключения
	 */
	public function delete($name){
		if (isset($this->list[$name])){
			$this->list[$name]->parent = null;
			unset($this->list[$name]);
		}else
		if (isset($this->temp_list[$name])){
			$this->temp_list[$name]->parent = null;
			unset($this->temp_list[$name]);
		}
	}

	/**
	 * Аргументы сообщения
	 * @return array
	 */
	public function getArgs(){
		return $this->args;
	}

	/**
	 * Пользовательские сообщения об ошибке
	 * Возвращаются сообщения либо всех подчиенных исключений, либо тольок свой сообщение, если нет подчиенных
	 * @param bool $all_sub Признак, возратить все сообщения на вложенные исключения?
	 * @param string $postfix Строка, которую добавлять в конец каждого сообщения.
	 * @return string
	 */
	public function getUserMessage($all_sub = false, $postfix = "\n"){
		// Объединение сообщений подчиенных исключений
		if ($all_sub && $this->isExist()){
			$message = '';
			foreach ($this->list as $e){
				/** @var $e \Engine\Error */
				$message.= $e->getUserMessage($all_sub, $postfix);
			}
			return $message;
		}
		// @TODO Поиск пользовательского сообщения...

		// Сообщение по-умолчанию
		return vsprintf($this->message.$postfix, $this->args);
	}

	/**
	 * Сообщение об ошибках
	 * @return string
	 */
	public function __toString(){
		$result = "{$this->message}\n";
		foreach ($this->list as $e){
			/** @var $e \Engine\Error */
			$result.=' - '.$e->__toString()."\n";
		}
		return $result;
	}
}
