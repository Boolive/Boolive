<?php
/**
 * Макет админки
 *
 * @version 1.0
 */
namespace site\library\admin\Admin;

use site\library\layouts\Layout\Layout,
    boolive\values\Rule,
    boolive\data\Data2,
    boolive\input\Input;

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

    function startInitChild($input)
    {
        parent::startInitChild($input);
        // По URL определяем объект и номер страницы
        $uri = $this->_input['REQUEST']['path'];
        //удаление "/admin"
        $uri = mb_substr($uri, 6);
        if (preg_match('/^\/[a-z]+:\/\//ui', $uri)){
            $uri = ltrim($uri, '/');
        }
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = Data2::read($uri);
    }

    function show($v = array(), $commands, $input)
    {
        $this->_commands->htmlHead('title', array('text'=>'Boolive'));
        $this->_commands->htmlHead('base', array('href'=>'http://'.Input::SERVER()->HTTP_HOST->string().'/admin/'));
        $v['basepath'] = '/admin';
        return parent::show($v, $commands, $input);
    }
}