<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Programs;

use Boolive\data\Data;
use Boolive\data\Entity;
use Library\views\AutoWidget2\AutoWidget2,
    Boolive\values\Rule;

class Programs extends AutoWidget2
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        // Проверка обновлений для объекта
        if ($this->_input['REQUEST']['object'] instanceof Entity){
            /** @var Entity $obj */
            $obj = $this->_input['REQUEST']['object'];
            if (($obj->_attribs['update_time']!=0 || $obj->_attribs['diff'] == Entity::DIFF_ADD) && (time()-$obj->_attribs['date']) > 300){
                Data::refresh($obj, 50, 1, true);
            }
        }
        return parent::work($v);
    }
}