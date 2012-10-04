<?php
/**
 * Макет админки
 *
 * @version 1.0
 */
namespace Library\layouts\Admin;

use Library\views\Focuser\Focuser,
    Boolive\values\Rule,
    Boolive\data\Data;

class Admin extends Focuser
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                    'path' => Rule::string(),//regexp('|^/admin|'),
                    )
                ),
                'PATH' => Rule::arrays(array(
                    0 => Rule::eq('admin')->required()
                    )
                )
            )
        );
    }

    protected function initInputChild($input){
        parent::initInputChild($input);
        // По URL определяем объект и номер страницы
        $uri = $this->_input['REQUEST']['path'];
        if (preg_match('|^(.*)/page-([0-9]+)$|u', $uri, $match)){
            $uri = $match[1];
            $this->_input_child['REQUEST']['page'] = $match[2];
        }else{
            $this->_input_child['REQUEST']['page'] = 1;
        }
        $uri = mb_substr($uri, 6);
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = Data::object($uri);
    }
}
