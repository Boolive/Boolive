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
 * @property \Boolive\values\Values REQUEST GET+POST данные или аргументы командной строки
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
        $values = array(
            //'GET' => array(),//isset($_GET)? $_GET : array(),
            //'POST' => isset($_POST)? $_POST : array(),
            'REQUEST' => array(),
            'FILES' => isset($_FILES)? self::normalizeFiles() : array(),
            'COOKIE' => isset($_COOKIE)? $_COOKIE : array(),
            'RAW' => empty($HTTP_RAW_POST_DATA)?'':$HTTP_RAW_POST_DATA, // Неформатированные данные
            'SERVER' => $_SERVER
        );
        if (isset($_SERVER['REQUEST_URI'])){
            parse_str('path='.urldecode($_SERVER['REQUEST_URI']), $values['REQUEST']);
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
        // Аргументы из консоли в get (режим CLI)
        if (empty($values['REQUEST']) && isset($_SERVER['argv'])) $values['REQUEST'] = $_SERVER['argv'];
        // Метод запроса
        if (isset($values['SERVER']['REQUEST_METHOD'])){
            $values['REQUEST']['method'] = $values['SERVER']['REQUEST_METHOD'];
        }
        // Создание контейнера
        self::$input = new Input($values);
    }

    /**
     * Элементы пути URI
     * @return \Boolive\values\Values
     */
    static function path()
    {
        return self::$input->path;
    }

    /**
     * POST данные
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
     * Выбор исходных неотфильтрованных всех данных
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
}
