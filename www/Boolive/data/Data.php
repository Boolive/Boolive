<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

use Boolive\Boolive,
    Boolive\functions\F,
    Boolive\auth\Auth,
    Boolive\errors\Error,
    Boolive\develop\Trace;

class Data
{
    /** @const  Файл конфигурации хранилищ */
    const CONFIG_FILE_STORES = 'config.stores.php';
    /** @var array Конфигурация хранилищ */
    private static $config_stores;
    /** @var array Экземпляры хранилищ */
    private static $stores;

    static function activate(){
        // Конфиг хранилищ
        self::$config_stores = F::loadConfig(DIR_SERVER.self::CONFIG_FILE_STORES, 'stores');
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
     *     'owner' => '//user',                         // Владелец искомых объектов
     *     'lang' => '//lang',                          // Язык (локаль) искомых объектов
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
     * @param bool $use_cache Признак, использовать или нет кэш?
     * @param bool $index Признак, выполнять индексирование данных перед поиском?
     * @return array|Entity|mixed|null В зависимости от услвоия выбора результатом будет
     * а) массив найденных объектов
     * б) объект,
     * в) вычисляемое значение, например, количество или null
     * г) массив со значенями указанного атрибута найденных объектов
     * д) значение указанного атриубта объекта
     */
    static function read($cond = '', $access = true, $use_cache = true, $index = true, $only_cache = false)
    {
        if ($cond == Entity::ENTITY_ID){
            return new Entity(array('uri'=>'/'.Entity::ENTITY_ID, 'id'=>Entity::ENTITY_ID));
        }
        if ($cond == 'null' || !isset($cond)){
            return null;
        }
        $cond = self::decodeCond($cond);
        // Контроль доступа в условие выбора
        if ($access) $cond['access'] = true;
        if (isset($cond['comment'])){
            $comment = ''.$cond['comment'];
            unset($cond['comment']);
        }else{
            $comment = 'no comment';
        }
        // Из буфера
        if ($use_cache){
            $result = Buffer::get($cond);
            if (isset($result)){
                if (PROFILE_DATA) Trace::groups('Data')->group('FROM BUFFER')->group($comment)->group()->set(F::toJSON($cond, false));
                return $result;
            }
        }
        if (!$only_cache){
            // Попытка сгруппировать выборку с возможными будущими подобными выборками

//            if (is_string($cond['from'])){
//                $from_info = Data::parseUri($cond['from']);
//                if (empty($from_info['id'])){
//                    $parent = F::splitRight('/', $cond['from']);
//                    $parent = $parent[0];
//                }else
//                if (empty($from_info['path'])){
//                    if ($obj = Data::read($from_info['path'], false, true, false, true)){
//                        $parent = $obj->parent()
//                    }
//                }
//                if (!empty($from_info['path']) && mb_substr_count($from_info['path'], '/')==1){
//                    $parent = $from_info['store'].$from_info['dslash'].$from_info['id'];
//                }
//                if (isset($parent) && $parent = Data::read($parent, false, true, false, true)){
//                    $a = 10;
//                    Trace::groups('Data')->group('group_count')->group()->set(F::toJSON($cond, false));
//                }
//            }


            // Определение хранилища по URI
            if ($store = self::getStore($cond['from'])){
                // Выбор объекта
                $result = $store->read($cond, $index);
            }else{
                $result = null;
            }
            // В буфер
            if ($use_cache) Buffer::set($result, $cond);
            if (PROFILE_DATA) Trace::groups('Data')->group('FROM STORE')->group($comment)->group()->set(F::toJSON($cond, false));
            return $result;
        }
        return null;
    }

    /**
     * Сохранение объекта
     * @param Entity $object Сохраняемый объект
     * @param \Boolive\errors\Error $error Контейнер для ошибок при сохранении
     * @param bool $access Признак, проверять или нет наличие доступа на запись объекта?
     * @return bool Признак, сохранен или нет объект?
     */
    static function write($object, &$error, $access = true)
    {
        if ($object->id() != Entity::ENTITY_ID){
            if (!$access || !IS_INSTALL || ($object->isAccessible() && Auth::getUser()->checkAccess('write', $object))){
                if ($store = self::getStore($object->key())){
                    return $store->write($object);
                }else{
                    $error->section = new Error('Не определена секция объекта', 'not-exist');
                }
            }else{
                $error->access = new Error('Нет доступа на запись', 'write');
            }
        }
        return false;
    }

    /**
     * Уничтожение объекта и его подчиенных
     * @param Entity $object Уничтожаемый объект
     * @param \Boolive\errors\Error $error Контейнер для ошибок при уничтожении
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @return bool Признак, был уничтожен объект или нет?
     */
    static function delete($object, &$error, $access = true, $integrity = true)
    {
        if ($object->id() != Entity::ENTITY_ID){
            $store = self::getStore($object->key());
            // Проверка доступа на уничтожение объекта и его подчиненных
            if (!self::deleteConflicts($object, $access, $integrity)){
                return $store->delete($object);
            }else{
                $error->destroy = new Error('Имеются конфлиткты при уничтожении объектов', 'destroy');
            }
        }
        return false;
    }

    /**
     * Поиск объектов, из-за которых невозможно уничтожение объекта
     * @param Entity $object Проверяемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @return array Массив URI объектов, из-за которых невозможно уничтожение объекта
     */
    static function deleteConflicts($object, $access = true, $integrity = true)
    {
        $conflicts = array();
        if ($object->id() != Entity::ENTITY_ID){
            $store = self::getStore($object->key());
            // Проверка доступа на уничтожение объекта и его подчиненных
            if ($access && IS_INSTALL && ($acond = Auth::getUser()->getAccessCond('destroy', $object->id(), null))){
                $objects = $store->read(array(
                        'select' => array('children', 'uri'),
                        'from' => $object->id(),
                        'depth' => 'max',
                        'where' => array('not', $acond),
                        'limit' => array(0,50),
                        'key' => 'name',
                        'access' => false
                    ), false
                );
                $conflicts['access'] = $objects;
            }
            // Проверка использования в качестве прототипа
            if ($integrity && ($objects = $store->deleteConflicts($object))){
                $conflicts['heirs'] = $objects;
            }
        }
        return $conflicts;
    }

    /**
     * Преобразование строкового условия поиска в структуру из массивов
     * Если условие является массивом, то оно нормализуется - определяются пункты по умолчанию, корректируется структура.
     * Условие может быть массивом из двух элементов - объекта и строкого условия, тогда объект
     * определяется в пункт from
     * @param string $cond Исходное условие
     * @param array $default Условие по умолчанию
     * @return array Преобразованное условие
     */
    static function decodeCond($cond, $default = array())
    {
        if (is_array($cond) && !empty($cond['correct'])){
            return $cond;
        }
        $result = array();
        // Услвоие - строка (uri + cond + lang + owner)
        if (is_string($cond)){
            $uri = $cond;
        }else
        if (is_array($cond)){
            // массив из объекта и строки. строка может состоять из uri, cond, lang, owner
            if (sizeof($cond) == 2 && isset($cond[0]) && $cond[0] instanceof Entity && isset($cond[1]) && is_string($cond[1])){
                $uri = $cond[1];
                $entity = $cond[0];
            }else{
                // массив условия
                $result = $cond;
            }
        }
        if (isset($uri)){
            $parse = function($cond){
                // Добавление запятой после закрывающей скобки, если следом нет закрывающих скобок
                $cond = preg_replace('/(\)(\s*[^\s\),$]))/ui','),$2', $cond);
                // name(a) => (name,a)
                $cond = preg_replace('/\s*([a-z]+)\(/ui','($1,', $cond);
                // Все значения вкавычки
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
                if ($cond){
                    foreach ($cond as $key => $item){
                        if (is_array($item)){
                            $k = array_shift($item);
                            unset($cond[$key]);
                            if (sizeof($item)==1) $item = $item[0];
                            $cond[$k] = $item;
                        }else{
                            unset($cond[$key]);
                        }
                    }
                }
                return $cond;
            };
            $uri = trim($uri);
            if (!preg_match('/^[^=]+\(/ui', $uri)){
                if (mb_substr($uri,0,4)!='from'){
                    if (preg_match('/^[a-z]+=/ui', $uri)){
                        $uri = 'from=&'.$uri;
                    }else{
                        $uri = 'from='.$uri;
                    }
                }
                $uri = preg_replace('#/?\?{1}#u', '&', $uri, 1);
                parse_str($uri, $result);
                if (isset($result['cond'])){
                    $primary_cond = $parse($result['cond']);
                    unset($result['cond']);
                }else{
                    $primary_cond = array();
                }
                $secondary_cond = '';
                foreach ($result as $key => $item) $secondary_cond.=$key.'('.$item.')';
                if (!empty($secondary_cond)){
                    if ($secondary_cond = $parse($secondary_cond)){
                        $result = array_replace($secondary_cond, $primary_cond);
                    }else{
                        $result = $primary_cond;
                    }
                }else{
                    $result = $primary_cond;
                }
            }else{
                $result = $parse($uri);
            }
            if (isset($entity)) $result['from'] = array($entity, $result['from']);
        }
        if (!empty($default)) $result = array_replace_recursive($default, $result);
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
            if ($result['select'][0] == 'self' || (($result['select'][0] == 'count' || $result['select'][0] == 'exists') && $result['select'][1] == 'self')){
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
        // Общий владелец и язык, если не указаны конкретные
        if (!isset($result['owner'])) $result['owner'] = Entity::ENTITY_ID;
        if (!isset($result['lang'])) $result['lang'] = Entity::ENTITY_ID;
        // Нормализация from
        if (!isset($result['from'])){
            $result['from'] = array(null);
        }else
        if (!is_array($result['from']) || (sizeof($result['from'])==2 && $result['from'][0] instanceof Entity && is_string($result['from'][1]))){
            $result['from'] = array($result['from']);
        }
        foreach ($result['from'] as $i => $f){
            if ($f instanceof Entity){
                $result['from'][$i] = $f->id();
            }else
            if (is_array($f)){
                // Если from[0] сущность, а from[1] строка
                if (sizeof($f)==2 && $f[0] instanceof Entity && is_string($f[1])){
                    $result['from'][$i] = $f[0]->uri().'/'.$f[1];
                }else{
                    $result['from'][$i] = '';
                }
            }
        }
        if (sizeof($result['from'])==1) $result['from'] = $result['from'][0];
        // limit
        if ($result['select'][0] == 'exists'){
            $result['limit'] = array(0,1);
        }else
        if (empty($result['limit'])){
            $result['limit'] = false;
        }
        // order
        if (isset($result['order'])){
            if (!is_array(reset($result['order']))){
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
        if (!isset($result['key']) || !in_array($result['key'], array('uri', 'id', 'name', 'owner', 'lang', 'order', 'date', 'parent', 'proto', 'value', 'parent_cnt', 'proto_cnt'))){
            $result['key'] = false;
        }
        if (isset($result['access'])){
            $result['access'] = (bool)$result['access'];
        }else{
            $result['access'] = false;
        }
        if (empty($result['where'])) $result['where'] = false;
        $r = array(
            'from' => $result['from'],
            'select' => $result['select'],
            'depth' => $result['depth'],
            'key' => $result['key'],
            'where' => $result['where'],
            'owner' => $result['owner'],
            'lang' => $result['lang'],
            'order' => $result['order'],
            'limit' => $result['limit'],
            'access' => $result['access'],
            'correct' => true
        );
        if (isset($result['comment'])) $r['comment'] = $result['comment'];
        return $r;
    }

    /**
     * Преобразование услвоия поиска из любого формата в url формат
     * @param string|array $cond Исходное условие поиска
     * @return string Преобразованное в URL условие
     */
    static function urlencodeCond($cond)
    {
        $cond = Data::decodeCond($cond);
        if (sizeof($cond['select']) == 1) $cond['select'] = $cond['select'][0];
        if ($cond['select'] == 'self'){
            unset($cond['select'], $cond['depth']);
        }
        $cond = F::toJSON($cond, false);
        $cond = mb_substr($cond, 1, mb_strlen($cond)-1, 'UTF-8');
        $cond = strtr($cond, array(
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
        $cond = preg_replace_callback('/"([^"]*)"/ui', function($m){
                        //trace($m);
                        $replacements = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
                        $escapers = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
                        return urlencode(str_replace($escapers, $replacements, $m[1]));
                    }, $cond);
        $cond = preg_replace('/,([a-z]+):/ui','&$1=',$cond);
        $cond = preg_replace('/\(([a-z]+),/ui','$1(',$cond);
        $cond = preg_replace('/\),/ui',')$1',$cond);
        $cond = mb_substr($cond, 5, mb_strlen($cond)-6);
        $info = explode('&', $cond, 2);
        if (!empty($info)){
            $cond = urldecode($info[0]).'?'.$info[1];
        }
        return $cond;
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
                'id' => intval($match[3]),
                'path' => $match[4]
            );
        }
        return false;
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
        $short = !empty($info['id'])? $info : false;
        if ($short && $only_id && !empty($info['path'])) $short = false;
        return $short;
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
	static function systemRequirements(){
		$requirements = array();
		if (file_exists(DIR_SERVER.self::CONFIG_FILE_STORES) && !is_writable(DIR_SERVER.self::CONFIG_FILE_STORES)){
			$requirements[] = 'Удалите файл конфигурации базы данных: <code>'.DIR_SERVER.self::CONFIG_FILE_STORES.'</code>';
		}
		if (!file_exists(DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE_STORES)){
			$requirements[] = 'Отсутствует установочный файл <code>'.DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE_STORES.'</code>';
		}
		return $requirements;
	}

    /**
	 * Запрашиваемые данные для установки модуля
	 * @return array
	 */
	static function installPrepare(){
		$file = DIR_SERVER.self::CONFIG_FILE_STORES;
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
	static function install($input){
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
            $errors->add($sub_errors->getAll());
            throw $errors;
        }
		// Создание MySQL хранилища
        \Boolive\data\stores\MySQLStore::createStore($new_config, $errors);

        // Создание файла конфигурации из шаблона
        $content = file_get_contents(DIR_SERVER_ENGINE.'data/tpl.'.self::CONFIG_FILE_STORES);
        $content = F::Parse($content, $new_config, '{', '}');
        $fp = fopen(DIR_SERVER.self::CONFIG_FILE_STORES, 'w');
        fwrite($fp, $content);
        fclose($fp);
	}
}