<?php
/**
 * Значения, передаваемые в php-шаблон.
 *
 * @example 
 * Пример использования:
 *  $v = new TemplatePHPValues();
 *  $v[0] = 'A&B';
 *  echo $v[0]; // A&amp;B
 *  echo $v->html(0); //A&B
 * @link http://boolive.ru/createcms/making-page
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\template\php;

use Boolive\values\Values,
    Boolive\values\Rule;

class PHPTemplateValues extends Values
{
    /** @var \Boolive\data\Entity Сущность, к которой обращаться за недостающими значениями*/
    private $_entity;

    /**
     * Конструктор
     * Все вложенные массивы преобразуются в объект TemplatePHPValues
     * @param null $value Массив значений любого типа
     * @param null|\Boolive\values\Rule $rule Правило проверки значений по умолчанию
     * @param \Boolive\data\Entity|null $entity Сущность, к которой обращаться за недостающими значениями
     */
    function __construct($value = null, $rule = null, $entity = null)
    {
        parent::__construct($value, $rule);
        $this->_entity = $entity;
    }

    protected function defineRule()
    {
        $this->_rule = Rule::arrays(Rule::any(Rule::string()->escape()->ignore('escape'), Rule::null()), true);
    }

    /**
     * Получение элемента массива в виде объекта Values
     * Если элемента с указанным именем нет, то будет исполнены одноименный подчиенный $this->_entity и
     * элементу установится результат его работы
     * @param mixed $name Ключ элемента
     * @return \Boolive\template\php\PHPTemplateValues
     */
//    public function offsetGet($name)
//    {
//        $start = (!$this->offsetExists($name) && isset($this->_entity));
//        $sub = parent::offsetGet($name);
//        if ($start){
//            $sub->_entity = $this->_entity->{$name};
//            $sub->set($this->_entity->startChild($name));
//        }
//        return $sub;
//    }

    public function __get($name)
    {
        $start = (!$this->offsetExists($name) && isset($this->_entity));
        $sub = $this->offsetGet($name);
        if ($start){
            $sub->_entity = $this->_entity->{$name};
            $sub->set($this->_entity->startChild($name));
        }
        return $sub;
    }
}