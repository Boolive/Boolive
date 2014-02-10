<?php
/**
 * Член
 * Базовый объект для пользователей и групп
 *
 * @version 1.0
 * @date 29.12.2012
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\access\Member;

use Boolive\data\Data;
use Boolive\data\Entity,
    Boolive\functions\F;
use Boolive\develop\Trace;
use Site\library\access\Role\Role;

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
    function checkAccess($action_kind, $object)
    {
        return $object->verify($this->getAccessCond($action_kind, $object));
    }

    /**
     * Условие доступа к объектам
     * Родитель и глубина указывается для оптимизации условия
     * @param string $action_kind Вид действия
     * @param $object Объект, к которому проверяется доступ
     * @return array
     */
    function getAccessCond($action_kind, $object = null)
    {
        if (!isset($this->_rights[$action_kind])){
            //Trace::groups('Data')->group('START getAccess');
            $this->_rights[$action_kind] = array();
            $cond = null;
            $curr = null;
            if ($this->isExist()){
                $parents = $this->find(array('select'=>'parents', 'depth' => array(0,'max'), 'where'=>array('attr', 'parent_cnt', '>', 0), 'order' => array('parent_cnt', 'asc'), 'group'=>true), false, true, false);
            }
            else{
                $parents = $this->parent()->find(array('select'=>'parents', 'depth' => array(0,'max'), 'where'=>array('attr', 'parent_cnt', '>', 0), 'order' => array('parent_cnt', 'asc'), 'group'=>true), false, true, false);
                //array_unshift($parents, $this);
            }

            $obj = reset($parents);
            // Выбор ролей члена и всех его групп (родителей)
            do{
                if ($obj && $obj->isExist()){
                    // Выбор ролей со всей информацией в них
                    $roles = $obj->rights->find(array(
                        'select' => 'tree',
                        'depth' => array(1, 'max'),
                        'comment' => 'read rights of Member',
                        'return'=>array('depth'=>1)
                    ), false);
//                    $rights = Data::read(array($obj, 'rights'), false);
                    //$roles = $rights->find(array('comment'=>'read all roles of Member'));
                    // Объединяем права в общий список
                    foreach ($roles as $r){
                        if ($r instanceof Role && ($c = $r->linked()->getAccessCond($action_kind, $object)) && is_array($c)){
                            $need = ($c[0] == 'not')?'all':'any';
                            if (is_null($cond)){
                                $cond = array($need, array($c));
                            }else
                            if ($curr!=$need){
                                if (count($cond[1]) == 1) $cond = $cond[1][0];
                                $cond = array($need, array($cond, $c));
                            }else{
                                $cond[1][] = $c;
                            }
                            $curr = $need;
                        }
                    }
                }
                $obj = next($parents);
            }while($obj instanceof Member);
            if (isset($cond)){
                if (count($cond[1]) == 1) $cond = $cond[1][0];
                $this->_rights[$action_kind] = $cond;
            }
            //Trace::groups('Data')->group('END getAccess');
        }
        return $this->_rights[$action_kind];
    }
}