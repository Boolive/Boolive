<?php
/**
 * Макет
 *
 * Виджет для создания макетов (разметок).
 * По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами.
 * Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов.
 * @version 1.0
 */
namespace Library\layouts\Layout;

use Library\views\Widget\Widget,
    Boolive\data\Data,
    Boolive\values\Rule;

class Layout extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),
            )),
            'previous' => Rule::eq(false)
        ));
    }

    function startInitChild($input){
        parent::startInitChild($input);
        // По URL определяем объект и номер страницы
        $uri = $this->_input['REQUEST']['path'];
//        if (preg_match('|^(.*)/page-([0-9]+)$|u', $uri, $match)){
//            $uri = $match[1];
//            $this->_input_child['REQUEST']['page'] = $match[2];
//        }else{
//            $this->_input_child['REQUEST']['page'] = 1;
//        }
        $object = null;
        // объект по умолчанию
        if (empty($uri) && ($object = Data::read(array(
                'select' => 'children',
                'from' => '/Contents',
                'where' => array(
                    array('attr', 'is_hidden', '=', 0),
                    array('attr', 'is_mandatory', '=', 0)
                ),
                'order' => array(
                    array('order', 'ASC')
                ),
                'limit' => array(0,1),
                'comment' => 'read default page'
            )))){
            $object = reset($object);
        }
        // ищем в /Contents
        if (!$object && !empty($uri)) $object = Data::read('/Contents'.$uri.'&comment=read default page');
        // точное соответсвие uri
        if (!$object->isExist()) $object = Data::read($uri);
        // корнеь
        if (!$object->isExist() && $uri == '/Site/') $object = Data::read('');
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = $object;
    }
}