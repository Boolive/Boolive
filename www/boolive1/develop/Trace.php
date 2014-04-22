<?php
/**
 * Трассировщик
 * @example
 * trace($value); // Вывод форматированного значения переменной
 * trace($value)->log(); // Вывод и запись в php лог форматированного значения
 * Trace::group()->db->query->set($sql); // Добавление значения $sql в группу db->query
 * Trace::group()->group('db')->group('query')->set($sql); // ...альтернативное указание групп
 * Trace::group()->db->out(); // Вывод группы db
 * Trace::group()->out(); // Вывод всех трассировок
 * @version 1.3
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\develop;

use Boolive\file\File;

class Trace
{
    /** @var \Boolive\develop\Trace Список всех объектов трассировки */
    static private $trace;
    /** @var mixed Трассируемое значение */
    private $value;
    /** @var mixed Ключ трассировки (именование) */
    private $key;
    /** @var array Список вложенных трассировок */
    private $children = array();
    /** @var string Форматированное значение (кэш) */
    private $format;

    /**
     * Конструктор объекта трассировки
     * @param string $key Ключ новой трассировки
     * @param $value Значения для трассировки
     * @param bool $clone Признак, клонировать значение, если является объектом?
     */
    function __construct($key = null, $value = null, $clone = true)
    {
        $this->key = $key;
        $this->set($value, $clone);
    }

    /**
     * Установка трассируемого значения.
     * @param mixed $value Значения для трассировки
     * @param bool $clone Признак, клонировать значение, если является объектом?
     * @return \Boolive\develop\Trace
     */
    function set($value, $clone = true)
    {
        if ($clone && is_object($value) && !$value instanceof \Exception){
            $this->value = clone $value;
        }else{
            $this->value = $value;
        }
        $this->format = null;
        return $this;
    }

    /**
     * Возвращение трассируемого значения
     * @return mixed
     */
    function get()
    {
        return $this->value;
    }

    /**
     * Возвращение ключа трассировки
     * @return string
     */
    function key()
    {
        return $this->key;
    }

    /**
     * Запись форматированного значения в лог файл
     * @return \Boolive\develop\Trace
     */
    function log()
    {
        //error_log(self::Format($this, $trace_buf = array(), '  ', false));
        File::create(date('Y.m.d G:i.s').' '.self::Format($this, $trace_buf = array(), '  ', false)."\r\n", DIR_SERVER_TEMP.'trace.log', true);
        return $this;
    }

    /**
     * Вывод форматированного значения в HTML
     * @return \Boolive\develop\Trace $this
     */
    function out()
    {
        echo '<pre>'.self::Format($this).'</pre>';
        return $this;
    }

    /**
     * Получения вложенного объекта трассировки
     * @param string|null $key Ключ трассировки. Если не существует, то создаётся новый объект трассировки.
     * @return \Boolive\develop\Trace Объект трассировки
     */
    function group($key = null)
    {
        if (empty($key)) $key = sizeof($this->children);
        if (!isset($this->children[$key])){
            $this->children[$key] = new Trace($key, null);
        }
        return $this->children[$key];
    }

    /**
     * Получения вложенного объекта трассировки
     * @param string|null $key Ключ трассировки, если null, то создаётся новый объект трассировки с целочисленным ключом
     * @return \Boolive\develop\Trace Объект трассировки
     */
    function __get($key = null)
    {
        return $this->group($key);
    }

    /**
     * Удаление вложенного объекта трассировки
     * @param $key Ключ трассировки
     */
    function __unset($key)
    {
        if (!isset($this->children[$key])){
            unset($this->children[$key]);
        }
    }

    /**
     * Корневой объект трассировки
     * @param null $key Ключ подчиенного объекта трассировки. Если отсутствует, то будет создан
     * @return \Boolive\develop\Trace
     */
    static function groups($key = null)
    {
        if (!isset(self::$trace)) self::$trace = new Trace('TRACE');
        if (isset($key)){
            return self::$trace->__get($key);
        }else{
            return self::$trace;
        }
    }

    /**
     * Оформление значения
     * @param $var
     * @return string
     */
    static private function style($var, $html = true)
    {
        $style='';
        if (is_string($var)){
            $style.='color:#008100;';
            $var = '\''.($html ? htmlspecialchars($var, ENT_COMPAT, 'UTF-8') : $var).'\'';
        }else
        if (is_numeric($var)||is_nan($var)){
            $style.='color:#FF0000;';
        }else
        if (is_bool($var)||is_null($var)){
            $style.='color:#252FFF;';
            if (is_bool($var)) $var = $var ? 'true' : 'false';
            if (is_null($var)) $var = 'null';
        }else{
            return $var;
        }
        return $html ? '<span style="'.$style.'">'.$var.'</span>' : $var;
    }

    /**
     * Форматирование значения
     *
     * @param mixed $var Значение для форматировния
     * @param array $trace_buf Буфер вывода (результата). Используется в рекурсии для предотвращения обратных ссылок
     * @param string $pfx Префикс для строки вывода. Для имитации иерархии
     * @param bool $html Форматировать в html?
     * @return string
     */
    static function format($var, &$trace_buf = array(), $pfx = '  ', $html = true)
    {
        $sp = '| ';
        $sp2 = '. ';
        $sp3 = '  ';
        $out = '';
        if ($var instanceof Trace){
            /*if (is_string($var->key))*/ $out.= '# '.$var->key."\n";
            if (empty($var->children)){
                $out.= $pfx.self::format($var->value, $trace_buf, $pfx.$sp3, $html);
            }else{
                if (isset($var->value)){
                    $out.= $pfx.self::format($var->value, $trace_buf, $pfx, $html)."\n";
                }
                $cnt = sizeof($var->children);
                foreach ($var->children as $var){
                    $cnt--;
                    $out.= $pfx.self::format($var, $trace_buf, ($cnt?$pfx.$sp:$pfx.$sp3), $html)."\n";
                }
                return rtrim($out);
            }
        }else
        // если не определена или null
        if (!isset($var) || is_null($var)){
            $out.= self::style(null, $html);
        }else
        // Строка, число, булево
        if (is_scalar($var)){
            $out.= self::style($var, $html);
        }else
        // если ресурс
        if (is_resource($var)){
            $out.= '{resource}';
        }else
        // если массив
        if (is_array($var)){
            $cnt = sizeof($var);
            if ($cnt == 0){
                $out.='{Array} ()';
            }else{
                $out.= '{Array}';
                foreach ($var as $name => $value){
                    $cnt--;
                    if ($cnt){
                        $new_pfx = $pfx.' '.$sp;
                    }else{
                        $new_pfx = $pfx.' '.$sp3;
                    }
                    $out.= "\n".$pfx.'['.self::style($name, $html).'] => '.self::format($value, $trace_buf, $new_pfx, $html);
                }
            }
        }else
        // Если объект
        if (is_object($var)){
            $class_name = get_class($var);
            if (isset($trace_buf[spl_object_hash($var)])){
                //if ($var instanceof \Boolive\data\Entity){
                //	$list = array('id' => $var['id'], 'name'=> $var->name());
                //}else{
                //	$list = array();
                //}
                $out.='{'.$class_name.'} уже отображен';
            }else{
                $trace_buf[spl_object_hash($var)] = true;
                $out.= '{'.$class_name.'}';
                while ($class_name = get_parent_class($class_name)){
                    $out.= ' -> {'.$class_name.'}';
                }
                $list = self::objToArray($var);
            }
            if (isset($list)){
                if (!is_array($list)){
                    $out.= "\n".$pfx.self::format($list, $trace_buf, $pfx, $html);
                }else{
                    $cnt = sizeof($list);
                    if ($cnt > 0){
                        foreach ($list as $name => $value){
                            $cnt--;
                            if ($cnt){
                                $new_pfx = $pfx.' '.$sp;
                            }else{
                                $new_pfx = $pfx.' '.$sp3;
                            }
                            $out.= "\n".$pfx.'['.$name.'] => '.self::format($value, $trace_buf, $new_pfx, $html);
                        }
                    }
                }
            }
        }
        // Иначе
        else{
            $out.= $var;
        }
        return $out;
    }

    /**
     * Преобразование объекта в массив
     * @param object|ITrace $object
     * @return array
     */
    static private function objToArray(&$object)
    {
        if ($object instanceof ITrace){
            $arr = $object->trace();
            if (!is_array($arr)) return $arr;
        }else{
            $arr = (array)$object;
        }
        $result = array();
        while (list ($key, $value) = each($arr)){
            $keys = explode("\0", $key);
            $clear_key = $keys[sizeof($keys) - 1];
            $result[$clear_key] = &$arr[$key];
        }
        return $result;
    }
}
