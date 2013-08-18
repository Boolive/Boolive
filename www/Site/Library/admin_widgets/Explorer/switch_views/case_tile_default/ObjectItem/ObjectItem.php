<?php
/**
 * Отображение объекта в виде пункта списка
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\switch_views\case_tile_default\ObjectItem;

use Library\admin_widgets\ObjectItem as usesObjectItem;

class ObjectItem extends usesObjectItem\ObjectItem
{
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