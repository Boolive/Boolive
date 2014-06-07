<?php
/**
 * Класс для атрибута Style в html разметке
 *
 * Created by JetBrains PhpStorm.
 * @author: polinа Putrolaynen
 * @date: 21.01.13
 *
 */
namespace site\library\design\Style;

use boolive\data\Entity;

class Style  extends Entity
{
    /**
    * Makes the "style" attribute string for html
    * @return string
    */
    function getStyle()
    {
        $style = $this->find(array('key'=>'name', 'comment' => 'read style'));
        unset($style['title'], $style['description']);
        $str = '';
        foreach($style as $name => $st){
            /** @var Entity $st */
            if (!$st->isDraft(null, false) && $st->value()!==''){
                $str.= $name.': '.$st->value().';';
            }
        }
        return $str;
    }

    function birth($for = null, $draft = true)
    {
        $obj = parent::birth($for, $draft);
        $obj->name(mb_strtolower($obj->name()), true);
        return $obj;
    }
}