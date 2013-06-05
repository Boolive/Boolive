<?php
/**
 * Виджет заголовков в тексте
 *
 * @version 1.0
 */
namespace Library\content_widgets\Head;

use Boolive\data\Data;
use Library\views\Widget\Widget;

class Head extends Widget
{
    public function work($v = array())
    {
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['style'] = $this->_input['REQUEST']['object']->find(array('select'=>'tree', 'depth'=>array(1, 'max'), 'comment' => 'read tree of text element'));
        $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
        $obj = $this->_input['REQUEST']['object'];
        $v['tag'] = 'h1';
        Data::read(array(
            'from' => '/Library/content_samples/paragraphs',
            'select' => 'children'
        ));
        if ($obj->is('/Library/content_samples/paragraphs/H2')){
            $v['tag'] = 'h2';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H3')){
            $v['tag'] = 'h3';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H4')){
            $v['tag'] = 'h4';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H5')){
            $v['tag'] = 'h5';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H6')){
            $v['tag'] = 'h6';
        }
        return parent::work($v);
    }
}
