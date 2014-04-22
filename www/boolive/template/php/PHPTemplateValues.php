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
namespace boolive\template\php;

use boolive\values\Values,
    boolive\values\Rule;

class PHPTemplateValues extends Values
{
    /** @var \boolive\data\Entity Сущность, к которой обращаться за недостающими значениями*/
    private $_entity;

    /**
     * Конструктор
     * Все вложенные массивы преобразуются в объект TemplatePHPValues
     * @param null $value Массив значений любого типа
     * @param null|\boolive\values\Rule $rule Правило проверки значений по умолчанию
     * @param \boolive\data\Entity|null $entity Сущность, к которой обращаться за недостающими значениями
     */
    function __construct($value = null, $rule = null, $entity = null)
    {
        parent::__construct($value, $rule);
        $this->_entity = $entity;
    }

    function __get($name)
    {
        $start = (!$this->offsetExists($name) && isset($this->_entity));
        $sub = $this->offsetGet($name);
        if ($start){
            $sub->_entity = $this->_entity->linked(false)->{$name};
            if ($sub->_entity->isExist() && !$sub->_entity->isDraft(null, false) && !$sub->_entity->isHidden(null, false)){
                $sub->set($this->_entity->linked(true)->startChild($name));
            }else{
                $sub->set('');
            }
        }
        return $sub;
    }

    function __toString()
    {
        return (string)$this->get(Rule::escape());
    }
}