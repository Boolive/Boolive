<?php
/**
 * Класс для атрибута Style в html разметке
 *
 * Created by JetBrains PhpStorm.
 * @author: polinа Putrolaynen
 * @date: 21.01.13
 *
 */
namespace Library\views\Style;

use Boolive\data\Entity;

class Style  extends Entity
{
    /**
    * Makes the "style" attribute string for html
    * @return string
    */
    public function getStyle()
    {
        $style = $this->find(array('where'=>array('attr', 'is_delete', '>=', '0')));
        unset($style['title']);
        $str = '';
        foreach($style as $st){
            /** @var Entity $st */
            if (!$st->isDelete(null, false) && $st->value()!==''){
                $str.= $st->name().': '.$st->value().';';
            }
        }
        return $str;
    }

    /**
     * Экпортировать все свойства совместно с объектом стиля
     * @return array|bool
     */
    public function exportedProperties()
    {
        return true;
    }
}