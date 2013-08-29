<?php
/**
 * Виджет Облако ключевых слов
 *
 * @version 1.0
 * @autor Hitev Kirill <nomer_47@mail.ru>
 */
namespace Library\content_widgets\KeywordsCloud;

use \Library\views\Widget\Widget;

class KeywordsCloud extends Widget
{
    public function work($v = array())
    {
        $list = $this->object->linked()->find(array(
            'where' => array(
                array('is', '/Library/content_samples/Keyword')
            ),
            'key' => 'name',
            'comment' => 'all keywords to cloud'
        ));
        $v['title'] = $this->title->value();
        $max_value = 0;
        $min_value = 0;
        $i=0;
        foreach ($list as $name => $item){
            /** @var $item \Boolive\data\Entity */
            if($item->value()!=0){
                $v['list'][$i]['href'] = $item->uri();
                $v['list'][$i]['title'] = $name;
                $v['list'][$i]['value'] = $item->value();
                $i+=1;
                $max_value = max($max_value, $item->value());
                $min_value = $min_value == 0 ? $item->value() : min($min_value, $item->value());
            }
        }
        $font_d = $this->max_font_size->value() - $this->min_font_size->value();
        $value_d = $max_value - $min_value;
        if ($font_d > 0 && $value_d > 0){
            $v['font_dsize'] = $font_d / $value_d;
            $v['font_start'] = $this->min_font_size->value();
            $v['value_start'] = -$min_value;
        }else{
            $v['font_dsize'] = 1;
            $v['font_start'] = 10;
            $v['value_start'] = 0;
        }
        return parent::work($v);
    }
}