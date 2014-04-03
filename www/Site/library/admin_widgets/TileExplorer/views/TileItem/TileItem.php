<?php
/**
 * Пункт объекта плиткой
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\TileExplorer\views\TileItem;

use Site\library\admin_widgets\BaseExplorer\views\Item\Item;

class TileItem extends Item
{
    function show($v = array(), $commands, $input)
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];

        if ($obj->{'icon-150'}->isExist()){
            $img = $obj->{'icon-150'};
            $v['icon-style'] = 'background-image: url('.$img->file().'); background-repeat: no-repeat; background-position: right bottom';
        }else{
            $v['icon-style'] = '';
        }
        return parent::show($v,$commands, $input);
    }
}