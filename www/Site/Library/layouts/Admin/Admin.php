<?php
/**
 * Макет админки
 *
 * @version 1.0
 */
namespace Library\layouts\Admin;

use Library\layouts\Layout\Layout,
    Boolive\values\Rule,
    Boolive\data\Data,
    Boolive\input\Input;

class Admin extends Layout
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),//regexp('|^/admin|'),
            )),
            'PATH' => Rule::arrays(array(
                0 => Rule::eq('admin')->required()
            ))
        ));
    }

    function startInitChild($input){
        parent::startInitChild($input);
        // По URL определяем объект и номер страницы
        $uri = $this->_input['REQUEST']['path'];
        if (preg_match('|^(.*)/page-([0-9]+)$|u', $uri, $match)){
            $uri = $match[1];
            $this->_input_child['REQUEST']['page'] = $match[2];
        }else{
            $this->_input_child['REQUEST']['page'] = 1;
        }
        //удаление "/admin"
        $uri = mb_substr($uri, 6);
        if (preg_match('/^\/[a-z]+:\/\//ui', $uri)){
            $uri = ltrim($uri, '/');
        }
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = Data::read($uri);
    }

    function show($v = array(), $commands, $input)
    {
        $this->_commands->addHtml('base', array('href'=>'http://'.Input::SERVER()->HTTP_HOST->string().DIR_WEB.'admin/'));
        $v['basepath'] = DIR_WEB.'admin';
        return parent::show($v, $commands, $input);
    }
}