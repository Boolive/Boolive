<?php
/**
 * Бефер атриубтов (кэш первого уровня)
 * Содержит атрибуты выбранных объектов
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use Boolive\functions\F;

class Buffer
{
    public static $list = array();

    /**
     * Выбор результата выборки из буфера
     * @param $cond Условие выборки
     * @param bool $check_exists Признак, проверять существование объекта в буфере родителя?
     * @return mixed|null Результат выборки или null, если результата нет в буфере
     */
    static function get($cond, $check_exists = true)
    {
        if (empty($cond['access'])) $cond['access'] = true;
        $result = null;
        // Ключ буфера из условия выборки.
        $buffer_cond = $cond;
        if (!empty($cond['key'])) $buffer_cond['key'] = false;
        $buffer_key = json_encode($buffer_cond);
        if (isset(self::$list[$buffer_key]) || array_key_exists($buffer_key, self::$list)){
            $result = self::$list[$buffer_key];
        }else
        // Если есть буфер выборки подчиненных, в которых должен оказаться искомый объект,
        // то проверяем его наличие в буфере
        if ($check_exists && $cond['select'][0] == 'self' && (is_scalar($cond['from']) && !Data::isShortUri($cond['from']))){
            $names = F::splitRight('/', $cond['from'], true);
            // Условие переопределяется и нормалтзуется с новыми параметрам
            $ocond = Data::decodeCond(array(
                'from' => $names[0],
                'depth' => array(1,1),
                'select' => array('children'),
                'order' => array(array('order', 'asc'))
            ), $cond);
            $key = json_encode($ocond);
            if (isset(self::$list[$key]) || array_key_exists($key, self::$list)){
                if (!isset(self::$list[$key][$names[1]])){
                    return array('name'=>$names[1], 'uri'=>$cond['from'], 'owner'=>$cond['owner'], 'lang'=>$cond['lang'], 'class' => '\\Boolive\\data\\Entity');
                }else{
                    return self::$list[$key][$names[1]];
                }
            }
        }
        return $result;
    }

    /**
     * Запись результата выборки в буфер
     * Сложные выборки могут дополнительно образовывать буфер простых выборок
     * @param $result Результат выборки
     * @param $cond Условие выборки
     */
    static function set($result, $cond)
    {
        $key = empty($cond['key'])?false:$cond['key'];
        if (!empty($cond['key'])) $cond['key'] = false;
        if (empty($cond['access'])) $cond['access'] = true;
        // Группа результатов
        if (is_array($cond['from'])){
            self::$list[json_encode($cond)] = $result;
            if (empty($cond['limit'])){
                foreach ($result as $key => $group){
                    $cond['from'] = $key;
                    self::set($group, $cond);
                }
            }
        }else
        // ветка объектов
        if ($cond['select'][0] == 'tree' && empty($cond['select'][1]) && empty($cond['limit'])){
            $list_key = $cond;
            $list_key['select'][0] = 'children';
            self::setTree($result, $cond, $key, Data::read($cond['from'].'&comment=buffer tree', !empty($cond['access']))->attributes(), json_encode($list_key));
        }else
        // один объект
        if (isset($result['class'])){
            // Ключ uri
            //if ($result->isExist()){
            if (isset($result['uri'])){
                $cond['from'] = $result['uri'];
                $cond['select'] = array('self');
                $cond['depth'] = array(0,0);
                self::$list[json_encode($cond)] = $result;
                if (!empty($result['id'])){
                    // Если объект существует, то дополнительно ключ id
                    $cond['from'] = $result['id'];
                    /*if (!isset(self::$list[$bkey = json_encode($cond)])) */self::$list[$bkey = json_encode($cond)] = $result;
                }
            }
            //}
        }else
        if (is_array($result)){
            // массив объектов
            if ($key!='name' && $cond['select'][0] == 'children' && empty($cond['select'][1]) && $cond['depth'][0]==1 && $cond['depth'][1] == 1){
                $list = array();
                foreach ($result as $obj){
                    $list[$obj['name']] = $obj;
                }
                $result = $list;
            }
            self::$list[json_encode($cond)] = $result;
            self::setList($result, $cond);
        }else{
            //self::$list[json_encode($cond)] = $result;
        }
    }

    /**
     * Запись списка объектов в буфер
     * @param array $objects Список объектов
     * @param array $cond Услвоие, которым выбраны объекты
     */
    private static function setList($objects, $cond)
    {
        if (is_array($objects)){
            $ocond = $cond;
            $ocond['select'] = array('self');
            $ocond['depth'] = array(0,0);
            $ocond['where'] = false;
            if (!empty($ocond['limit'])) $ocond['limit'] = false;
            if (!empty($ocond['order'])) $ocond['order'] = false;
            foreach ($objects as $obj){
                $ocond['from'] = $obj['id'];
                /*if (!isset(self::$list[$ke]y = json_encode($ocond)])) */self::$list[json_encode($ocond)] = $obj;
                $ocond['from'] = $obj['uri'];
                /*if (!isset(self::$list[$key = json_encode($ocond)]))*/ self::$list[json_encode($ocond)] = $obj;
            }
        }
    }

    /**
     * Запись в буфер дерева объектов
     * @param array $objects Объект или список объектов
     * @param array $cond Услвоие, которым выбраны объекты
     * @param $key
     * @param $from
     * @param null|string $listkey
     */
    private static function setTree($objects, $cond, $key, $from, $listkey = null)
    {
        $id = $from['id'];
        $uri = $from['uri'];
        if (isset($objects['class'])){
            // буфер всего дерева в виде списка
            if ($listkey) self::$list[$listkey][] = $objects;
            // бефер дерева с глубиной от 0
            $cond['depth'][0] = 0;
            $cond['from'] = $id;
            /*if (!isset(self::$list[$bkey = json_encode($cond)])) */self::$list[json_encode($cond)] = $objects;
            $cond['from'] = $uri;
            /*if (!isset(self::$list[$bkey = json_encode($cond)])) */self::$list[json_encode($cond)] = $objects;
            $ocond = $cond;
            $ocond['select'] = array('self');
            $ocond['depth'] = array(0,0);
            $ocond['where'] = false;
            if (!empty($ocond['limit'])) $ocond['limit'] = false;
            if (!empty($ocond['order'])) $ocond['order'] = false;
            /*if (!isset(self::$list[$bkey = json_encode($ocond)])) */self::$list[json_encode($ocond)] = $objects;
            $ocond['from'] = $id;
            /*if (!isset(self::$list[$bkey = json_encode($ocond)])) */self::$list[json_encode($ocond)] = $objects;
            if (isset($objects['children'])){
                $objects = $objects['children'];
            }else{
                $objects = array();
            }
            $cond['depth'][0] = 1;
        }
        if ($cond['depth'][1]>=1){
            // Буфер списка с глубиной от 1
            $ocond = $cond;
            $ocond['from'] = $id;
            $ocond['select'] = array('children');
            $ocond['depth'] = array(1,1);
            self::$list[json_encode($ocond)] = $objects;
            $ocond['from'] = $uri;
            self::$list[json_encode($ocond)] = $objects;
            unset($ocond);
            // Буфер дерева с глубиной от 1
            $cond['from'] = $id;
            self::$list[json_encode($cond)] = $objects;
            $cond['from'] = $uri;
            self::$list[json_encode($cond)] = $objects;
            // Если конечная глубина больше 1, то буфер подветки
            foreach ($objects as $obj){
                if ($cond['depth'][1] != Entity::MAX_DEPTH) $cond['depth'] = array(0, $cond['depth'][1]-1);
                self::setTree($obj, $cond, $key, $obj, $listkey);
            }
        }
    }

    /**
     * Очистка буфера
     */
    public static function clear()
    {
        self::$list = array();
    }
}