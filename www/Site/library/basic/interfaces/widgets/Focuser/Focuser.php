<?php
/**
 * Фокусировщик
 *
 * По URL запроса определяет объект и номер страницы для последующего оперирования ими подчиненными виджетами.
 * Найденный объект и номер страницы помещаются во входящие данные для подчиненных виджетов.
 * Используется в качестве эталона для макетов сайта.
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\Focuser;

use library\basic\interfaces\widgets\Widget\Widget,
    Boolive\data\Data;

class Focuser extends Widget
{
    public function canWork(){
        if ($result = parent::canWork()){
            // По URL определяем объект и номер страницы
            $uri = $this->_input->GET->path->string();
            if (preg_match('|^(.*)/page-([0-9]+)$|u', $uri, $match)){
                $uri = $match[1];
                $this->_input->GET->page = $match[2];
            }else{
                $this->_input->GET->page = 1;
            }
            $object = null;
            // объект по умолчанию
            if (empty($uri) && ($object = Data::object('/contents')->find(array('count'=>1, 'order'=>'`order` ASC')))){
                $object = $object[0];
            }
            // ищем в /contents
            if (!$object && !empty($uri)) $object = Data::object('/contents/'.$uri);
            // точное соответсвие uri
            if (!$object) $object = Data::object('/'.$uri);
            // корнеь
            if (!$object && $uri == 'Site') $object = Data::object('');
            // Установка во входящие данные
            $this->_input->GET->object = $object;
        }
        return $result;
    }

//    public function work($v = array()){
//        trace($this->_input);
//        return parent::work($v);
//    }
}
