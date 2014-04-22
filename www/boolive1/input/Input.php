<?php
/**
 * Входящие данные
 * Объединяет в одном контейнере суперглобальные массивы GET, POST, COOKIE, FILES, HTTP_RAW_POST_DATA, SERVER
 * Дополнительно обрабатывает их для нормализазии.
 * Из GET создаются path и args.
 * * path - элементы пути URI после адреса хоста: /param0/param1/param2
 * * args - именованные значения из части запроса URI - то, что после "?":  arg1=value&arg2=value2
 * Если система запущена из командной строки, то переданные аргументы окажутся в args
 * Для получения всех данных используется Input::all()
 * При выборки из контейнера значения фильтруются в соответствии с указанным правилом или правилом по умочланию
 * @link http://boolive.ru/createcms/processing-request
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\input;

use Boolive\values\Values,
    Boolive\functions\F;

/**
 * @property \Boolive\values\Values path Элементы пути URI
 * @property \Boolive\values\Values REQUEST = GET+POST+argv данные или аргументы командной строки
 * @property \Boolive\values\Values FILES Загруженные файлы
 * @property \Boolive\values\Values COOKIE Куки
 * @property \Boolive\values\Values RAW Неформатированные данные
 * @property \Boolive\values\Values SERVER Информация о сервере и среде исполнения
 */
class Input extends Values
{
    /** @var \Boolive\input\Input Общий контейнер всех входящих данных */
    private static $input;

