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
     * @param string $parent URI объекта, для подчиенных которого необходимо условие доступа
     * @param int $depth Глубина затрагиваемых объектов относительно родительского
     * @return array
     */
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        if (!array_key_exists($action_kind, $this->_rights)){
            $this->_rights[$action_kind] = array();
            $cond = null;
            $curr = null;
            $obj = $this;
            // Выбор ролей члена и всех его групп (родителей)
            do{
                $roles = $obj->rights->find();
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
                $obj = $obj->parent();
            }while($obj instanceof Member);
            if (isset($cond) && sizeof($cond[1]) == 1) $cond = $cond[1][0];
            $this->_rights[$action_kind] = $cond;
        }
        return $this->_rights[$action_kind];
    }
}