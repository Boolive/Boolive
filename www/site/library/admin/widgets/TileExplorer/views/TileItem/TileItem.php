<?php
/**
 * Пункт объекта плиткой
 * 
 * @version 1.0
 */
namespace site\library\admin\widgets\TileExplorer\views\TileItem;

use site\library\admin\widgets\BaseExplorer\views\Item\Item;

class TileItem extends Item
{
    function show($v = array(), $commands, $input)
    {
        /** @var $obj \boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];

        if ($obj->{'icon-150'}->isExist()){
            $img = $obj->{'icon-150'};
            $v['icon-style'] = 'background-image: url('.$img->file().'); background-repeat: no-repeat; background-position: right 15px;';
        }else{
            $v['icon-style'] = '';
        }
        return parent::show($v,$commands, $input);
    }
}