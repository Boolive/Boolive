<?php
/**
 * Член
 * Базовый объект для пользователей и групп
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\access\Member;

use Boolive\data\Data;
use Boolive\data\Entity,
    Boolive\functions\F;

class Member extends Entity
{
    /** @var array Информация о доступе к объектам сгрупированная по видам действий */
    protected $_rights = array();
    /**
     * Проверка доступа к указанному объекту
     * @param string $action_kind Вид действия
     * @param \Boolive\data\Entity $object Объект, к которому проверяется доступ
     * @return bool
     */
    public function checkAccess($action_kind, $object)
    {
        return $object->verify($this->getAccessCond($action_kind/*, $object->parentUri(), 1*/));
    }

    /**
     * Условие доступа к объектам
     * Родитель и глубина указывается для оптимизации условия
     * @param string $action_kind Вид действия
     * @param string $parent URI объекта, для подчиненных которого необходимо условие доступа
     * @param int $depth Глубина затрагиваемых объектов относительно родительского
     * @return array
     */
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        if (!isset($this->_rights[$action_kind])){
            $this->_rights[$action_kind] = array();
            $cond = null;
            $curr = null;
//            if ($this->isExist()){
//                $parents = $this->find(array('select'=>'parents', 'depth' => array(0,'max'), 'order' => array('parent_cnt', 'desc')));
//            }
            //else{
//                $parents = $this->parent()->find(array('select'=>'parents', 'depth' => array(0,'max'), 'order' => array('parent_cnt', 'desc')));
//                array_unshift($parents, $this);
//            }

            $obj = $this;
            // Выбор ролей члена и всех его групп (родителей)
            do{
                if ($obj->isExist()){
                    $rights = Data::read(array(
                        'select' => 'tree',
                        'from' => array($obj, 'rights'),
                        'depth' => array(0, 'max'),
                        'comment' => 'read rights of Member'
                    ));
//                    $rights = Data::read(array($obj, 'rights'), false);
                    $roles = $rights->find(array('comment'=>'read all rights'));
                    // Объединяем права в общий список
                    foreach ($roles as $r){
                        if (($c = $r->getAccessCond($action_kind, $parent, $depth)) && is_array($c)){
                            $need = ($c[0] == 'not')?'all':'any';
                            if (is_null($cond)){
                                $cond = array($need, array($c));
                            }else
                            if ($curr!=$need){
                                if (sizeof($cond[1]) == 1) $cond = $cond[1][0];
                                $cond = array($need, array($cond, $c));
                            }else{
                                $cond[1][] = $c;
                            }
                            $curr = $need;
                        }
                    }
                }
                $obj = $obj->parent();
            }while($obj instanceof Member);
            if (isset($cond) && sizeof($cond[1]) == 1) $cond = $cond[1][0];
            $this->_rights[$action_kind] = $cond;
        }
        return $this->_rights[$action_kind];
    }
}