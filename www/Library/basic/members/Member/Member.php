<?php
/**
 * Член
 * Базовый объект для пользователей, групп и других субъектов
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\basic\members\Member;

use Boolive\data\Entity;

class Member extends Entity
{
    /**
     * Проверка доступа к указанному объекту
     * @param string $action_kind Вид действия
     * @param \Boolive\data\Entity $object Объект, к которому проверяется доступ
     * @return bool
     */
    public function checkAccess($action_kind, $object)
    {
        return $object->verify($this->getAccessCond($action_kind, $object->getParentUri(), 1));
    }

    /**
     * Условие достпа к объектам для использования его при поиске объектов
     * Родитель и глубина указывается для упрощения условия
     * @param string $action_kind Вид действия
     * @param string $parent URI объекта, для подчиенных которого необходимо условие доступа
     * @param int $depth Глубина затрагиваемых объектов относительно родительского
     * @return array
     */
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        if ($action_kind == 'write'){
            return array('not', array('any', array(
                //array('attr', 'is_hidden', '=', '1'),
                    array('parent', '/Contents/news/news1'),
                    array('attr', 'uri', '=', '/Contents/news/news1'),
                //array('is', array('/Library/content_samples/Page'))
                )
            ));
        }
        return array();//'not', array('any', array(
            //array('attr', 'is_hidden', '=', '1'),
            //array('parent', '/Contents/news/news1'),
            //array('attr', 'uri', '=', '/Contents/news/news1'),
            //array('is', array('/Library/content_samples/Page'))
            //)
//        ));
    }
}
