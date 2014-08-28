<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\data;

use boolive\config\Config;
use boolive\functions\F,
    boolive\errors\Error,
    boolive\develop\Trace;

class Data2
{
    /** @var array Конфигурация хранилищ */
    public static $config;
    /** @var array Экземпляр хранилища */
    private static $store;

    static function activate()
    {
        // Конфиг хранилищ
        self::$config = Config::read('data2');
    }

    /**
     * Создание нового объекта
     * @param Entity|string $proto Прототипируемый объект, на основе которого создаётся новый
     * @param Entity|string $parent Родительский объект, в подчиненным (свойством) которого будет новый объект
     * @param string|null $name Имя нового объекта
     * @return Entity
     */
    static function create($proto, $parent, $name = null)
    {
        if (!$proto instanceof Entity) $proto = Data2::read($proto);
        $class = get_class($proto);
        $attr = array(
            'name' => $name ? $name : $proto->name(),
            'order' => Entity::MAX_ORDER,
            'is_hidden' => $proto->isHidden(),
            'is_draft' => $proto->isDraft(),
            'is_property' => $proto->isProperty()
        );
        /** @var $obj Entity */
        $obj = new $class($attr);
        $obj->name(null, true); // Уникальность имени
        if (isset($proto)){
            if (!$parent instanceof Entity) $parent = Data2::read($parent);
            $obj->parent($parent);
        }
        $obj->proto($proto);
        $obj->isDefaultValue(true);
        $obj->isDefaultClass(true);
        return $obj;
    }

    static function read($cond = '', $access = true)
    {
        //1. Нормализация условия
        $cond = self::normalizeCond($cond);
        //2. Если выбор одного объекта - поиск в буфере. Если найден, то возврат результата
        if ($cond['struct'] == 'object' && empty($cond['multiple']) && (
                $cond['select'] == 'self' || $cond['select'] == 'child'
            )){
            if ($result = Buffer2::get($cond['from'])) return $result;
        }
        //3. Поиск в кэше
        //4. Если нет в кэше, то запрос к хранилищу.
        if ($store = self::getStore()){
            $result = $store->read($cond);
        }else{
            return null;
        }
        //5. Если не из кэша, то запись результата в кэш
        //6. Создание экземпляров

        if (!empty($result) && !$cond['calc']){
            $key = empty($cond['key'])? false : $cond['key'];
            $entities = array();
            $tree_depth = $cond['struct'] == 'tree' ? $cond['depth'][1] : 0;
            foreach ($result as $rkey => $ritem){
                if ($key) $rkey = $ritem[$key];
                if (isset($ritem['class_name'])){
                    if ($tree_depth || !($entities[$rkey] = Buffer2::get($ritem['uri']))){
                        try{
                            $entities[$rkey] = new $ritem['class_name']($ritem, $tree_depth);
                        }catch (\ErrorException $e){
                            $entities[$rkey] = new Entity($ritem, $tree_depth);
                        }
                        $entities[$rkey]->isChanged(false);
                        if (!$tree_depth){
                            Buffer2::set($entities[$rkey]);
                        }
                    }
                }
            }
            $result = $entities;
        }
        if ($cond['struct'] == 'object' ||
            $cond['struct'] == 'value' ||
           ($cond['struct'] == 'tree' && ($cond['depth'][0] == 0 || $cond['select'] == 'parents' || $cond['select'] == 'protos')))
        {
            $result = reset($result);
        }
        return $result;
    }

    /**
     * Сохранение объекта
     * @param Entity $entity Сохраняемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на запись объекта?
     * @throws \boolive\errors\Error
     * @return bool Признак, сохранен или нет объект?
     */
    static function write($entity, $access = true)
    {
        if ($entity->id() != Entity::ENTITY_ID && $entity->check()){
            if ($store = self::getStore()){
                if ($result = $store->write($entity, $access)){
                    if (!Buffer2::isExists($entity)){
                        Buffer2::set($entity);
                    }
                }
                return $result;
            }else{
                $entity->errors()->store->{'not-exist'} = 'Неопределено хранилище';
            }
        }
        return false;
    }

