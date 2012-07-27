<?php
/**
 * Значения, передаваемые в php-шаблон.
 *
 * @example 
 * Пример использования:
 * 	$v = new TemplatePHPValues();
 * 	$v[0] = 'A&B';
 * 	echo $v[0]; // A&amp;B
 *  echo $v->html(0); //A&B
 * 
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine\Template;

use Engine\Values,
	Engine\Rule;

class PHPTemplateValues extends Values{
	/** @var \Engine\Entity Сущность, к которой обращаться за недостающими значениями*/
	private $_entity;

	/**
	 * Конструктор
	 * Все вложенные массивы преобразуются в объект TemplatePHPValues
	 * @param null $value Массив значений любого типа
	 * @param null|\Engine\Rule $rule Правило проверки значений по умолчанию
	 * @param \Engine\Entity|null $entity Сущность, к которой обращаться за недостающими значениями
	 */
	function __construct($value = null, $rule = null, $entity = null){
		parent::__construct($value, $rule);
		$this->_entity = $entity;
	}

	protected function defineRule(){
		$this->_rule = Rule::arrays(Rule::string()->escape(), true);
	}

	/**
	 * Получение элемента массива в виде объекта Values
	 * Если элемента с указанным именем нет, то будет исполнены одноименный подчиенный $this->_entity и
	 * элементу установится результат его работы
	 * @param mixed $name Ключ элемента
	 * @return \Engine\Template\PHPTemplateValues
	 */
	public function offsetGet($name){
		$start = (!$this->offsetExists($name) && isset($this->_entity));
		$sub = parent::offsetGet($name);
//		if ($start){
//			$sub->_entity = $this->_entity->{$name};
//			$sub->_value = $this->_entity->startChild($name);
//		}
		return $sub;
	}
}