<?php
/**
 * Модуль данных
 *
 * @link http://boolive.ru/createcms/data-and-entity
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\data;

use boolive\functions\F,
    boolive\errors\Error,
    boolive\develop\Trace;

class Data2
{
    /** @const  Файл конфигурации хранилищ */
    const CONFIG_FILE = 'config.data2.php';
    /** @var array Конфигурация хранилищ */
    public static $config;
    /** @var array Экземпляр хранилища */
    private static $store;

    static function activate()
    {
        // Конфиг хранилищ
        self::$config = F::loadConfig(DIR_SERVER.self::CONFIG_FILE, 'store');
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

        if (!$proto instanceof Entity) $proto = Data::read($proto);
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
            if (!$parent instanceof Entity) $parent = Data::read($parent);
            $obj->parent($parent);
        }
        $obj->proto($proto);
        $obj->isDefaultValue(true);
        $obj->isDefaultClass(true);
        return $obj;
    }

    static function read($cond = '', $access = true)
    {
        $cond = self::normalizeCond($cond);

        if ($store = self::getStore()){
            return $store->read($cond);
        }
        //
        //1. Нормализация условия
        //2. Если выбор одного объекта - поиск в буфере. Если найден, то возврат результата
        //3. Поиск в кэше
        //4. Если нет в кэше, то запрос к хранилищу.
        //5. Если не из кэша, то запись результата в кэш
        //6. Создание экземпляров
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
        if ($entity->id() != Entity::ENTITY_ID){
            if ($store = self::getStore()){
                return $store->write($entity, $access);
            }else{
                $entity->errors()->store->{'not-exist'} = 'Неопределено хранилище';
            }
        }
        return false;
    }

    static function normalizeCond($cond)
    {
        if (!empty($cond['correct'])) return $cond;

        return $cond;
    }

    /**
     * Преобразование условия из URL формата в массив
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
        if (sizeof($cond['select']) == 1) $cond['select'] = $cond['select'][0];
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
     * @return array
     */
    static function condStringToArray($cond)
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
        return $cond;
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
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements()
    {
		$requirements = array();
		if (file_exists(DIR_SERVER.self::CONFIG_FILE) && !is_writable(DIR_SERVER.self::CONFIG_FILE)){
			$requirements[] = 'Установите права на запись для файла: <code>'.DIR_SERVER.self::CONFIG_FILE.'</code>';
		}
		if (!file_exists(DIR_SERVER.'boolive/data/tpl.'.self::CONFIG_FILE)){
			$requirements[] = 'Отсутствует установочный файл <code>'.DIR_SERVER.'boolive/data/tpl.'.self::CONFIG_FILE.'</code>';
		}
		return $requirements;
	}

    /**
	 * Запрашиваемые данные для установки модуля
	 * @return array
	 */
	static function installPrepare()
    {
		$config = F::loadConfig(DIR_SERVER.self::CONFIG_FILE, 'store');
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
		// Если ошибочные данные от юзера
		if ($sub_errors){
            $errors->add($sub_errors->children());
            throw $errors;
        }
        if ($cur_config = $config = F::loadConfig(DIR_SERVER.self::CONFIG_FILE, 'store')){
            $new_config = array_replace_recursive($cur_config['connect'], $new_config);
        }
		// Создание MySQL хранилища
        \boolive\data\stores\MySQLStore2::createStore($new_config, $errors);

        foreach ($new_config['sections'] as $i => $sec){
            $new_config['sections'][$i] = "array('code' => {$sec['code']}, 'uri' => '{$sec['uri']}')";
        }
        $new_config['sections'] = implode(",\n            ", $new_config['sections']);

        // Создание файла конфигурации из шаблона
        $content = file_get_contents(DIR_SERVER.'boolive/data/tpl.'.self::CONFIG_FILE);
        $content = F::Parse($content, $new_config, '_', '_', null);
        $fp = fopen(DIR_SERVER.self::CONFIG_FILE, 'w');
        fwrite($fp, $content);
        fclose($fp);
	}
}