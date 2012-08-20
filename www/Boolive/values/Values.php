<?php
/**
 * Значения, требующие проверки или фильтра.
 *
 * В объект помещается любое значение и правило по умолчанию на значение.
 * Из объекта значение возвращается отфильтрованным по указанному правилу.
 * Если значение является массивом, то можно обращаться к его элементам, но они будут возвращаться в виде объектов Values,
 * таким образом сохраняется полная изоляция опасных значений.
 *
 * @link http://boolive.ru/createcms/dangerous-data
 * @version 3.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\values;

use ArrayAccess, IteratorAggregate, ArrayIterator, Countable,
    Boolive\errors\Error,
    Boolive\develop\ITrace;

class Values implements IteratorAggregate, ArrayAccess, Countable, ITrace
{
    /** @var mixed|array Значение */
    private $_value;
    /** @var \Boolive\values\Rule Правило по умолчанию для значения */
    protected $_rule;
    /** @var bool Признак, отфильтрованы значения (true) или нет (false)? */
    protected $_filtered;
    /** @var array Объекты \Boolive\values\Values для возвращения элементов при обращении к ним, если $this->_value массив*/
    protected $_interfaces;
    /** @var \Boolive\values\Values Родитель объекта для оповещения об изменениях значения */
    protected $_maker;
    /** @var string Имя для элемента в родителе */
    protected $_name;

    /**
     * Конструктор
     * @param null|mixed $value Значение
     * @param null|\Boolive\values\Rule $rule Правило проверки значений по умолчанию
     */
    public function __construct($value = null, $rule = null)
    {
        $this->_value = $value;
        $this->_rule = $rule;
        $this->_filtered = false;
    }

    /**
     * Внутренний выбор значения
     * @return array|mixed|null
     */
    protected function getValue(){
        if (isset($this->_maker, $this->_name)){
            $parent = $this->_maker->getValue(); // Берется значения родителя
            return isset($parent[$this->_name])? $parent[$this->_name] : null; // Из него выбирается своё
        }else{
            return $this->_value;
        }
    }

    /**
     * Установка правила проверки по умолчанию
     * Применяется в наследуемых классах для предопредления правила
     */
    protected function defineRule()
    {
        // Одномерный ассоциативный массив строк
        $this->_rule = Rule::arrays(Rule::string(), true);
    }

    /**
     * Правило проверки по умолчанию
     * Правило используется если в аргументах методов не указывается правило
     * @param null $name Ключ элемента, для которого требуется правило. Если не указан, то возвращается общее правило
     * @return \Boolive\values\Rule
     */
    public function getRule($name = null)
    {
        // Если правила нет по умолчанию, то пробуем его установить
        if (!isset($this->_rule)) $this->defineRule();
        // Правило на элемент
        if (isset($name)){
            if ($this->_rule instanceof Rule){
                // Если правило на массив
                if (isset($this->_rule->arrays)){
                    // Нормализация аргументов
                    $args = array(array(), null, false);
                    foreach ($this->_rule->arrays as $arg){
                        if (is_array($arg)){
                            $args[0] = $arg;
                        }else
                        if ($arg instanceof Rule){
                            $args[1] = $arg;
                        }else
                        if (is_bool($arg)){
                            $args[2] = $arg;
                        }
                    }
                    $value = $this->getValue();
                    // Если элемент массив и правило рекурсивно, то отдаём всё правило
                    if (isset($value[$name]) && is_array($value[$name]) && $args[2]){
                        return $this->_rule;
                    }
                    // Выбор правила для элемента
                    if (is_array($args[0]) && isset($args[0][$name])){
                        // Правило на элемент
                        return $args[0][$name];
                    }
                    // Если правило рекурсивно и есть общее на все элементы, то создаётся два варианта правила
                    if ($args[1] instanceof Rule && $args[2]){
                        return Rule::any($args[1], $this->_rule);
                    }
                    // Правило по умолчанию, если есть
                    return $args[1] instanceof Rule ? $args[1] : null;
                }
            }
            return null;
        }
        return $this->_rule;
    }

    /**
     * Установка значения
     * @param mixed $value
     */
    public function set($value)
    {
        if (isset($this->_maker, $this->_name)){
            $parent = $this->_maker->getValue(); // Берется значение родителя
            $parent[$this->_name] = $value; // В него добавляется своё
            $this->_maker->set($parent); // Значение родителя переписывается
        }else{
            $this->_value = $value;
        }
        //$this->_interfaces = array();
        $this->_filtered = false;
    }

    /**
     * Выбор значения с применением правила
     * @param null|\Boolive\values\Rule $rule
     * @param null $error
     * @return mixed
     */
    public function get($rule = null, &$error = null)
    {
        // Если не указано правило и значение уже отфильтровано, то повторно фильтровать не нужно
        if (!$rule && $this->_filtered) return $this->getValue();
        // Если правило не указано, то берём по умолчанию
        if (!$rule) $rule = $this->getRule();
        // Если правило определено
        if ($rule instanceof Rule){
            return Check::filter($this->getValue(), $rule, $error);
        }
        $error = new Error(array('Нет правила'), 'NO_RULE');
        return null;
    }

    /**
     * Фильтр всех значений в соответствии с правилом по умолчанию.
     * Текущее значение заменяется на отфильтрованное, если нет ошибок.
     * Если ошибок нет, то возвращается true, иначе false.
     * После успешного фильтра доступ к значениям будет выполняться без проверок, пока не произойдут какие-либо изменения.
     * @param null $errors Ошибки после проверки
     * @return bool
     */
    public function filter(&$errors = null)
    {
        $values = $this->get(null, $errors);
        if (!isset($errors)){
            $this->set($values);
            $this->_filtered = true;
        }else{
            $this->_filtered = false;
        }
        return $this->_filtered;
    }

    /**
     * Проверка значения
     * @param \Boolive\values\Rule $rule Правило проверки
     * @param \Boolive\errors\Error | null $error Ошибки после проверки
     * @return bool
     */
    public function check($rule = null, &$error = null)
    {
        $this->get($rule, $error);
        return !isset($error);
    }

    /**
     * Удяляет все элементы, имена которых не указаны в аргументах этого метода
     * Ключи элементов передаются в массиве или через запятую.
     * Если текущее значение не массив, то оно будет заменено на пустой массив
     * @example
     * 1. choose(array('name1', 'name2'));
     * 2. choose('name1', 'name2', 'name3');
     * @return \Boolive\values\Values Ссылка на себя
     */
    public function choose()
    {
        $value = $this->getValue();
        if (!is_array($value)) $value= array();
        $arg = func_get_args();
        if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
        $list = array();
        foreach ($arg as $name){
            if (array_key_exists($name, $value)) $list[$name] = $value[$name];
        }
        $this->set($list);
        return $this;
    }

    /**
     * Возвращает все значения в виде массива объектов Values
     * Если текущее значение не массив, то оно будет возвращено в качестве нулевого элемента массива
     * @return array Массив объектов Values
     */
    public function getValues()
    {
        $values = $this->getValue();
        $v = is_array($values)? $values : array($values);
        $r = array();
        foreach ($v as $name => $value){
            $r[$name] = new Values($value);
        }
        return $r;
    }

    /**
     * Возвращает массив ключей
     * @return array
     */
    public function getKeys()
    {
        return array_keys((array)$this->getValue());
    }

    /**
     * Создание копии с указанием нового правила по умолчанию
     * @param null|\Boolive\values\Rule $rule
     * @return \Boolive\values\Values
     */
    public function getCopy($rule = null)
    {
        return new static($this->getValue(), $rule);
    }

    /**
     * Замещение значений своих элементов соответствующими по ключам значениями из переданного массива.
     * Если ключ есть в переданном массиве, но отсутствует у себя, то он будет создан.
     * Если ключ присутствует только у себя, то элемент с таким ключом сохранится как есть.
     * @param array $list Новые значения
     */
    public function replaceArray($list)
    {
        if ((is_array($list)||$list instanceof \ArrayAccess) &&!empty($list)){
            $value = $this->getValue();
            if (!is_array($value)) $value = array();
            foreach ($list as $key => $value){
                $value[$key] = $value;
            }
            $this->set($value);
        }
    }

    /**
     * Смещение числовых ключей
     * Числовие ключи уменьшаются на $shift. Для увеличения ключей $shift должен быть отрицательным
     * @param int $shift Размер смещения
     */
    public function shiftKeys($shift = 0)
    {
        $value = $this->getValue();
        if (is_array($value)){
            if ($shift){
                $list = array();
                foreach ((array)$this as $key => $value){
                    if (is_numeric($key)){
                        $list[-$shift + $key] = $value[$key];
                    }else{
                        $list[$key] = $value[$key];
                    }
                }
                $this->set($list);
            }
        }
    }

    /**
     * Признак, отфильтрованы значения или нет
     * @return bool
     */
    public function isFiltered()
    {
        return $this->_filtered;
    }

    /**
     * Количество элементов.
     * Если значение не массив, то всегда возвращается 1
     * @return int
     */
    public function count()
    {
        $value = $this->getValue();
        return is_array($value) ? count($value) : 1;
    }

    /**
     * Получение элемента массива в виде объекта Values
     * Если значение не являются массивом, то оно будет заменено на пустой массив.
     * Если элемента с указанным именем нет, то он будет создан со значением null
     * @param mixed $name Ключ элемента
     * @return \Boolive\values\Values
     */
    public function offsetGet($name)
    {
        if (is_null($name)) return $this;
        if (!isset($this->_interfaces[$name])){
            // Создание объекта Values для запрашиваемого значения.
            // Объекту устанавливается правило в соответсвии с правилом данного объекта Values и запрашиваемого элемента
            $this->_interfaces[$name] = $interface = new static(null, $this->getRule($name));
            $interface->_maker = $this;
            $interface->_name = $name;
        }
        return $this->_interfaces[$name];
    }

    /**
     * Установка значения элементу массива
     * Если текущее значение не является массивом, то оно будет заменено на пустой массив.
     * @param mixed $name Ключ элемента
     * @param mixed $value Новое значения элемента
     */
    public function offsetSet($name, $value)
    {
        $v = $this->getValue();
        if (!is_array($v)) $v = array();
        if (is_null($name)){
            $v[] = $value;
        }else{
            $v[$name] = $value;
            if (isset($this->_interfaces[$name])) unset($this->_interfaces[$name]);
        }
        $this->set($v);
    }

    /**
     * Проверка существования элемента
     * @param mixed $name
     * @return bool
     */
    public function offsetExists($name)
    {
        $v = $this->getValue();
        return is_array($v) && array_key_exists($name, $v);
    }

    /**
     * Проверка элемента, пустое у него значение или нет
     * @param string $name Ключ проверяемого элемента
     * @param bool $number Признак, проверять как число или строку. Если $number==true, то '0' будет считаться пустым значенеим
     * @return bool
     */
    public function offsetEmpty($name, $number = false)
    {
        $v = $this->getValue();
        if (is_array($v) && $this->offsetExists($name)){
            if (is_string($v[$name])){
                if ($number) {
                    $value = trim($v[$name]);
                    return empty($value);
                }
                return trim($v[$name]) == '';
            }
            return (empty($v[$name]));
        }
        return true;
    }

    /**
     * Удаление элемента
     * @param mixed $name
     */
    public function offsetUnset($name)
    {
        $v = $this->getValue();
        if (is_array($v) && $this->offsetExists($name)){
            $this->_interfaces[$name]->_maker = null;
            unset($this->_interfaces[$name]);
            unset($v[$name]);
            $this->set($v);
        }
    }

    /**
     * Удаление всех элементов.
     * Обнуление значения.
     */
    public function offsetUnsetAll()
    {
//        foreach ($this->_interfaces as $i) $i->_maker = null;
        $this->set(null);
    }

    /**
     * Удаление элементов по именам
     * Ключи передаются в массиве или через запятую.
     * @example
     * 1. offsetUnsetList(array('name1', 'name2'));
     * 2. offsetUnsetList('name1', 'name2', 'name3');
     */
    public function offsetUnsetList()
    {
        $v = $this->getValue();
        if (is_array($v)){
            $arg = func_get_args();
            if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
            foreach ($arg as $name){
                $this->_interfaces[$name]->_maker = null;
                unset($this->_interfaces[$name]);
                unset($v[$name]);
            }
            $this->set($v);
        }
    }

    /**
     * Итератор для циклов
     * На каждый элемент массива (если текущее значение массив) создаётся объект Values.
     * Возвращается итератор на массив объектов Values.
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getValues());
    }

    /**
     * Перегрузка метода получения элемента как свойства
     * Всегда возвращется Values, даже если нет запрашиваемого элемента (Values будет пустым тогда)
     * @example $v = $values->v1;
     * @param string $name Ключ элемента
     * @return array|\Boolive\values\Values
     */
    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Перегрузка установки значения элементу как свойства
     * @example $values->v1 = "value";
     * @param string $name Ключ элемента
     * @param mixed $value Значение
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * Перегрузка функции isset() для проверки существоания элемента
     * @example isset($values->v1);
     * @param string $name Ключ элемента
     * @return bool
     */
    public function __isset($name)
    {
        $v = $this->getValue();
        return is_array($v) && isset($v['values']);
    }

    /**
     * Перегрузка функции unsset() для удаления элемента
     * @example unsset($values->v1);
     * @param string $name Ключ элемента
     * @return bool
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    /**
     * Отфильтрованное значение в виде строки
     * Значение фильтруется правилом по умолчанию и конвертируется в строку
     * @example
     * echo $values->v1;
     * $s = (string)$values->v1;
     * @return string
     */
    public function __toString()
    {
        return (string)$this->get();
    }

    /**
     * Клонирование объекта
     */
    public function __clone()
    {
        $this->_value = $this->getValue();
        if (is_array($this->_value)){
            foreach ($this->_value as $key => $item){
                if (is_object($item)){
                    $this->_value[$key] = clone $item;
                }
            }
        }else
        if (is_object($this->_value)){
            $this->_value = clone $this->_value;
        }
        $this->_interfaces = array();
        $this->_maker = null;
    }

    /**
     * Выбор значения, фильтруя его
     * Правило фильтра создаётся из имени вызванного метода и его аргументов,
     * таким образом, правило может иметь только один фильтр.
     * Используется как альтернатива get() в простых ситуациях
     * @param string $name Имя метода
     * @param array $args Аргументы
     * @return null
     */
    public function __call($name, $args)
    {
        $rule = new Rule();
        $rule->__set($name, $args);
        return $this->get($rule);
    }

    /**
     * Возвращает свойства объекта для трассировки
     * @return array
     */
    public function trace()
    {
        return $this->_value;
    }
}