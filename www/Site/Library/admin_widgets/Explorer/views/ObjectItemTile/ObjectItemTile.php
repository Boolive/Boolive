<?php
/**
 * Отображение объекта в виде плитки
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\views\ObjectItemTile;

use Library\admin_widgets\Explorer\views\ObjectItem\ObjectItem,
    Boolive\values\Rule;

class ObjectItemTile extends ObjectItem
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity($this->object_rule->value())->required(),
                        'view_kind' => Rule::eq('tile')
                    )
                )
            )
        );
    }
    public function work($v = array())
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];

        if ($obj->{'icon-150'}->isExist()){
            $img = $obj->{'icon-150'};
            $v['style'] = 'background-image: url('.$img->file().'); background-repeat: no-repeat; background-position: right bottom;';
        }else{
            $v['style'] = '';
        }
        if ($v['style']){
            $v['style'] = 'style="'.$v['style'].'"';
        }

        return parent::work($v);
    }
}