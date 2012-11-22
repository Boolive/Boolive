<?php
/**
 * Фокусировщик
 *
 * По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами.
 * Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов.
 * Может использоваться для макета сайта.
 * @version 1.0
 */
namespace Library\views\Focuser;

use Library\views\Widget\Widget,
    Boolive\data\Data,
    Boolive\values\Rule;

class Focuser extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(),
                )
            ))
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
        $object = null;
        // объект по умолчанию
        if (empty($uri) && ($object = Data::read('/Contents')->findAll2(array(
                'where' => array(
                    array('attr', 'is_history', '=', 0),
                    array('attr', 'is_delete', '=', 0),
                    array('is', '/Library/content_samples/Page')
                ),
                'order' => array(
                    array('order', 'ASC')
                ),
                'limit' => array(0,1)
            ), false, null))){
            $object = reset($object);
        }
        // ищем в /Contents
        if (!$object && !empty($uri)) $object = Data::read('/Contents'.$uri);
        // точное соответсвие uri
        if (!$object) $object = Data::read($uri);
        // корнеь
        if (!$object && $uri == '/Site/') $object = Data::read('');
        // Установка во входящие данные
        $this->_input_child['REQUEST']['object'] = $object;
    }
}