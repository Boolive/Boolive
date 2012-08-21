<?php
/**
 * Фокусировщик
 *
 * По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами.
 * Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов.
 * Используется в качестве эталона для макетов сайта.
 * @version 1.0
 */
namespace Library\basic\widgets\Focuser;

use Library\basic\widgets\Widget\Widget,
    Boolive\data\Data,
    Boolive\values\Rule;

class Focuser extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'path' => Rule::string(),
                ), Rule::any()
            )), Rule::any()
        );
    }

    public function canWork()
    {
        if ($result = parent::canWork()){
            // По URL определяем объект и номер страницы
            $uri = $this->_input['GET']['path'];
            if (preg_match('|^(.*)/page-([0-9]+)$|u', $uri, $match)){
                $uri = $match[1];
                $this->_input['GET']['page'] = $match[2];
            }else{
                $this->_input['GET']['page'] = 1;
            }
            $object = null;
            // объект по умолчанию
            if (empty($uri) && ($object = Data::object('/Contents')->find(array('count'=>1, 'order'=>'`order` ASC')))){
                $object = reset($object);
            }
            // ищем в /Contents
            if (!$object && !empty($uri)) $object = Data::object('/Contents'.$uri);
            // точное соответсвие uri
            if (!$object) $object = Data::object($uri);
            // корнеь
            if (!$object && $uri == '/Site/') $object = Data::object('');
            // Установка во входящие данные
            $this->_input['GET']['object'] = $object;
        }
        return $result;
    }
}
