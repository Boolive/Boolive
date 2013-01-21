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
        $attr = array();
        $style = $this->find();
        unset($style['title']);
        foreach($style as $st){
           $attr[$st->name()]=$st->name().': '.$st->value();
        }
        $attr = implode(';', $attr);
        return $attr;
    }
}
