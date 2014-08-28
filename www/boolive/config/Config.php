<?php
/**
 * Модуль для чтения и записи файлов конфигурации
 *
 * @version 1.0
 * @date 21.08.2014
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\config;

use boolive\functions\F;

class Config
{
    /**
     * Признак, использовать ли синтакиси квадратных скобок в опредлении массива
     * @var bool
     */
    static $bracket_syntax = true;
    /**
     * Получение конфигурации по названию
     * По имени находится файл конфиграции в директрии DIR_CONFIG
     * @param $name string Название конфигурации
     * @return mixed
     */
    static function read($name)
    {
        try{
            return include DIR_CONFIG.$name.'.php';
        }catch (\Exception $e){
            return array();
        }
    }

    /**
     * Запись кофигруации в файл
     * @param $name string Название конфигурации
     * @param $config mixed
     * @param null $comment
     * @param bool $pretty
     */
    static function write($name, $config, $comment = null, $pretty = true)
    {
        $fp = fopen(DIR_CONFIG.$name.'.php', 'w');
        fwrite($fp, self::generate($config, $comment, $pretty));
        fclose($fp);
    }

    /**
     * Проверка возможности записать файл конфигураци
     * @param $name string Название конфигурации
     * @return bool
     */
    static function is_writable($name)
    {
        if (file_exists(DIR_CONFIG.$name.'.php')){
            return is_writable(DIR_CONFIG.$name.'.php');
        }
        return is_writable(DIR_CONFIG);
    }

    static function file_name($name)
    {
        return DIR_CONFIG.$name.'.php';
    }

    /**
     * Генератор содержимого файла конфигурации
     * @param array $config
     * @param string $comment
     * @param bool $pretty
     * @return string
     */
    static function generate(array $config, $comment = '', $pretty = true)
    {
        $arraySyntax = array(
            'open' => self::$bracket_syntax ? '[' : 'array(',
            'close' => self::$bracket_syntax ? ']' : ')'
        );
        $code = "<?php\n";
        if ($comment){
            $comment = explode("\n",$comment);
            $code.="/**\n";
            foreach ($comment as $line) $code.=' * '.$line."\n";
            $code.=" */\n";
        }
        return $code.
               "return " . $arraySyntax['open'] . "\n" .
        ($pretty ? F::arrayToCode($config, $arraySyntax) : var_export($config, true) ).
               $arraySyntax['close'] . ";\n";
    }
}
 