    /**
     * Активация модуля
     * Создание общего контейнера входящих данных
     */
    static function activate()
    {
        if (get_magic_quotes_gpc()){
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)){
                foreach ($val as $k => $v){
                    unset($process[$key][$k]);
                    if (is_array($v)){
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = &$process[$key][stripslashes($k)];
                    }else{
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }
        $values = array(
            'REQUEST' => array(),
            'FILES' => isset($_FILES)? self::normalizeFiles() : array(),
            'COOKIE' => isset($_COOKIE)? $_COOKIE : array(),
            'RAW' => file_get_contents("php://input"), // Неформатированные данные
            'SERVER' => $_SERVER
        );
        if (isset($_SERVER['REQUEST_URI'])){
            $_SERVER['REQUEST_URI'] = preg_replace('#\?{1}#u', '&', $_SERVER['REQUEST_URI'], 1);
            $request_uri = preg_replace('#^'.preg_quote(DIR_WEB).'#u', '/', $_SERVER['REQUEST_URI'], 1);
            parse_str('path='.$request_uri, $values['REQUEST']);
            $values['SERVER']['argv'] = $values['REQUEST'];
            $values['SERVER']['argc'] = sizeof($values['REQUEST']);
        }
        // Элементы пути URI
        if (isset($values['REQUEST']['path']) && ($values['REQUEST']['path'] = rtrim($values['REQUEST']['path'],'/ '))){
            $values['PATH'] = explode('/', trim($values['REQUEST']['path'],' /'));
        }else{
            $values['PATH'] = array();
        }
        if (isset($_POST)){
            $values['REQUEST'] = array_replace_recursive($values['REQUEST'], $_POST);
        }
        // Аргументы из консоли (режим CLI)
        if (isset($_SERVER['argv'])){
            $values['REQUEST']['method'] = 'CLI';
            $values['SERVER']['argv'] = $_SERVER['argv'];
            $values['SERVER']['argc'] = $_SERVER['argc'];
        }
        $values['ARG'] = array_flip($values['SERVER']['argv']);
        // Метод запроса
        if (isset($values['SERVER']['REQUEST_METHOD']) && !isset($values['REQUEST']['method'])){
            $values['REQUEST']['method'] = $values['SERVER']['REQUEST_METHOD'];
        }
        // Создание контейнера
        self::$input = new Input($values);
    }

    /**
     * Элементы пути URI
     * @return \Boolive\values\Values
     */
    static function PATH()
    {
        return self::$input->PATH;
    }

    /**
     * GET и POST данные
     * @return \Boolive\values\Values
     */
    static function REQUEST()
    {
        return self::$input->REQUEST;
    }

    /**
     * Загруженные файлы
     * @return \Boolive\values\Values
     */
    static function FILES()
    {
        return self::$input->FILES;
    }

    /**
     * Куки
     * @return \Boolive\values\Values
     */
    static function COOKIE()
    {
        return self::$input->COOKIE;
    }

    /**
     * Неформатированные данные
     * @return \Boolive\values\Values
     */
    static function RAW()
    {
        return self::$input->RAW;
    }

    /**
     * Информация о сервере и среде исполнения
     * @return \Boolive\values\Values
     */
    static function SERVER()
    {
        return self::$input->SERVER;
    }

    /**
     * Все входящие данные
     * @return \Boolive\input\Input
     */
    static function ALL()
    {
        return self::$input;
    }

    /**
     * Выбор всех входящих данных без фильтрации
     * @return mixed
     */
    static function getSource()
    {
        return self::$input->getValue();
    }

    /**
     * Нормализация массива $_FILES в соответствии с именованием полей формы
     * @return array
     */
    private static function normalizeFiles()
    {
        // Перегруппировка элементов массива $_FILES
        $rec_to_array = function ($array, $name) use (&$rec_to_array){
            $result = array();
            foreach ($array as $key => $value){
                if (is_array($value)){
                    $result[$key] = $rec_to_array($value, $name);
                }else{
                    $result[$key][$name] = $value;
                }
            }
            return $result;
        };
        $result = array();
        foreach ($_FILES as $field => $data){
            $result[$field] = array();
            foreach ($data as $name => $value){
                if (is_array($value)){
                    $result[$field] = F::arrayMergeRecursive($result[$field], $rec_to_array($value, $name));
                }else{
                    $result[$field][$name] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Правила по умолчанию
     * Правило отсутствует, поэтому без явного указания правиал при выборки значений значения не получить
     */
    protected function defineRule()
    {
        $this->_rule = null;
    }

    /**
     * Создание URL на основе текущего.
     * Если не указан ни один параметр, то возвращается URL текущего запроса
     * @param null|string|array $path Путь uri. Если не указан, то используется текущий путь
     * @param int $shift С какого парметра пути текущего URL делать замену на $path
     * @param array $args Массив аргументов.
     * @param bool $append Добавлять ли текущие аргументы к новым?
     * @param bool|string $host Добавлять ли адрес сайта. Если true, то добавляет адрес текущего сайта. Можно строкой указать другой сайт
     * @param string $schema Схема url. Указывается, если указан $host
     * @return string
     */
    static function url($path = null, $shift = 0, $args = array(), $append = false, $host = false, $schema = 'http://')
    {
        if (is_string($path)){
			$path = explode('/',trim($path,'/'));
		}
        if (!isset($path)) $path = array();
        $url = '';
        if (isset($path[0]) && $path[0] == 'contents'){
            array_shift($path);
        }
        // Параметры
        // Текущие параметры (текщего адреса) заменяем на указанные в $params
        $cur_path = self::PATH()->getValue();
        $index = sizeof($cur_path);
        if (is_array($path) and sizeof($path) > 0){
            foreach ($path as $index => $value){
                $cur_path[$index + $shift] = $value;
            }
            $index+=$shift + 1;
        }else
        if ($shift > 0){
            $index = $shift;
        }
        // Все текущие параметры после поcледнего из измененных отсекаются
        for ($i = 0; $i < $index; $i++){
            if (isset($cur_path[$i])){
                $url.=$cur_path[$i].'/';
            }else{
                $url.='/';
            }
        }


		// Аргументы
		if (!isset($args)){
            $args = self::SERVER()->argv->getValue();
        }else{
            if ($append){
                $args = array_merge(self::SERVER()->argv->getValue(), $args);
            }
        }
        if (isset($args['path'])) unset($args['path']);
		if (strlen($url) > 0){
            if (mb_substr($url,0,1)=='/') $url = mb_substr($url,1);
			$url = rtrim($url,'/');
		}
        if (is_array($args)){
			foreach ($args as $name => $value){
				$url .= '&'.$name.($value!==''?'='.$value:'');
			}
		}
		if ($host){
			return $schema.($host===true?HTTP_HOST.DIR_WEB:$host).$url;
		}else{
			return DIR_WEB.$url;
		}
    }
}