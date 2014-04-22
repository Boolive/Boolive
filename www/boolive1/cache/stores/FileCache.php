<?php
/**
 * Кэш-хранилище на файлах
 *
 * @version 1.0
 * @date 13.06.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\cache\stores;

class FileCache
{
    /** @var string Полный путь к директории для файлов кэша */
    private $dir;
    /** @var  string Ключ хранилища */
    private $store_key;
    /** @var int Период в секундак после последнего обращения к кэшу, когда его можно удалить */
    private $clear_period = 0;

    function __construct($store_key, $config)
    {
        $this->store_key = $store_key;
        $this->dir = $config['dir'];
    }

    /**
     * Чтение значения
     * @param string $key Ключ значения
     * @param int $time Количество секунд валидности значения с момента последнего его изменения
     * @return null|string Если значения нет, то null.
     */
    function get($key, $time = 0)
    {
        try{
            $file = $this->dir.$key.'.cache';
			if ($time > 0 && is_file($file) && (time() - filemtime($file)) > $time){
                $this->delete($key);
            }else{
                return file_get_contents($file);
            }
		}catch (\Exception $e){}
        return null;
    }

    /**
     * Запись значения
     * @param string $key Ключ значения.
     * @param string $value Значение для записи
     * @return bool Признак, было ли значение записано в кэш?
     */
    function set($key, $value)
    {
        try{
			$file = $this->dir.$key.'.cache';
			$temp = $file.rand(0, 1000);
			$path = dirname($file);
			// Создание директории для кэш файла
			if (!is_dir($path)) mkdir($path, 0777, true);
			// Создание кэш файла с временным именем
			$f = fopen($temp, 'w');
			fwrite($f, $value);
			fclose($f);
			// Переименовываем в оригинальное имя.
			// Таким образом не нужно использовать блокировки при файлов
			try{
				rename($temp, $file);
			}catch (\Exception $e){
				unlink($file);
				rename($temp, $file);
			}
			chmod($file, 0777);
			return true;
		}catch (\Exception $e){
			return false;
		}
    }

    /**
     * Удаление значения из кэша
     * @param string $key Ключ значения.
     * @return bool Признак, было ли значение удалено?
     */
    function delete($key)
    {
        try{
			return unlink($this->dir.$key.'.cache');
		}catch (\Exception $e){
			return false;
		}
    }

    /**
     * Очистка старых кэш значений
     * @param $key
     */
    function clear($key, $ext = '.cache')
    {
        if (is_file($file = $this->dir.$key.$ext)){
            if (time()-fileatime($file) > $this->clear_period) unlink($file);
        }else
        if (is_dir($dir = $this->dir.$key)){
            $dirs = array_diff(scandir($dir), array('.', '..'));
            foreach ($dirs as $d){
                $this->clear(($key?$key.'/':'').$d, '');
            }
            if (!$dirs) rmdir($dir);
        }
    }
}