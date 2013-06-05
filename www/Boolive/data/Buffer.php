<?php
/**
 * Бефер данных
 * Содержит используемые экземпляры объектов для оптимизации повтороного обращения к ним
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
     * @return mixed|null Результат выборки или null, если результата нет в буфере
     */
    static function get($cond, $check_exists = true)
    {
        $result = null;
        // Ключ буфера из условия выборки.
        $buffer_cond = $cond;
        if (!empty($cond['key'])) $buffer_cond['key'] = false;
        $buffer_key = json_encode($buffer_cond);
        if (isset(self::$list[$buffer_key]) || array_key_exists($buffer_key, self::$list)){
            $result = self::$list[$buffer_key];
            if (is_array($result) && reset($result) instanceof Entity){
                if (empty($cond['key'])){
                    return array_values($result);
                }
                $list = array();
                foreach ($result as $obj){
                    $list[$obj->attr($cond['key'])] = $obj;
                }
                $result = $list;
            }
        }else
        // Если есть буфер выборки подчиенных, в которых должен оказаться искомый объект,
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
                    return new Entity(array('name'=>$names[1], 'uri'=>$cond['from'], 'owner'=>$cond['owner'], 'lang'=>$cond['lang']));
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

        // ветка объектов
        if ($cond['select'][0] == 'tree' && empty($cond['select'][1]) && empty($cond['limit'])){
            self::setTree($result, $cond, $key, Data::read($cond['from'].'&comment=buffer tree', !empty($cond['access'])));
        }else
        // один объект
        if ($result instanceof Entity){
            // Ключ uri
            $cond['from'] = $result->uri();
            self::$list[json_encode($cond)] = $result;
            if ($result->isExist()){
                // Если объект существует, то дополнительно ключ id
                $cond['from'] = $result->id();
                self::$list[json_encode($cond)] = $result;
            }
        }else{
            // массив объектов
            if ($key!='name' && $cond['select'][0] == 'children' && empty($cond['select'][1]) && $cond['depth'][0]==1 && $cond['depth'][1] == 1){
                $list = array();
                foreach ($result as $obj){
                    $list[$obj->attr('name')] = $obj;
                }
                $result = $list;
            }
            self::$list[json_encode($cond)] = $result;
            self::setList($result, $cond);
        }
    }

    /**
     * Запись списка объектов в буфер
     * @param $objects
     * @param $cond
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
                $ocond['from'] = $obj->id();
                self::$list[json_encode($ocond)] = $obj;
                $ocond['from'] = $obj->uri();
                self::$list[json_encode($ocond)] = $obj;
            }
        }
    }

    /**
     * Запись в буфер дерева объектов
     * @param $objects
     * @param $cond
     * @param $key
     * @param $from
     */
    private static function setTree($objects, $cond, $key, $from)
    {
        if ($objects instanceof Entity){
            // бефер дерева с глубиной от 0
            $cond['depth'][0] = 0;
            $cond['from'] = $from->id();
            self::$list[json_encode($cond)] = $objects;
            $cond['from'] = $from->uri();
            self::$list[json_encode($cond)] =  $objects;
            $ocond = $cond;
            $ocond['select'] = array('self');
            $ocond['depth'] = array(0,0);
            $ocond['where'] = false;
            if (!empty($ocond['limit'])) $ocond['limit'] = false;
            if (!empty($ocond['order'])) $ocond['order'] = false;
            self::$list[json_encode($ocond)] = $objects;
            $ocond['from'] = $from->id();
            self::$list[json_encode($ocond)] = $objects;
            $objects = $objects->children($key);
            $cond['depth'][0] = 1;
        }
        if ($cond['depth'][1]>=1){
            // Буфер списка с глубиной от 1
            $ocond = $cond;
            $ocond['from'] = $from->id();
            $ocond['select'] = array('children');
            $ocond['depth'] = array(1,1);
            self::$list[json_encode($ocond)] = $objects;
            $ocond['from'] = $from->uri();
            self::$list[json_encode($ocond)] = $objects;
            unset($ocond);
            // Буфер дерева с глубиной от 1
            $cond['from'] = $from->id();
            self::$list[json_encode($cond)] = $objects;
            $cond['from'] = $from->uri();
            self::$list[json_encode($cond)] = $objects;
            // Если конечная глубина больше 1, то буфер подветки
            foreach ($objects as $obj){
                if ($cond['depth'][1] != Entity::MAX_DEPTH) $cond['depth'] = array(0, $cond['depth'][1]-1);
                self::setTree($obj, $cond, $key, $obj);
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