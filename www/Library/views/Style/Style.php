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
        $style = $this->find();
        unset($style['title']);
        $str = '';
        foreach($style as $st){
            if ($st->value()!==''){
                $str.= $st->name().': '.$st->value().';';
            }
        }
        return $str;
    }
}