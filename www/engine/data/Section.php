<?php
/**
 * Класс секции
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

abstract class Section extends Entity{
	/**
	 * @param array $config Конфигурация соединения
	 */
	public function __construct($config){

	}
	/**
	 * Выбор объекта по его uri
	 * @param $uri
	 * @return \Engine\Entity|null
	 */
	public function read($uri){

	}

	/**
	 * Запись объекта. Путь указывается в атриубте uri объекта
	 * @param \Engine\Entity $entity Записываемый объект
	 * @return bool Если были изменения, то true
	 */
	public function put($entity){
		return false;
	}

	/**
	 * Вызов метода у объекта
	 * Реализуется для внешних секций
	 * @param $method
	 * @param $args
	 * @return mixed
	 */
	public function call($method, $args){
		return null;
	}

	/**
	 * @param $cond
	 * @return array
	 */
	public function select($cond){
		return array();
	}

	/**
	 * Установка секции
	 */
	public function install(){

	}
}
