<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use Boolive\functions\F,
    Boolive\errors\Error,
    Boolive\develop\Trace;

class Data
{
    /** @const  Файл конфигурации хранилищ */
    const CONFIG_FILE = 'config.data.php';
    /** @var array Конфигурация хранилищ */
    private static $config_stores;
    /** @var array Экземпляры хранилищ */
    private static $stores;
    /** @var array Условия, по которым выбранны объекты, для которых объединять последующие выборки */
    private static $group_cond = array();

    static function activate()
    {
        // Конфиг хранилищ
        self::$config_stores = F::loadConfig(DIR_SERVER.self::CONFIG_FILE, 'stores');
    }

    /**
     * Выбор объектов по условию
     * <h3>Пример условия в виде массива</h3>
     * <code>
     * $cond = array(
     *     'from' => '/Interfaces',                     // Выбор объектов из /Interfaces
     *     'depth' => 3,                                // Глубина выбора из from. Если 0, то выбирается from, а не его подчиненные
     *     'select' => 'children',                      // Что выбирать?
     *     'where' => array(                            // Услвоия выборки объединенные логическим AND
     *         array('attr', 'uri', '=', '?'),          // Сравнение атрибута
     *         array('not', array(                      // Отрицание всех условий
     *             array('attr', 'value', '=', '%?%')
     *         )),
     *         array('any', array(                      // Услвоия, объединенные логическим OR
     *             array('child', array(                // Условия на подчиненный объект
     *                 array('attr', 'value', '>', 10),
     *                 array('attr', 'value', '<', 100),
     *             ))
     *         )),
     *         array('is', '/Library/object')          // Кем объект является? Проверка наследования (прототипирования)
     *     ),
     *     'order' => array(                           // Сортировка
     *         array('uri', 'DESC'),                   // по атрибуту uri
     *         array('childname', 'value', 'ASC')      // по атрибуту value подчиненного с имененм childname
     *     ),
     *     'limit' => array(10, 15),                    // Ограничение - выбирать с 10-го не более 15 объектов
     *     'key' => 'name',                             // Атрибут, который использовать для ключей массива результата
     * );
     * </code>
     * <h2>Условие строкой:</h2><pre>
     * $cond = "
     *   from(/Contents)
     *   select(children)
     *   depth(max)
     *   where(all(
     *       attr(uri,like,%А%),
     *       not(attr(value,eq, m))
     *       any(
     *           child(title, all(
     *               attr(value,gte,10)
     *               attr(value,lt,100)
     *           ))
     *       ),
     *       is(/Library/object)
     *   ))
     *   order((uri,desc),(childname,value,asc))
     *   limit(10,15)
     * ";</pre>
     * <h2>Условие в URL формате</h2><pre>
     * $cond = "
     *   /Content&
     *   depth=max&
     *   select=children&
     *   where=all(
     *       attr(uri,like,%25А%25),
     *       not(attr(value,eq, m))
     *       any(
     *           child(title, all(
     *               attr(value,gte,10)
     *               attr(value,lt,100)
     *           ))
     *       ),
     *       is(/Library/object)
     *   )&
     *   order=(uri,desc),(childname,value,asc)&
     *   limit=10,15
     * ";</pre>
     * @param string $cond Условие выбора
     * @param bool $access Признак, учитывать или нет права доступа на чтение?
     * @param bool $index Признак, выполнять индексирование данных перед поиском?
     * @param bool $only_cache Признак, выбирать только из буфера?
     * @return array|Entity|mixed|null В зависимости от услвоия выбора результатом будет
     * а) массив найденных объектов
     * б) объект,
     * в) вычисляемое значение, например, количество или null
     * г) массив со значенями указанного атрибута найденных объектов
     * д) значение указанного атриубта объекта
     */
    static function read($cond = '', $access = true, $index = true, $only_cache = false)
    {
        if ($cond == Entity::ENTITY_ID){
            return new Entity(array('uri'=>'/'.Entity::ENTITY_ID, 'id'=>Entity::ENTITY_ID));
        }
        if ($cond == 'null' || !isset($cond)){
            return null;
        }
        $cond = self::normalizeCond($cond, array(), true, true);

        // Контроль доступа в условие выбора
        if ($access) $cond['access'] = true;
        if (isset($cond['comment'])){
            $comment = ''.$cond['comment'];
            unset($cond['comment']);
        }else{
            $comment = 'no comment';
        }
        // Выполнять последующие группировку выборок с выбранными объектами?
        if (isset($cond['group'])){
            unset($cond['group']);
            $group = true;
        }else{
            $group = false;
        }
        // Ограничения для возвращаемого результата
        if (isset($cond['return'])){
            $return = $cond['return'];
            unset($cond['return']);
        }else{
            $return = null;
        }
        // Уровень кэширования. По умолчанию 1.
        if (isset($cond['cache'])){
            $cache = max(min(2, $cond['cache']),0);
            unset($cond['cache']);
        }else{
            $cache = 1;
        }
        $what = ($cond['select'][0] == 'exists' || $cond['select'][0] == 'count')? $cond['select'][1] : $cond['select'][0];
        // Создаём групповое условие (множественный $cond['from']
        // Попытка сгруппировать выборку с возможными будущими подобными выборками
        $group_cond = $cond;
        // Группировку осуществлять, если from = Entity или array(Entity, name) для self
        // Это позволит правильно определять объект по услвоию выборки которого делать группу.
        // Парсер условия не должен нормализовывать from в этом случаи
        if ($cond['from'] instanceof Entity){
            $from_type = 'entity';
        }else
        if (is_array($cond['from'])  && count($cond['from'])==2 && $cond['from'][0] instanceof Entity && is_string($cond['from'][1])){
            $from_type = 'entity/name';
        }else{
            $from_type = '';
        }
        $cond['from'] = self::normalizeFrom($cond['from'], false);

        // Если выбор одного объекта, то группировка по родителям выбранных ранее вместе
        if ($what == 'self' && ($from_type == 'entity' || $from_type == 'entity/name')){
            if ($from_type == 'entity'){
                $parent = $group_cond['from']->parent(null, false);
                $name = $group_cond['from']->name();
            }else{
                $parent = $group_cond['from'][0];
                $name = $group_cond['from'][1];
            }
            if ($parent){
                if (($parent_cond = $parent->cond()) && ($parent_cond['select'][0]!='self'|| is_array($parent_cond['from'])) && isset(self::$group_cond[json_encode($parent_cond)])){
                    if (self::compareCond($parent_cond, $group_cond, array(/*'from',*/ 'key'))){
                        $group_cond = $parent_cond;
                    }else{
                        if ($parent_cond['select'][0] == 'tree') $parent_cond['select'][0] = 'children';
                        $list = Buffer::getPlain($parent_cond);
                        if (count($list)>1){
                            $key_from_group = $parent->key().'/'.$name;
                            $group_cond['from'] = array();
                            $makefrom = function(&$group_cond, $list, $name) use (&$makefrom){
                                foreach ($list as $from){
                                    if (is_array($from)){
                                        if (isset($from['class_name'])){
                                            if (isset($from['id']) || isset($from['uri'])){
                                                $group_cond['from'][] = (isset($from['id'])?$from['id']:$from['uri']).'/'.$name;
                                            }
                                        }else{
                                            $makefrom($group_cond, $from, $name);
                                        }
                                    }
                                }
                            };
                            $makefrom($group_cond, $list, $name);
                        }
                    }
                }
            }
        }else
        // Выбор объекта, на которого ссылается from
        if ($what == 'link' && $from_type == 'entity'){
            // Пробуем сгруппировать
            if (($from_cond = $group_cond['from']->cond()) && isset(self::$group_cond[json_encode($from_cond)]) && ($from_cond['select'][0]!='self' || is_array($from_cond['from']))){
                $cond['from'] = $group_cond['from']->attr('is_link');
                if (self::compareCond($from_cond, $group_cond, array('from', 'key'))){
                    $group_cond = $from_cond;
                }else{
                    if ($from_cond['select'][0] == 'tree') $from_cond['select'][0] = 'children';
                    $list = Buffer::getPlain($from_cond);
                    if (count($list)>1){
                        $key_from_group = $group_cond['from']->attr('is_link');
                        $group_cond['from'] = array();
                        $makefrom = function(&$group_cond, $list) use (&$makefrom){
                            foreach ($list as $from){
                                if (is_array($from)){
                                    if (isset($from['class_name'])){
                                        if (!empty($from['is_link']) /*&& $from['is_link']!=Entity::ENTITY_ID*/) $group_cond['from'][] = $from['is_link'];
                                    }else{
                                        $makefrom($group_cond, $from);
                                    }
                                }
                            }
                        };
                        $makefrom($group_cond, $list);
                    }
                }
            }else{
                $group_cond['from'] = $cond['from'] = $group_cond['from']->attr('is_link');
            }
            $group_cond['select'] = $cond['select'] = array('self');
        }else
        // Выбор прототипа у которого берется значение по умолчанию
        if ($what == 'default_value_proto' && $from_type == 'entity'){
            // Пробуем сгруппировать
            if (($from_cond = $group_cond['from']->cond()) && isset(self::$group_cond[json_encode($from_cond)]) && ($from_cond['select'][0]!='self' || is_array($from_cond['from']))){
                $cond['from'] = $group_cond['from']->attr('is_default_value');
                if (self::compareCond($from_cond, $group_cond, array('from', 'key'))){
                    $group_cond = $from_cond;
                }else{
                    if ($from_cond['select'][0] == 'tree') $from_cond['select'][0] = 'children';
                    $list = Buffer::getPlain($from_cond);
                    if (count($list)>1){
                        $key_from_group = $group_cond['from']->attr('is_default_value');
                        $group_cond['from'] = array();
                        $makefrom = function(&$group_cond, $list) use (&$makefrom){
                            foreach ($list as $from){
                                if (is_array($from)){
                                    if (isset($from['class_name'])){
                                        if (isset($from['is_default_value']) && $from['is_default_value']!=$from['id'] /*&& $from['is_link']!=Entity::ENTITY_ID*/) $group_cond['from'][] = $from['is_default_value'];
                                    }else{
                                        $makefrom($group_cond, $from);
                                    }
                                }
                            }
                        };
                        $makefrom($group_cond, $list);
                    }
                }
            }else{
                $group_cond['from'] = $cond['from'] = $group_cond['from']->attr('is_default_value');
            }
            $group_cond['select'] = $cond['select'] = array('self');
        }else
        // Остальные выборки
        if ($from_type == 'entity'){
            // Группировка по объектам, выбранных ранее вместе с текущим from
            if (($from_cond = $group_cond['from']->cond()) && isset(self::$group_cond[json_encode($from_cond)]) && ($from_cond['select'][0]!='self' || is_array($from_cond['from']))){
                if (self::compareCond($from_cond, $group_cond, array('from', 'key'))){
                    $group_cond = $from_cond;
                }else{
                    if ($from_cond['select'][0] == 'tree') $from_cond['select'][0] = 'children';
                    $list = Buffer::getPlain($from_cond);
                    if (count($list)>1){
                        $key_from_group = $group_cond['from']->id();
                        $group_cond['from'] = array();
                        $makefrom = function(&$group_cond, $list) use (&$makefrom){
                            foreach ($list as $from){
                                if (is_array($from)){
                                    if (isset($from['class_name'])){
                                        if (isset($from['id'])) $group_cond['from'][] = $from['id'];
                                    }else{
                                        $makefrom($group_cond, $from);
                                    }
                                }
                            }
                        };
                        $makefrom($group_cond, $list);
                    }
                }
            }
        }
        // Если автоматическая группировка, то по такому услвоию тоже группировать потом
        if (isset($key_from_group)){
            $group_cond['from'] = self::normalizeFrom($group_cond['from'], false);
            self::$group_cond[json_encode($group_cond)] = true;
        }else{
            $group_cond['from'] = $cond['from'];
        }
        // Если явно указано, что по текущему услвоию потом группировать
        if ($group) self::$group_cond[json_encode($cond)] = true;

        // Из буфера сущностей
        if ($cache==2){
            $result = Buffer::getEntity($group_cond);
            if (isset($result)){
                if (PROFILE_DATA) Trace::groups('Data')->group('FROM BUFFER 2')->group($comment)->group()->set(F::toJSON($group_cond, false));
                if (isset($key_from_group) && is_array($result) && isset($result[$key_from_group])){
                    return $result[$key_from_group];
                }
                return $result;
            }
        }
        // Из буфера атрибутов если ещё не выбрали
        if ($cache && !isset($result)){
            $result = Buffer::getPlain($group_cond);
            if (isset($result)){
                if (PROFILE_DATA) Trace::groups('Data')->group('FROM BUFFER')->group($comment)->group()->set(F::toJSON($group_cond, false));
            }
        }
        // Если не выбрали из буфера, то выбор из хранилища. Получаем атрибуты объектов
        if (!isset($result) && !$only_cache){
            // Определение хранилища по URI
            if ($store = self::getStore($group_cond['from'])){
                $result = $store->read($group_cond, $index);
                // @todo Если выбран объект, но он не существует, хотя имеется  его uri и uri не соответсвует хранилищу, то выбрать объект повторно по uri
                if ($group_cond['select'][0] == 'self' && empty($result['is_exists']) && isset($result['uri']) && Data::isAbsoluteUri($result['uri'])){
                    $group_cond2 = $group_cond;
                    $group_cond2['from'] = $result['uri'];
                    if (($store2 = self::getStore($result['uri'])) && $store != $store2){
                        $result = $store2->read($group_cond2, $index);
                    }
                }
                if (PROFILE_DATA) Trace::groups('Data')->group('FROM STORE')->group($comment)->group()->set(F::toJSON($group_cond, false));
            }else{
                $result = null;
            }
            // В буфер атрибутов (1)
            if ($cache) Buffer::setPlain($result, $group_cond);
        }
        // Если не возвращать результат, то не тратим ресурсы на создание экземпляров
        if ($return === false) return null;
        // Если кэш не сущностей и была группировка, то создавать только запрашиваемые экземпляры
        if ($cache!=2 && isset($key_from_group) && isset($result[$key_from_group])){
            $result = $result[$key_from_group];
        }else{
            $cond = $group_cond;
        }
        // Создание экземпляров
        if (is_array($result) && empty($cond['select'][1])){
            if (!is_array($cond['from'])) $result = array($result);
            if ($what=='tree'){
                $children_depth = isset($return['depth'])&&($what=='tree')? $return['depth'] : Entity::MAX_DEPTH;
            }else{
                $children_depth = false;
            }
            foreach ($result as $gi => $gitem){
                if (isset($gitem['class_name'])){
                    $gitem['cond'] = $group_cond;
                    try{
                        $result[$gi] = new $gitem['class_name']($gitem, $children_depth);
                    }catch (\Exception $e){
                        $result[$gi] = new Entity($gitem, $children_depth);
                    }
                }else{
                    $list = array();
                    if ($children_depth===false || $children_depth > 0){
                        foreach ($result[$gi] as $item){
                            if (isset($item['class_name'])){
                                $item['cond'] = $cond;
                                try{
                                    $obj = new $item['class_name']($item, $children_depth);
                                }catch (\Exception $e){
                                    $obj = new Entity($item, $children_depth);
                                }
                                if (empty($cond['key'])){
                                    $list[] = $obj;
                                }else{
                                    $list[$item[$cond['key']]] = $obj;
                                }
                            }
                        }
                    }
                    $result[$gi] = $list;
                }
            }
            if (!is_array($cond['from'])) $result = reset($result);
        }
        // В буфер сущностей (2)
        if ($cache==2 && isset($result)){
            Buffer::setEntity($result, $cond);
        }
        // Если условие автоматом сгруппировано и выбор был по нему, то выбор из группы запрашиваемого элемента
        if (/*$cache==2 && */isset($key_from_group) && is_array($result) && isset($result[$key_from_group])){
            $result = $result[$key_from_group];
        }
        return $result;
    }

