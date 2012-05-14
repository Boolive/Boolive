<?php
/**
 * Класс результата вызова события
 *
 * @version	1.0
 */
namespace Engine;

class EventResult{
	/** @var int Количество исполненных методов-обработчиков */
	public $count;
	/** @var mixed Результат вызова методов-обработчиков */
	public $value;

	function __construct(){
		$this->count = 0;
		$this->value = null;
	}
}