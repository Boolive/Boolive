<?php
/**
 * Макет
 *
 * Виджет для создания макетов (разметок).
 * По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами.
 * Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов.
 * @version 1.0
 */
namespace site\library\layouts\Layout;

use site\library\views\Widget\Widget,
    boolive\data\Data,
    boolive\values\Rule;

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
        // объект по умолчанию - первый в /contents не являющейся свойством
        if (empty($uri)){
            if ($object = Data::read(array(
                'select' => 'children',
                'from' => '/contents',
                'where' => array(
                    array('is_hidden', '=', 0),
                    array('is_property', '=', 0),
                    array('child', 'is_main', array('value','=',1))
                ),
//                'order' => array(
//                    array('order', 'ASC')
//                ),
                'limit' => array(0,1),
                //'comment' => 'read default page'
            ))){
                $object = reset($object);
            }
        }else{
            if ($uri === '/site/') $object = Data::read('');
            // Ищем в /contents
            if (!$object) $object = Data::read('/contents'.$uri.'&comment=read default page');
            // Точное соответствие uri
            if ((!$object || !$object->isExist())) $object = Data::read($uri);
        }
        if ((!$object || !$object->isExist())) $object = Data::read('/contents');
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = $object;
    }
}