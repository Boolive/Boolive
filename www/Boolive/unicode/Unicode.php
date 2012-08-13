<?php
/**
 * Класс для работы с кодировкой юникод (UTF-8)
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\unicode;

class Unicode
{
    static private $translit_table;
    /**
     * Активация
     * Устанавливает UTF-8 кодировкой по умолчанию для всех мультибайтовых функций
     *
     */
    static function activate()
    {
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
    }

    /**
     * Код символа в UTF-8 строке
     *
     * @param string $chr символ
     * @return int
     */
    static function ord($chr)
    {
        $ord0 = ord($chr);
        if ($ord0 >= 0 && $ord0 <= 127){
            return $ord0;
        }
        if (!isset($chr{1})){
            trigger_error('Short sequence - at least 2 bytes expected, only 1 seen');
            return FALSE;
        }
        $ord1 = ord($chr{1});
        if ($ord0 >= 192 && $ord0 <= 223){
            return ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
        }
        if (!isset($chr{2})){
            trigger_error('Short sequence - at least 3 bytes expected, only 2 seen');
            return FALSE;
        }
        $ord2 = ord($chr{2});
        if ($ord0 >= 224 && $ord0 <= 239){
            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        if (!isset($chr{3})){
            trigger_error('Short sequence - at least 4 bytes expected, only 3 seen');
            return FALSE;
        }
        $ord3 = ord($chr{3});
        if ($ord0 >= 240 && $ord0 <= 247){
            return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        }
        if (!isset($chr{4})){
            trigger_error('Short sequence - at least 5 bytes expected, only 4 seen');
            return FALSE;
        }
        $ord4 = ord($chr{4});
        if ($ord0 >= 248 && $ord0 <= 251){
            return ($ord0 - 248) * 16777216 + ($ord1 - 128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
        }
        if (!isset($chr{5})){
            trigger_error('Short sequence - at least 6 bytes expected, only 5 seen');
            return FALSE;
        }
        if ($ord0 >= 252 && $ord0 <= 253){
            return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($chr{5}) - 128);
        }
        if ($ord0 >= 254 && $ord0 <= 255){
            trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0);
            return FALSE;
        }
        return null;
    }

    /**
     * Проверка системных требований для установки модуля
     * @return bool
     */
    static function systemRequirements()
    {
        if (!extension_loaded('mbstring')){
            return 'Требуется расширение "mbstring" для PHP';
        }
        return true;
    }

    /**
     * Транслит с русского в английский
     * @param $string
     * @return string
     */
    static function translit($string)
    {
        if (!isset(self::$translit_table)){
            self::$translit_table = array(
                'А' => 'A',		'Б' => 'B',		'В' => 'V',		'Г' => 'G',		'Д' => 'D',
                'Е' => 'E',		'Ё' => 'YO',	'Ж' => 'ZH',	'З' => 'Z',		'И' => 'I',
                'Й' => 'J',		'К' => 'K',		'Л' => 'L',		'М' => 'M',		'Н' => 'N',
                'О' => 'O',		'П' => 'P',		'Р' => 'R',		'С' => 'S',		'Т' => 'T',
                'У' => 'U',		'Ф' => 'F',		'Х' => 'H',		'Ц' => 'C',		'Ч' => 'CH',
                'Ш' => 'SH',	'Щ' => 'CSH',	'Ь' => '',		'Ы' => 'Y',		'Ъ' => '',
                'Э' => 'E',		'Ю' => 'YU',	'Я' => 'YA',
                'а' => 'a',		'б' => 'b',		'в' => 'v',		'г' => 'g',		'д' => 'd',
                'е' => 'e',		'ё' => 'yo',	'ж' => 'zh',	'з' => 'z',		'и' => 'i',
                'й' => 'j',		'к' => 'k',		'л' => 'l',		'м' => 'm',		'н' => 'n',
                'о' => 'o',		'п' => 'p',		'р' => 'r',		'с' => 's',		'т' => 't',
                'у' => 'u',		'ф' => 'f',		'х' => 'h',		'ц' => 'c',		'ч' => 'ch',
                'ш' => 'sh',	'щ' => 'csh',	'ь' => '',		'ы' => 'y',		'ъ' => '',
                'э' => 'e',		'ю' => 'yu',	'я' => 'ya',
            );
        }
        return @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', str_replace(array_keys(self::$translit_table), array_values(self::$translit_table), $string));
    }
}