    /**
     * Сохранение объекта
     * @param Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на запись объекта?
     * @throws \Boolive\errors\Error
     * @return bool Признак, сохранен или нет объект?
     */
    static function write($entity, $access = true)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore($entity->key())){
                return $store->write($entity, $access);
            }else{
                $error = new Error('Невозможно сохранить объект', $entity->uri());
                $error->store = new Error('Неопределено хранилище для объекта', 'not-exist');
                throw $error;
            }
        }
        return false;
    }

    /**
     * Уничтожение объекта и его подчиенных
     * @param Entity $entity Уничтожаемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @throws \Boolive\errors\Error
     * @return bool Признак, был уничтожен объект или нет?
     */
    static function delete($entity, $access = true, $integrity = true)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore($entity->key())){
                return $store->delete($entity, $access, $integrity);
            }else{
                $error = new Error('Невозможно удалить объект', $entity->uri());
                $error->store = new Error('Неопределено хранилище объекта', 'not-exist');
                throw $error;
            }
        }
        return false;
    }

    /**
     * Поиск обновлений для объекта в прототипе или info файле.
     * Если для объекта есть обновления, то он помечается признаком dif = changed
     * Ищутся новые свойства (подчиненные объекты) от прототипа или info файлов. Найденные объекты сохраняются с признаком dif = new, если уже были проиндексированы после создания. Иначе без признака.
     * Проверяются существующие свойства объекта, если их прототип отсутсвует, но был свойством прототипа родителя, то такие свойства помечаются dif = delete
     * Для каждого свойства запускается findUpdate()
     *
     * @param Entity $entity Обновляеый объект
     * @param int $step_size Количество проверяемых подчиненных за раз
     * @param int $depth Глубина обновления
     * @param bool $from_file Признак, проверять или нет изменения в .info файлах
     * @throws \Boolive\errors\Error
     */
    static function findUpdates($entity, $step_size = 50, $depth = 1, $from_file = true)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore($entity->key())){
                $store->findUpdates($entity, $step_size, $depth, $from_file);
            }else{
                $error = new Error('Невозможно проверить обновления для объекта', $entity->uri());
                $error->store = new Error('Неопределено хранилище объекта', 'not-exist');
                throw $error;
            }
        }
    }

    /**
     * Применение ранее найденных обновлдений для объекта
     * @param Entity $entity Объект, для которого применяются обновления
     * @throws \Boolive\errors\Error
     */
    static function applyUpdates($entity)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore($entity->key())){
                $store->applyUpdates($entity);
            }else{
                $error = new Error('Невозможно применить обновления для объекта', $entity->uri());
                $error->store = new Error('Неопределено хранилище объекта', 'not-exist');
                throw $error;
            }
        }
    }

    /**
     * Нормализация условия поиска.
     * Определяются пункты по умолчанию, корректируется структура.
     * Условие может быть массивом из двух элементов - объекта и строкого условия, тогда объект
     * определяется в пункт from
     * @param string $cond Исходное условие в виде строки, url или массива
     * @param array $default Условие по умолчанию
     * @param bool $normalize Признак, нормализовать элменты услвоия (по умолчани, отсортировать, привести к общему формату)?
     * @param bool $entity_in_from Признак, допустимы сущности в from или преобразовать их uri/id?
     * @return array Преобразованное условие
     */
    static function normalizeCond($cond, $default = array(), $normalize = false, $entity_in_from = false)
    {
        if (is_array($cond) && !empty($cond['correct'])){
            return $cond;
        }
        $result = array();
        // Услвоие - строка (uri + cond)
        if (is_string($cond)){
            $uri = $cond;
        }else
        if (is_array($cond)){
            // массив из объекта и строки. строка может состоять из uri, cond,
            if (count($cond) == 2 && isset($cond[0]) && $cond[0] instanceof Entity && isset($cond[1]) && is_string($cond[1])){
                $uri = $cond[1];
                $entity = $cond[0];
            }else{
                // массив условия
                $result = $cond;
            }
        }
        if (isset($uri)){
            if (!preg_match('/^[^=]+\(/ui', $uri)){
                $result = self::urldecodeCond($uri);
            }else{
                $result = self::parseCond($uri);
            }
            if (isset($entity)) $result['from'] = array($entity, $result['from']);
        }
        if (!empty($default)) $result = array_replace_recursive($default, $result);
        if ($normalize){
            // select - Что выбирать
            if (empty($result['select'])){
                // по умолчанию self (выбирается from)
                $result['select'] = array('self');
                $result['depth'] = array(0,0);
            }else
            if (!is_array($result['select'])){
                $result['select'] = array($result['select']);
            }
            // Если подсчёт количества, то по умолчанию подчиненных
            if ($result['select'][0] == 'count' && empty($result['select'][1])){
                $result['select'][1] = 'children';
            }else
            // Если проверка существования, то по умолчанию self
            if ($result['select'][0] == 'exists' && empty($result['select'][1])){
                $result['select'][1] = 'self';
                $result['depth'] = array(0,0);
            }
            // depth - Глубина поиска/выбра
            if (!isset($result['depth'])){
                // По умолчанию в зависимости от select
                if ($result['select'][0] == 'self' || $result['select'][0] == 'link' || (($result['select'][0] == 'count' || $result['select'][0] == 'exists') && $result['select'][1] == 'self')){
                    $result['depth'] = array(0,0);
                }else
                if ($result['select'][0] == 'parents' || $result['select'][0] == 'protos' ||
                    (($result['select'][0] == 'count' || $result['select'][0] == 'exists') && ($result['select'][1] == 'parents' || $result['select'][1] == 'protos'))
                ){
                    // выбор всех родителей или прототипов
                    $result['depth'] = array(1, Entity::MAX_DEPTH);
                }else{
                    // выбор непосредственных подчиненных или наследников
                    $result['depth'] = array(1,1);
                }
            }else
            if (!is_array($result['depth'])){
                $result['depth'] = array($result['depth']?1:0, $result['depth']);
            }
            foreach ($result['depth'] as $i => $d){
                if ($d === 'max'){
                    $result['depth'][$i] = Entity::MAX_DEPTH;
                }else
                if ($d != Entity::MAX_DEPTH){
                    $result['depth'][$i] = (int)$d;
                }
            }
            // Нормализация from
            $result['from'] = self::normalizeFrom(isset($result['from'])?$result['from']:null, $entity_in_from);
            if ($result['select'][0] == 'self' && is_string($result['from']) && ($f = rtrim($result['from'],'/'))!=$result['from']){
                $result['from'] = $f;
                $result['select'][0] = 'children';
                $result['depth'] = array(1,1);
            }
            // limit
            if ($result['select'][0] == 'exists'){
                $result['limit'] = array(0,1);
            }else
            if (empty($result['limit'])){
                $result['limit'] = false;
            }
            // order
            if (isset($result['order'])){
                if (!empty($result['order']) && !is_array(reset($result['order']))){
                    $result['order'] = array($result['order']);
                }
            }
            if (empty($result['order'])){
                if ($result['select'][0] == 'children' || $result['select'][0] == 'tree'){
                    $result['order'] = array(array('order', 'asc'));
                }else{
                    $result['order'] = false;
                }
            }
            // Сортировка и ограничение количества бессмысленно при глубине 0
            if ($result['select'][0] == 'self'){
                $result['limit'] = false;
                $result['order'] = false;
            }
            if (!isset($result['key']) || !in_array($result['key'], array('uri', 'id', 'name', 'order', 'date', 'parent', 'proto', 'value', 'parent_cnt', 'proto_cnt'))){
                $result['key'] = false;
            }
            if (isset($result['access'])){
                $result['access'] = (bool)$result['access'];
            }else{
                $result['access'] = false;
            }
            if (empty($result['where'])) $result['where'] = false;
            // Если ограничение результата то не кэшировать сущности
            if (!empty($result['cache']) && $result['cache']==2 && !empty($result['return'])){
                $result['cache'] = 1;
            }
            $r = array(
                'from' => $result['from'],
                'select' => $result['select'],
                'depth' => $result['depth'],
                'key' => $result['key'],
                'where' => $result['where'],
                'order' => $result['order'],
                'limit' => $result['limit'],
                'access' => $result['access'],
                'file_content' => isset($result['file_content']) ? intval($result['file_content']) : 0,
                'class_content' => isset($result['class_content']) ? intval($result['class_content']) : 0,
                'correct' => true
            );
            if (isset($result['comment'])) $r['comment'] = $result['comment'];
            if (isset($result['group'])) $r['group'] = true;
            if (isset($result['return'])) $r['return'] = $result['return'];
            if (isset($result['cache'])) $r['cache'] = $result['cache'];
            return $r;
        }
        return $result;
    }

    /**
     * Нормализация элемента from в условии.
     * @param array $from Элмент "from" из условия в формате массива
     * @param bool $can_entity Признак, может ли быть сущностью? Если нет, то заменяется на идентификатор сущности
     * @return array|string
     */
    static function normalizeFrom($from, $can_entity = false)
    {
        if (!isset($from)){
            return '';
        }else
        if (!is_array($from) || (count($from)==2 && $from[0] instanceof Entity && is_string($from[1]))){
            $from = array($from);
        }
        foreach ($from as $i => $f){
            if (!is_scalar($f)){
                if ($f instanceof Entity){
                    if (!$can_entity) $from[$i] = $f->id();
                }else
                if (is_array($f)){
                    // Если from[0] сущность, а from[1] строка
                    if (count($f)==2 && $f[0] instanceof Entity && is_string($f[1])){
                        if (!$can_entity || mb_substr_count($f[1], '/')){
                            $from[$i] = $f[0]->uri().'/'.$f[1];
                        }
                    }else{
                        $from[$i] = '';
                    }
                }
            }
        }
        return (count($from)==1) ? $from[0] : $from;
    }

    /**
     * Преобразование условия из URL формата в массив
     * Пример:
     *  Условие: from=/main/&where=is(/Library/Comment)&limit=0,10
     *  Означает: выбрать 10 подчиненных у объекта /main, которые прототипированы от /Library/Comment (можно не писать "from=")
     * @param string $uri Условие поиска в URL формате
     * @return array
     */
    static function urldecodeCond($uri)
    {
        $uri = trim($uri);
        if (mb_substr($uri,0,4)!='from'){
            if (preg_match('/^[a-z]+=/ui', $uri)){
                $uri = 'from=&'.$uri;
            }else{
                $uri = 'from='.$uri;
            }
        }
        $uri = preg_replace('#/?\?{1}#u', '&', $uri, 1);
        parse_str($uri, $params);
        $uri_s= '';
        foreach ($params as $key => $item) $uri_s.=$key.'('.$item.')';
        $result = self::parseCond($uri_s);
        if ($result){
            foreach ($result as $key => $item){
                if (is_array($item)){
                    $k = array_shift($item);
                    unset($result[$key]);
                    if (count($item)==1) $item = $item[0];
                    if ($item === 'false' || $item === '0') $item = false;
                    $result[$k] = $item;
                }else{
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * Преобразование условия поиска из массива или строки в url формат
     * @param string|array $cond Исходное условие поиска
     * @return string Преобразованное в URL условие
     */
    static function urlencodeCond($cond)
    {
        $cond = self::normalizeCond($cond, array(), true);
        if (is_array($cond['from'])){
            $info = parse_url(reset($cond['from']));
            $base_url = '';
            if (isset($info['scheme'])) $base_url.= $info['scheme'].'://';
            if (isset($info['host'])) $base_url.= $info['host'];
            if ($base_url_length = mb_strlen($base_url)){
                foreach ($cond['from'] as $i => $from){
                    if (mb_substr($from,0,$base_url_length) == $base_url) $cond['from'][$i] = mb_substr($from, $base_url_length);
                }
            }
        }
        if (count($cond['select']) == 1) $cond['select'] = $cond['select'][0];
        if ($cond['select'] == 'self'){
            unset($cond['select'], $cond['depth']);
        }
        unset($cond['correct']);
        foreach ($cond as $key => $c){
            if (empty($c)) unset($cond[$key]);
        }
        $url = F::toJSON($cond, false);
        $url = mb_substr($url, 1, mb_strlen($url)-2, 'UTF-8');
        $url = strtr($url, array(
                         '[' => '(',
                         ']' => ')',
                         ',""]' => ',)',
                         '"="' => '"eq"',
                         '"!="' => '"neq"',
                         '">"' => '"gt"',
                         '">="' => '"gte"',
                         '"<"' => '"lt"',
                         '"<="' => '"lte"'
                    ));
        $url = preg_replace_callback('/"([^"]*)"/ui', function($m){
                        $replacements = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
                        $escapers = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
                        return urlencode(str_replace($escapers, $replacements, $m[1]));
                    }, $url);
        $url = preg_replace('/,([a-z_]+):/ui','&$1=',$url);
        $url = preg_replace('/\(([a-z_]+),/ui','$1(',$url);
        $url = preg_replace('/\),/ui',')$1',$url);
        $url = mb_substr($url, 5, mb_strlen($url)-5);
        if (isset($base_url)){
            $url = $base_url.'?from='.$url;
        }else{
            $info = explode('&', $url, 2);
            if (!empty($info)){
                $url = urldecode($info[0]).'?'.$info[1];
            }
        }
        return $url;
    }

    /**
     * Информация о URI
     * <pre>
     * array(
     *     'uri' => '',
     *     'store' => '',
     *     'dslash' => '//',
     *     'id' => 1,
     *     'path' => ''
     * );
     * </pre>
     * @param $uri
     * @return array|bool
     */
    static function parseUri($uri)
    {
        if (is_array($uri)) $uri = reset($uri);
        if (is_string($uri) && preg_match('/^([^\/]*)(\/\/)?([0-9]*)(.*)$/u', $uri, $match)){
            return array(
                'uri' => $match[0],
                'store' => $match[1],
                'dslash' => $match[2],
                'id' => empty($match[3])&&$match[3]!=='0'?null:intval($match[3]),
                'path' => $match[4]
            );
        }
        return false;
    }

    /**
     * Преобразование строкового условия в массив
     * Пример:
     *  Условие: select(children)from(/main)where(is(/Library/Comment))limit(0,10)
     *  Означает: выбрать 10 подчиненных у объекта /main, которые прототипированы от /Library/Comment (можно не писать "from=")
     * @param $cond
     * @return array
     */
    static function parseCond($cond)
    {
        // Добавление запятой после закрывающей скобки, если следом нет закрывающих скобок
        $cond = preg_replace('/(\)(\s*[^\s\),$]))/ui','),$2', $cond);
        // name(a) => (name,a)
        $cond = preg_replace('/\s*([a-z_]+)\(/ui','($1,', $cond);
        // Все значения в кавычки
        $cond = preg_replace_callback('/(,|\()([^,)(]+)/ui', function($m){
                    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
                    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
                    return $m[1].'"'.str_replace($escapers, $replacements, $m[2]).'"';
                }, $cond);
        $cond = strtr($cond, array(
                    '(' => '[',
                    ')' => ']',
                    ',)' => ',""]',
                    '"eq"' => '"="',
                    '"neq"' => '"!="',
                    '"gt"' => '">"',
                    '"gte"' => '">="',
                    '"lt"' => '"<"',
                    '"lte"' => '"<="',
                ));
        $cond = '['.$cond.']';
        $cond = json_decode($cond);
        return $cond;
    }

    /**
     * Сравнене условий поиска в формате массива
     * @param array $cond1 Первое условие
     * @param array $cond2 Второе условие
     * @param array $ignore Ключи условий, которые игнорировать при сравнении
     * @return bool Признак, равны или нет услвоия
     */
    static function compareCond($cond1, $cond2, $ignore = array())
    {
        foreach ($ignore as $key){
            unset($cond1[$key]);
            unset($cond2[$key]);
        }
        return json_encode($cond1) == json_encode($cond2);
    }

    /**
     * Проверка, является ли URI сокращенным
     * Если да, то возвращается информация о URI, иначе false
     * Сокращенные URI используются в хранилищах для более оптимального хранения и поиска объектов
     * @param $uri Проверяемый URI
     * @param bool $only_id Признак, считать uri коротким, если в нём нет пути, только числововй идентификатор
     * @return array|bool
     */
    static function isShortUri($uri, $only_id = false)
    {
        $info = self::parseUri($uri);
        $short = isset($info['id'])? $info : false;
        if ($short && $only_id && !empty($info['path'])) $short = false;
        return $short;
    }

    /**
     * Проверяет, является ли URI абсолютным. Абсолютный, если начинается со схемы
     * @param $uri
     * @return bool
     */
    static function isAbsoluteUri($uri)
    {
        return (bool)preg_match('|^[a-z]+:\/\/|ui', $uri);
    }

    /**
     * Преобразование абсолютного URI в локальный для доступа к загруженным внешним файлам
     * @param $absolute_uri
     * @param bool $add_remote
     * @return string
     */
    static function convertAbsoluteToLocal($absolute_uri, $add_remote = true)
    {
        if (preg_match('/^[a-z]+:\/\/([^\/]+)(.*)$/u', $absolute_uri, $match)){
            $match[1] = str_replace('.','_',$match[1]);
            $match[1] = str_replace('-','__',$match[1]);
            return ($add_remote?'Remote/':'').$match[1].$match[2];
        }
        return $absolute_uri;
    }

    /**
     * Определение URI относительного прототипа
     * @param string $obj_uri URI объекта, для которого определяется относительный прототип
     * @param string $proto_uri URI обычного прототипа, от которого создаётся объект
     * @param string $proto_proto_uri URI прототипа у прототипа.
     * @return string
     */
    static function getRelativeProto($obj_uri, $proto_uri, $proto_proto_uri)
    {
        $obj_uri = explode('/', $obj_uri);
        $proto_uri = explode('/', $proto_uri);
        $proto_proto_uri = explode('/', $proto_proto_uri);
        $i = 0;
        // Поиск элемента, с которого прототипы отличаются
        while (isset($proto_uri[$i]) && isset($proto_proto_uri[$i]) && $proto_uri[$i] == $proto_proto_uri[$i]) $i++;
        // Начальная часть пути берется от $obj
        $l = count($obj_uri)-count($proto_uri)+$i;
        if ($l>0){
            $new_proto = array_slice($obj_uri, 0, $l);
        }else{
            return false;
        }
        // Конечная часть пути добавляется от $proto_proto (его конец, отличающийся от $proto)
        if ($i <= count($proto_proto_uri)){
            $new_proto = array_merge($new_proto, array_slice($proto_proto_uri, $i));
        }
        return implode('/', $new_proto);
    }

    /**
     * Взвращает экземпляр хранилища
     * @param $uri Путь на объект, для которого определяется хранилище
     * @return \Boolive\data\stores\MySQLStore|null Экземпляр хранилища, если имеется или null, если нет
     */
    static function getStore($uri)
    {
        if (is_array($uri)) $uri = reset($uri);
        foreach (self::$config_stores as $key => $config){
            if ($key == '' || mb_strpos($uri, $key) === 0){
                if (!isset(self::$stores[$key])){
                    self::$stores[$key] = new $config['class']($key, $config['connect']);
                }
                return self::$stores[$key];
            }
        }
        return null;
    }

    /**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements()
    {
		$requirements = array();
		if (file_exists(DIR_SERVER.self::CONFIG_FILE) && !is_writable(DIR_SERVER.self::CONFIG_FILE)){
			$requirements[] = 'Удалите файл конфигурации базы данных: <code>'.DIR_SERVER.self::CONFIG_FILE.'</code>';
		}
		if (!file_exists(DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE)){
			$requirements[] = 'Отсутствует установочный файл <code>'.DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE.'</code>';
		}
		return $requirements;
	}

    /**
	 * Запрашиваемые данные для установки модуля
	 * @return array
	 */
	static function installPrepare()
    {
		$file = DIR_SERVER.self::CONFIG_FILE;
		if (file_exists($file)){
			include $file;
			if (isset($stores) && is_array($stores[''])){
				$config = $stores[''];
			}
		}
        if (empty($config)){
            $config = array(
                'connect' => array(
                    'dsn' => array(
                        'dbname'   => 'boolive',
                        'host'     => 'localhost',
                        'port'     => '3306',
                    ),
                    'user'     => 'root',
                    'password' => '',
                    'prefix'   => '',
                )
            );
        }
		return array(
			'title' => 'Настройка базы данных',
			'descript' => 'Параметры доступа к системе управления базами данных MySQL. База данных используется системой Boolive для хранения информации',
			'fields' => array(
				'dbname' => array(
					'label' => 'Имя базы данных',
					'descript' => 'Если указанной базы данных нет, то осуществится попытка её автоматического создания',
					'value' => $config['connect']['dsn']['dbname'],
					'input' => 'text',
					'required' => true,
				),
				'user' => array(
					'label' => 'Имя пользователя для доступа к базе данных',
					'descript' => 'Имя пользователя, имеющего право использовать указанную базу данных. Для автоматического создания базы данных пользователь должен иметь право создавать базы данных',
					'value' => $config['connect']['user'],
					'input' => 'text',
					'required' => true,
				),
				'password' => array(
					'label' => 'Пароль к базе данных',
					'descript' => 'Пароль вместе с именем пользователя необходим для получения доступа к указанной базе данных',
					'value' => $config['connect']['password'],
					'input' => 'text',
					'required' => false,
				),
				'host' => array(
					'label' => 'Сервер базы данных',
					'descript' => 'IP адрес или домен сервера, где установлена MySQL',
					'value' => $config['connect']['dsn']['host'],
					'input' => 'text',
					'required' => true,
				),
				'port' => array(
					'label' => 'Порт сервера базы данных',
					'descript' => 'Номер порта, по которому осуществляется доступ к серверу базы данных',
					'value' => $config['connect']['dsn']['port'],
					'input' => 'text',
					'required' => true,
				),
			)
		);
	}

    /**
     * Установка
     * @param \Boolive\input\Input $input Параметры доступа к БД
     * @throws \Boolive\errors\Error
     */
	static function install($input)
    {
		// Параметры доступа к БД
		$errors = new Error('Некоректные параметры доступа к СУБД', 'db');
		$new_config = $input->REQUEST->get(\Boolive\values\Rule::arrays(array(
			'dbname'	 => \Boolive\values\Rule::regexp('/^[0-9a-zA-Z_-]+$/u')->more(0)->max(50)->required(),
			'user' 		 => \Boolive\values\Rule::string()->more(0)->max(50)->required(),
			'password'	 => \Boolive\values\Rule::string()->max(50)->required(),
			'host' 		 => \Boolive\values\Rule::string()->more(0)->max(255)->default('localhost')->required(),
			'port' 		 => \Boolive\values\Rule::int()->min(1)->default(3306)->required(),
			//'prefix'	 => Rule::regexp('/^[0-9a-zA-Z_-]+$/u')->max(50)->default('')
		)), $sub_errors);
		$new_config['prefix'] = '';
		// Если ошибочные данные от юзера
		if ($sub_errors){
            $errors->add($sub_errors->children());
            throw $errors;
        }
		// Создание MySQL хранилища
        \Boolive\data\stores\MySQLStore::createStore($new_config, $errors);

        // Создание файла конфигурации из шаблона
        $content = file_get_contents(DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE);
        $content = F::Parse($content, $new_config, '{', '}');
        $fp = fopen(DIR_SERVER.self::CONFIG_FILE, 'w');
        fwrite($fp, $content);
        fclose($fp);
	}
}