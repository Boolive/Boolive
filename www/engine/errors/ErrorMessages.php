<?php
/**
 * Сообщения об ошибках
 * Объекты сообщений сгруппированы в иерархическую структуру, и ассоциируются с соответсвующими структурами исключений.
 * Класс предствляет собой реестр сообщений, к кторому обращаются модули ошибок и все остальные, чтоб получить или
 * записать человекопонятное сообщение об ошибке.
 * @example
 * //Сообщения для исключения с кодом 'system'
 * $message = ErrorMessages::Error()->get('system');
 * $message = ErrorMessages::Error('system');
 * $message = ErrorMessages::Error()->system;
 * //Сообщения для исключения с кодом 'login' вложенным в исключение с кодом 'auth'
 * $message = ErrorMessages::Error()->auth->login;
 * @version 1.0
 */
namespace Engine;

use Countable;

class ErrorMessages implements Countable{
	/** @var \Engine\ErrorMessages Корневой объект всех сообщений об ощибках */
	static private $messages_list;
	/** @var string Сообщение об ошибке */
	private $message;
	/** @var array Вложенные сообщения. Ключи элементов массива являются кодами (названиями) ошибок */
	private $list;

	/**
	 * Активация модуля
	 */
	static function Activate(){
		self::$messages_list = new ErrorMessages('Error');
	}

	/**
	 * Возращает корневой объект всех сообщений об ощибках
	 * @param string $key Ключ ошибки (код, название). Если указан, то возвращается объект этой ошибки
	 * @return ErrorMessages|void
	 */
	static function Error($key = null){
		if (isset($key)){
			return self::$messages_list->get($key);
		}
		return self::$messages_list;
	}

	/**
	 * Конструктор объекта сообщения об ошибке
	 * @param null $message
	 */
	public function __construct($message = null){
		$this->message = $message;
		$this->list = array();
	}

	/**
	 * Выбор объекта сообщения об ошибке.
	 * Еслии сообщения нет, то будет создан объект с пустым сообщением
	 * @example $error->key;
	 * @param $key Ключ ошибки (код, название)
	 * @return \Engine\ErrorMessages
	 */
	public function __get($key){
		return $this->get($key);
	}

	/**
	 * Добавление сообщения об ошибке.
	 * Еслии указанный ключ ошибки уже сущесвтует, то будет обновленно сообщение
	 * @example $error->key = 'message'; или $error->key = new ErrorMessages('mesage');
	 * @param $key Ключ ошибки (код, название)
	 * @param $message|\Engine\ErrorMessages Сообщение об ошибке или объект сообщения об ошибке
	 * @return \Engine\ErrorMessages Объект добавленного сообщения об ошибке
	 */
	public function __set($key, $message = 'Error'){
		$this->add($key, $message);
	}

	/**
	 * Проверка существования сообщение для указанной ошибки
	 * @example isset($error->key);
	 * @param $key Ключ ошибки (код, название)
	 * @return bool Признак, true, если сущесвтует
	 */
	public function __isset($key){
		return $this->isExist($key);
	}

	/**
	 * Удаление сообщения об ошибке.
	 * Если не указан ключ ошибки, то удаляются все сообщения
	 * @example unsset($error->key);
	 * @param null $key Ключ ошибки (код, название)
	 */
	public function __unset($key){
		$this->delete($key);
	}

	/**
	 * Выбор объекта сообщения об ошибке
	 * Еслии сообщения нет, то будет создан объект с пустым сообщением
	 * @param $key Ключ ошибки (код, название)
	 * @return \Engine\ErrorMessages
	 */
	public function get($key){
		if (!isset($this->list[$key])){
			$this->list[$key] = new ErrorMessages();
		}
		return $this->list[$key];
	}

	/**
	 * Добавление сообщения об ошибке.
	 * Еслии указанный ключ ошибки уже сущесвтует, то будет обновленно сообщение
	 * @param $key Ключ ошибки (код, название)
	 * @param $message|\Engine\ErrorMessages Сообщение об ошибке или объект сообщения об ошибке
	 * @return \Engine\ErrorMessages Объект добавленного сообщения об ошибке
	 */
	public function add($key, $message = 'Error'){
		if ($message instanceof ErrorMessages){
			$this->list[$key] = $message;
		}else
		if (!isset($this->list[$key])){
			$this->list[$key] = new ErrorMessages($message);
		}else{
			$this->list[$key]->message($message);
		}
		return $this->list[$key];
	}

	/**
	 * Проверка существования сообщение для указанной ошибки
	 * @param $key Ключ ошибки (код, название)
	 * @return bool Признак, true, если сущесвтует
	 */
	public function isExist($key){
		return isset($this->list[$key]);
	}

	/**
	 * Удаление сообщения об ошибке.
	 * Если не указан ключ ошибки, то удаляются все сообщения
	 * @param null $key Ключ ошибки (код, название)
	 */
	public function delete($key = null){
		if (!isset($key)){
			$this->list = array();
		}else
		if (isset($this->list[$key])){
			unset($this->list[$key]);
		}
	}

	/**
	 * Количество подчиенных сообщений
	 * @return int
	 */
	public function count(){
		return sizeof($this->list);
	}

	/**
	 * Возвращает список объектов всех подчиенных сообщений
	 * @return array
	 */
	public function getList(){
		return $this->list;
	}

	/**
	 * Установка и возвращение сообщения об ошибке
	 * Обновление сообшения происходит, если установлен аргумент $message
	 * @param string|null $message Сообщение об ошибке
	 * @return null|string Сообщение об ошибке
	 */
	public function message($message = null){
		if (isset($message)){
			$this->message = $message;
		}
		return $this->message;
	}
}
