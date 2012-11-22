<?php
/**
 * Член
 * Базовый объект для пользователей, групп и других субъектов
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\basic\members\Member;

use Boolive\data\Entity,
    Boolive\functions\F;

class Member extends Entity
{
    /** @var array Информация о доступе к объектам сгрупированная по видам действий */
    private $access_info;
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
     * Условие доступа к объектам
     * Родитель и глубина указывается для оптимизации условия
     * @param string $action_kind Вид действия
     * @param string $parent URI объекта, для подчиенных которого необходимо условие доступа
     * @param int $depth Глубина затрагиваемых объектов относительно родительского
     * @return array
     */
    public function getAccessCond($action_kind, $parent = '', $depth = null)
    {
        //return array();
        // Поиск оптимальной ветки условий доступа по $parent
        $find = function($access)use($parent, &$find){
            $f = false;
            foreach ($access as $uri => $info){
                // Выбор ветки
                if (mb_substr($parent.'/', 0, mb_strlen($uri)+1) == $uri.'/'){
                    // Пробуем найти вложенную ветку. Если не найдётся, то используем текущую
                    if (!isset($info['child']) || !($f = $find($info['child'], $access[$uri]))) $f = $info;
                    return $f;
                }
            }
            // Ветка услвоий не найдена
            return $f;
        };
        $cond = $find(array(''=>$this->getAccessInfo($action_kind)));

        // Условий нет (такое возможно при инициализации контроля доступа)
        if (empty($cond)) return array();

        // Отсечение лишних концов условий если глубина превышает необходумую или не совпадает с $parent
        $max_level = isset($depth) ? mb_substr_count($parent, '/') + $depth : null;
        $parent_length = mb_strlen($parent)+1;
        $trim = function(&$cond)use(&$trim, $max_level, $parent, $parent_length){
            $result = array();
            $cnt = sizeof($cond[1]);
            for ($i = 1; $i<$cnt; $i++){
                if ((is_null($max_level) || $cond[1][$i][1] <= $max_level) &&
                    mb_substr($cond[1][$i][2].'/', 0, $parent_length) == $parent.'/'){
                    // Условие подходит
                    // Обработка вложенного услвоия
                    $result[] = $trim($cond[1][$i][0]);
                }
            }
            array_unshift($result, $cond[1][0]);
            return array($cond[0], $result);
        };
        return $trim($cond['cond']);
    }

    /**
     * Вся информация о доступе к объектам в соответсвии с видом действия
     * Используется для формирования оптимального условия доступа в getAccessCond()
     * @param string $action_kind Вид действия
     * @return array
     */
    public function getAccessInfo($action_kind = 'read')
    {
        if (!isset($this->access_info[$action_kind])){
            $this->access_info[$action_kind] = array();
            $access_info = array();
            $obj = $this;

            $cond = array('where' => array(array('attr', 'is_link', '=', 1)));
            // Выбор прав члена и всех его групп (родителей)
            do{
                $rights = $obj->real()->access->{$action_kind}->findAll2($cond);
                // Объединяем права в общий список
                foreach ($rights as $r){
                    $for = $r->notLink();
                    if (!isset($access_info[$for['uri']])) $access_info[$for['uri']] = array('access' => (int)$r->getValue(), 'level' => $for->getLevel());
                }
                $obj = $obj->parent();
            }while($obj instanceof Member);
            // По умолчанию нет доступа на всё
            if (!isset($access_info[''])){
                $access_info[''] = array('access' => -1, 'level' => 0);
            }
            // Упорядочивание "доступов" к объектам
            ksort($access_info);
            // Образование дерева условий и формирования самих условий
            $prev_list = array();
            foreach ($access_info as $uri => $info){
                // Поиск в $prev родителя
                $i = sizeof($prev_list);
                while (--$i >= 0){
                    if (abs($access_info[$prev_list[$i]]['access'])<3 && // Предыдущее условие затрагивает подчиненных
                        mb_substr($uri.'/', 0, mb_strlen($prev_list[$i])+1) == $prev_list[$i].'/'){
                        if (($info['access'] > 0) != ($access_info[$prev_list[$i]]['access'] > 0)) // Одно запрещает, другое запрещает
                        {
                            $access_info[$uri]['parent'] = $prev_list[$i];
                            $access_info[$prev_list[$i]]['child'][$uri] = &$access_info[$uri];
                        }else{
                            // Удаляем, так как лишнее
                            unset($access_info[$uri]);
                        }
                        $i = 0;
                    }
                }
                if (isset($access_info[$uri])){
                    $prev_list[] = $uri;
                    // Запрет
                    if ($access_info[$uri]['access'] < 0){
                        $access_info[$uri]['cond'] = array('any', array(
                            array('not', array(array('of', $uri, -$access_info[$uri]['access'])))
                        ));
                    }
                    // Разрешение
                    else{
                        $access_info[$uri]['cond'] = array('all', array(
                            array('of', $uri, $access_info[$uri]['access'])
                        ));
                    }
                    // Условие
                    if (isset($access_info[$uri]['parent']) && isset($access_info[$access_info[$uri]['parent']]['cond'])){
                        $access_info[$access_info[$uri]['parent']]['cond'][1][] = array(&$access_info[$uri]['cond'], $access_info[$uri]['level'], $uri);
                    }
                }
            }
            $this->access_info[$action_kind] = $access_info[''];
        }
        return $this->access_info[$action_kind];
    }
}