    /**
     * Уничтожение объекта и его подчиенных
     * @param Entity $entity Уничтожаемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта?
     * @param bool $integrity Признак, проверять целостность данных?
     * @throws \boolive\errors\Error
     * @return bool Признак, был уничтожен объект или нет?
     */
    static function delete($entity, $access = true, $integrity = true)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore()){
                return $store->delete($entity, $access, $integrity);
            }else{
                $entity->errors()->store->{'not-exist'} = 'Неопределено хранилище объекта';
            }
        }
        return false;
    }

    /**
     * Дополнение объекта обязательными свойствами от прототипов
     * @param Entity $entity Дополняемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на запись объекта?
     * @return bool
     * @throws \boolive\errors\Error
     */
    static function complete($entity, $access = true)
    {
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore()){
                return $store->complete($entity, $access);
            }else{
                $entity->errors()->store->{'not-exist'} = 'Неопределено хранилище для объекта';
            }
        }
        return false;
    }

    /**
     * Нормализация услвоия выборки
     * @param string|array $cond
     * @param bool $full_normalize Признак, выполнять полную нормализацию или только конвертирование в массив?
     *                             Полная нормализация используется, если в условии указаны не все параметры
     * @param array $default Условие по умолчанию
     * @return array
     */
    static function normalizeCond($cond, $full_normalize = true, $default = array())
    {
        if (!empty($cond['correct'])) return $cond;
        $result = array();
        // Определение формата условия - массив, url, строка, массив из объекта и url
        if (is_array($cond)){
            if (sizeof($cond) == 2 && isset($cond[0]) && $cond[0] instanceof Entity && isset($cond[1])){
                // Пара из объекта и uri на подчиенного. uri может быть строковым условием
                $entity = $cond[0];
                $str_cond = $cond[1];
            }else{
                // обычный массив условия
                $result = $cond;
            }
        }else{
            $str_cond = $cond;
        }
        // Декодирование строкового услвоия в массив
        if (isset($str_cond)){
            if (!preg_match('/^[^=]+\(/ui', $str_cond)){
                $result = self::condStringToArray(self::condUrlToStrnig($str_cond), true);
            }else{
                $result = self::condStringToArray($str_cond, true);
            }
            if (isset($entity)) $result['from'] = array($entity, $result['from']);
        }
        if (!empty($default)) $result = array_replace_recursive($default, $result);

        if ($full_normalize){
            // select - что выбирать. объект, подчиненных, наследников, родителей, прототипы
            if (empty($result['select'])){
                // по умолчанию self (выбирается from)
                $result['select'] = 'self';
            }
            // calc - вычислять количество выбранных объектов или максимальные, минимальные, средние значения, или проверять существование.
            if (empty($result['calc'])){
                $result['calc'] = false;
            }

            // struct - структура результата. Экземпляр объекта, массив объектов, вычисляемое значение или дерево объектов
            if (empty($result['struct'])){
                if ($result['calc']){
                    $result['struct'] = 'value';
                }else
                if ($result['select'] == 'self' || $result['select'] == 'child'){
                    $result['struct'] = 'object';
                }else
                if (empty($result['struct'])){
                    $result['struct'] = 'array';
                }else{
                    $result['calc'] = false;
                }
            }

            // depth - глубина выборки. Два значения - начальная и конечная глубина относительно from.
            if (!isset($result['depth'])){
                // По умолчанию в зависимости от select
                if ($result['select'] == 'self' || $result['select'] == 'link'){
                    $result['depth'] = array(0,0);
                }else
                if ($result['select'] == 'parents' || $result['select'] == 'protos' || $result['struct'] == 'tree'){
                    // выбор всех родителей или прототипов
                    $result['depth'] = array(1, Entity::MAX_DEPTH);
                }else{
                    // выбор непосредственных подчиненных или наследников
                    $result['depth'] = array(1,1);
                }
            }else{
                if (!is_array($result['depth'])){
                    $result['depth'] = array(1, $result['depth']);
                }
                $result['depth'][1] = ($result['depth'][1] === 'max')? Entity::MAX_DEPTH : $result['depth'][1];
            }

            // from - от куда или какой объект выбирать. Строка, число, массив
            // Если URI, то дополнительно определяются секции, в которых выполнять поиск
            $result['sections'] = array();
            if (empty($result['from'])){
                $result['from'] = '';
                $result['sections'] = self::getSections($result['from'], $result['depth'][1]);
            }else
            if (is_array($result['from'])){
                if (count($result['from'])==2 && $result['from'][0] instanceof Entity && is_scalar($result['from'][1])){
                    $result['parent'] = (int)$result['from'][0]->id();
                    $result['name'] = $result['from'][1];
                    $result['from'] = $result['from'][0]->uri().'/'.$result['from'][1];
                }else{
                    foreach ($result['from'] as $fkey => $fval){
                        if (self::isUri($fval)){
                            $result['sections'] = array_merge($result['sections'], self::getSections($fval,$result['depth'][1]));
                        }else{
                            $result['from'][$fkey] = $fval==Entity::ENTITY_ID? Entity::ENTITY_ID : intval($fval);
                        }
                    }
                    $result['sections'] = F::array_unique($result['sections']);
                    $result['limit'] = array(0,count($result['from']));
                    $result['multiple'] = true;
                }
            }else
            if ($result['from'] instanceof Entity){
                $result['sections'] = self::getSections($result['from']->uri(), $result['depth'][1]);
                $result['from'] = $result['from']->key();
            }else
            if (self::isUri($result['from'])){
                $result['sections'] = self::getSections($result['from'], $result['depth'][1]);
            }else
            if ($result['from']!= Entity::ENTITY_ID){
                $result['from'] = intval($result['from']);
            }

            // where - условие выборки
            if (empty($result['where'])) $result['where'] = false;

            // order - сортировка. Можно указывать атрибуты и названия подчиненных объектов (свойств)
            if (isset($result['order'])){
                if (!empty($result['order']) && !is_array(reset($result['order']))){
                    $result['order'] = array($result['order']);
                }
            }
            if (empty($result['order'])){
                if ($result['select'] == 'children' || $result['struct'] == 'tree'){
                    $result['order'] = array(array('order', 'asc'));
                }else{
                    $result['order'] = false;
                }
            }
            if ($result['calc'] == 'exists'){
                $result['limit'] = false;
                $result['order'] = false;
            }

            // limit - ограничения выборки, начальный объект и количество
            if ($result['calc'] == 'exists'){
                $result['limit'] = array(0,1);
            }else
            if (empty($result['limit'])){
                $result['limit'] = false;
            }

            // key - если результат список, то определяет какой атрибут использовать в качестве ключа
            if (!isset($result['key'])){
                $result['key'] = false;
            }

            // cache - код режима кэширования
            if (empty($result['cache'])){
                $result['cache'] = false;
            }

            // access - проверять или нет доступ. Если проверять, то к условию добавятся условия доступа на чтение
            if (isset($result['access'])){
                $result['access'] = (bool)$result['access'];
            }else{
                $result['access'] = false;
            }

            // group - признак, группировать ли последующие запросы. Работает только для select = child
            if (empty($result['group'])){
                $result['group'] = false;
            }

            // Ограничение на возвращаемый результат (когда нет необходимости создавать экземпляры выбранных объектов, когда объекты в виде массива помещаются в кэш на будущие выборки)
            if (!isset($result['return'])) $result['return'] = true;

            // Упорядочивание параметров (для создания корректных хэш-ключей для кэша)
            $r = array(
                'select' => $result['select'],//check
                'calc' => $result['calc'],//check
                'from' => $result['from'],//check
                'sections' => $result['sections'],//check
                'multiple' => !empty($result['multiple']),
                'depth' => $result['depth'],//check
                'struct' => $result['struct'],//check
                'where' => $result['where'],
                'order' => $result['order'],//check
                'limit' => $result['limit'],//check
                'key' => $result['key'],//check
                'access' => $result['access'],
                'correct' => true,//check
                'cache' => $result['cache'],
                'comment' => empty($result['comment'])? false : $result['comment'],//check
                'group' => $result['group']
            );
            if (isset($result['parent']) && isset($result['name'])){
                $r['parent'] = $result['parent'];
                $r['name'] = $result['name'];
            }
            return $r;
        }
        return $result;
    }

    /**
     * Преобразование условия из URL формата в обычный сроковый
     * Пример:
     *  Условие: from=/main/&where=is(/library/Comment)&limit=0,10
     *  Означает: выбрать 10 подчиненных у объекта /main, которые прототипированы от /library/Comment (можно не писать "from=")
     * @param string $uri Условие поиска в URL формате
     * @return array
     */
    static function condUrlToStrnig($uri)
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
        $result = '';
        foreach ($params as $key => $item) $result.=$key.'('.$item.')';
        return $result;
    }

    /**
     * Преобразование условия поиска из массива или строки в url формат
     * @param string|array $cond Исходное условие поиска
     * @return string Преобразованное в URL условие
     */
    static function condToUrl($cond)
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
        //if (sizeof($cond['select']) == 1) $cond['select'] = $cond['select'][0];
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
     * Преобразование строкового условия в массив
     * Пример:
     *  Условие: select(children)from(/main)where(is(/library/Comment))limit(0,10)
     *  Означает: выбрать 10 подчиненных у объекта /main, которые прототипированы от /library/Comment (можно не писать "from=")
     * @param $cond
     * @param bool $accos Признак, конвертировать ли первый уровень массива в ассоциативный?
     *                    Используется для общего условия, когда задаются from, where и другие параметры.
     *                    Не используется для отдельной конвертации условий в where
     * @return array
     */
    static function condStringToArray($cond, $accos = false)
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
                    '",eq"' => '",="',
                    '",neq"' => '",!="',
                    '",gt"' => '",>"',
                    '",gte"' => '",>="',
                    '",lt"' => '",<"',
                    '",lte"' => '",<="',
                ));
        $cond = '['.$cond.']';
        $cond = json_decode($cond);
        if ($accos && $cond){
            foreach ($cond as $key => $item){
                if (is_array($item)){
                    $k = array_shift($item);
                    unset($cond[$key]);
                    if (sizeof($item)==1) $item = $item[0];
                    if ($item === 'false' || $item === '0') $item = false;
                    $cond[$k] = $item;
                }else{
                    unset($cond[$key]);
                }
            }
        }
        return $cond;
    }

    /**
     * Возвращает код секции по uri. По умолчанию 0
     * Секция определяется по настройкам подключения
     * @param string $uri URI, для которого определяется секция
     * @throws \Exception
     * @return int Код секции
     */
    static function getSection($uri){
        if ($store = self::getStore()){
            return $store->getSection($uri);
        }else{
            throw new \Exception('Не определено хранилище объектов');
        }
    }

    static function getSections($uri, $depth){
        if ($store = self::getStore()){
            return $store->getSections($uri, $depth);
        }else{
            throw new \Exception('Не определено хранилище объектов');
        }
    }

    /**
     * Взвращает экземпляр хранилища
     * @return \boolive\data\stores\MySQLStore2|null Экземпляр хранилища, если имеется или null, если нет
     */
    static function getStore()
    {
        if (!isset(self::$store)){
            self::$store = new self::$config['class'](self::$config['connect']);
        }
        return self::$store;
    }

    /**
     * Проверка, является ли значение URI объекта
     * @param $uri
     * @return bool
     */
    static function isUri($uri)
    {
        return !(is_int($uri) || preg_match('/^[0-9]+$/', $uri));
    }

    /**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements()
    {
		$requirements = array();
		if (!Config::is_writable('data2')){
			$requirements[] = 'Установите права на запись файла: <code>'.Config::file_name('data2').'</code>';
		}
//		if (!file_exists(DIR.'boolive/data/tpl.'.self::CONFIG_FILE)){
//			$requirements[] = 'Отсутствует установочный файл <code>'.DIR.'boolive/data/tpl.'.self::CONFIG_FILE.'</code>';
//		}
		return $requirements;
	}

    /**
	 * Запрашиваемые данные для установки модуля
	 * @return array
	 */
	static function installPrepare()
    {
		$config = Config::read('data2');
        if (empty($config)){
            $config = array(
                'connect' => array(
                    'dbname'   => 'boolive',
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => '',
                    'prefix'   => '',
                    'sections' => array(array('code' => 0, 'uri' => ''))
                )
            );
        }
		return array(
			'title' => 'Настройка базы данных 2',
			'descript' => 'Параметры доступа к системе управления базами данных MySQL. База данных используется системой Boolive для хранения информации',
			'fields' => array(
				'dbname' => array(
					'label' => 'Имя базы данных',
					'descript' => 'Если указанной базы данных нет, то осуществится попытка её автоматического создания',
					'value' => $config['connect']['dbname'],
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
					'value' => $config['connect']['host'],
					'input' => 'text',
					'required' => true,
				),
				'port' => array(
					'label' => 'Порт сервера базы данных',
					'descript' => 'Номер порта, по которому осуществляется доступ к серверу базы данных',
					'value' => $config['connect']['port'],
					'input' => 'text',
					'required' => true,
				),
			)
		);
	}

    /**
     * Установка
     * @param \boolive\input\Input $input Параметры доступа к БД
     * @throws \boolive\errors\Error
     */
	static function install($input)
    {
		// Параметры доступа к БД
		$errors = new Error('Некоректные параметры доступа к СУБД', 'db');
		$new_config = $input->REQUEST->get(\boolive\values\Rule::arrays(array(
            'dbname'	 => \boolive\values\Rule::regexp('/^[0-9a-zA-Z_-]+$/u')->more(0)->max(50)->required(),
            'host' 		 => \boolive\values\Rule::string()->more(0)->max(255)->default('localhost')->required(),
            'port' 		 => \boolive\values\Rule::int()->min(1)->default(3306)->required(),
			'user' 		 => \boolive\values\Rule::string()->more(0)->max(50)->required(),
			'password'	 => \boolive\values\Rule::string()->max(50)->required()
			//'prefix'	 => Rule::regexp('/^[0-9a-zA-Z_-]+$/u')->max(50)->default('')
		)), $sub_errors);
		$new_config['prefix'] = '';
        $new_config['options'] = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_bin"'
        );
        $new_config['prefix'] = '';
        $new_config['trace_sql'] = false;
        $new_config['trace_count'] = false;
        $new_config['sections'] = array(
            array('code' => 0, 'uri' => '')
        );
        $new_config = array(
            'class' => '\boolive\data\stores\MySQLStore2',
            'connect' => $new_config
        );
		// Если ошибочные данные от юзера
		if ($sub_errors){
            $errors->add($sub_errors->children());
            throw $errors;
        }
        if ($cur_config = $config = Config::read('data2')){
            $new_config = array_replace_recursive($cur_config['connect'], $new_config);
        }
		// Создание MySQL хранилища
        \boolive\data\stores\MySQLStore2::createStore($new_config['connect'], $errors);

        Config::write('data2', $new_config);
	}
}