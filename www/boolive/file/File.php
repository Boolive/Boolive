<?php
/**
 * Класс для работы с файлами
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\file;

use boolive\functions\F;

class File
{
    const VIRT_DISK = 'B';
    static private $IS_WIN = false;

    static function activate()
    {
        self::$IS_WIN = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
    /**
     * Создание файла
     * @param string $content Содержимое файла
     * @param string $to Путь к создаваемому файлу
     * @param bool $append Добавлять в файл или пересоздавать его. По умолчанию пересоздаётся.
     * @return bool Признак, создан файл или нет
     */
    static function create($content, $to, $append = false)
    {
        $to = self::makeVirtualDir($to);
        // Если папки нет, то создаем её
        $dir = dirname($to);
        if(!is_dir($dir)){
            mkdir($dir, 0775, true);
        }
        $result = false;
        // Создание файла
        if (($f = fopen($to, $append?'a':'w'))) {
            stream_set_write_buffer($f, 20);
            fwrite($f, $content);
            fclose($f);
            $result = true;
        }
        self::deleteVirtualDir($to);
        return $result;
    }

    /**
     * Перемешщение загруженного файла по указанному пути
     * @param string $from Путь к загружаемому файлу
     * @param string $to Путь, куда файл копировать. Путь должен содерджать имя файла
     * @return bool Признак, загружен файл или нет
     */
    static function upload($from, $to)
    {
        $result = false;
        if(is_uploaded_file($from)){
            $to = self::makeVirtualDir($to);
            // Если папки нет, то создаем её
            $dir = dirname($to);
            if(!is_dir($dir)){
                mkdir($dir, 0775, true);
            }
            if (is_file($to)){
                unlink($to);
            }
            //Перемещаем файл если он загружен через POST
            $result = move_uploaded_file($from, $to);
            self::deleteVirtualDir($to);
        }
        return $result;
    }

    /**
     * Копирование файла
     * @param string $from Путь к копируемому файлу
     * @param string $to Путь, куда файл копировать. Путь должен содерджать имя файла
     * @return bool Признак, скопирован файл или нет
     */
    static function copy($from, $to)
    {
        $result = false;
        if (file_exists($from)){
            $to = self::makeVirtualDir($to);
            // Если папки нет, то создаем её
            $dir = dirname($to);
            if(!is_dir($dir)){
                mkdir($dir, 0775, true);
            }
            if (is_file($to)){
                unlink($to);
            }
            $result = copy($from, $to);
            self::deleteVirtualDir($to);
        }
        return $result;
    }

    /**
     * Переименование или перемещение файла
     * @param string $from Путь к переименовываемому файлу
     * @param string $to Путь с новым именем
     * @return bool Признак, переименован файл или нет
     */
    static function rename($from, $to)
    {
        $result = false;
        if (file_exists($from)){
            $to = self::makeVirtualDir($to);
            $dir = dirname($to);
            if (!is_dir($dir)) mkdir($dir, true);
            if (mb_strtoupper($from)!=mb_strtoupper($to)){
                if(is_dir($to)){
                    self::clearDir($to, true);
                }else
                if (is_file($to)){
                    unlink($to);
                }
            }
            $result = rename($from, $to);
            self::deleteVirtualDir($to);
        }
        return $result;
    }

    /**
     * Удаление файла
     * @param string $from Путь к удаляемому файлу
     * @return bool
     */
    static function delete($from)
    {
        $from = self::makeVirtualDir($from, false);
        $result = false;
        if (is_file($from)){
            @unlink($from);
            $result = true;
        }
        self::deleteVirtualDir($from);
        return $result;
    }

    /**
     * Удаление пустой директории
     * @param $dir
     * @return bool
     */
    static function deleteEmtyDir($dir)
    {
        $dir = self::makeVirtualDir($dir, false);
        if (is_dir($dir) && sizeof(scandir($dir)) == 2){
            return @rmdir($dir);
        }
        self::deleteVirtualDir($dir);
        return false;
    }

    /**
     * Удаление всех файлов и поддиректорий в указанной директории
     * @param string $dir Путь на очищаемому директорию
     * @param bool $delete_me Удалить указанную директорию (true) или только её содержимое (false)?
     * @return bool Признак, выполнено ли удаление
     */
    static function clearDir($dir, $delete_me = false)
    {
        $dir = self::makeVirtualDir($dir, false);
        $result = false;
        if (is_file($dir)){
            $result = @unlink($dir);
        }else
        if (is_dir($dir)){
            $scan = glob(rtrim($dir, '/').'/*');
            foreach ($scan as $path){
                self::clearDir($path, true);
            }
            $result = $delete_me?@rmdir($dir):true;
        }
        self::deleteVirtualDir($dir);
        return $result;
    }

    /**
     * Возвращает имя и расширение файла из его пути
     * Путь может быть относительным. Файл может отсутствовать
     * @param string $path Путь к файлу
     * @param null $key Какую информацию о файле возвратить? dir, name, base, ext. Если null, то возвращается всё в виде массива
     * @return array|string Имя без расширения, расширение, полное имя файла и директория
     */
    static function fileInfo($path, $key = null)
    {
        $path = str_replace('\\','/',$path);
        $list = F::explode('/', $path, -2);
        if (sizeof($list)<2){
            array_unshift($list, '');
        }
        $info = array('dir'=>$list[0], 'name'=>$list[1], 'base'=> '', 'ext'=>'', 'back'=>false);
        if (($len = mb_strlen($list[0]))>1 && mb_substr($list[0], $len-2)=='..'){
            $info['back'] = true;
        }
        $list = F::explode('.', $info['name'], -2);
        // Если $list имеет один элемент, то это не расширение
        if (sizeof($list)>1){
            $info['ext'] = strtolower($list[1]);
        }else{
            $info['ext'] = '';
        }
        $info['base'] = $list[0];
        if ($key){
            return $info[$key];
        }
        return $info;
    }

    /**
     * Смена расширения в имени файла.
     * Смена имени не касается самого файла!
     * @param string $path Путь к файлу
     * @param string $ext Новое расширение файла
     * @return string Новое имя файла
     */
    static function changeExtention($path, $ext)
    {
        $dir = dirname($path).'/';
        $f = self::fileInfo($path);
        return $dir.$f['base'].'.'.$ext;
    }

    /**
     * Смена имени файла не меняя расширения.
     * Смена имени не касается самого файла!
     * @param string $path Путь к файлу
     * @param string $name Новое имя файла без расширения
     * @return string Новое имя файла
     */
    static function changeName($path, $name)
    {
        $f = self::fileInfo($path);
        return $f['dir'].$name.'.'.$f['ext'];
    }

    /**
     * Имя файла из пути на файл
     * @param $path string путь к файлу
     * @return string|null
     */
    static function fileName($path)
    {
        $list = explode('/', $path);
        return array_pop($list);
    }

    /**
     * Расширение файла
     * @param $path
     * @return mixed
     */
    static function fileExtention($path)
    {
        $list = F::explode('.', $path, -2);
        if (sizeof($list)>1){
            return strtolower($list[1]);
        }else{
            return '';
        }
    }

    /**
     * Создание уникального имени для файла или директории
     * @param $dir Директория со слэшем на конце, в которой подобрать уникальное имя
     * @param $name Базовое имя, к которому будут добавляться числовые префиксы для уникальности
     * @param $ext Расширение с точкой, присваиваемое к имени после подбора
     * @param int $start Начальное значение для префикса
     * @return string|bool Уникальное имя вместе с путём или false, если не удалось подобрать
     */
    static function makeUniqueName($dir, $name, $ext, $start = 1)
    {
        self::makeVirtualDir($dir);
        $i = 0;
        $to = $dir.$name.$ext;
        while (file_exists($to) && $i<100){
            $to = $dir.$name.(++$i+$start).$ext;
        }
        $result = ($i < 100+$start)? false : $to;
        self::deleteVirtualDir($dir);
        return $result;
    }

    /**
     * Текст ошибки загрзки файла
     * @param int $error_code Код ошибки
     * @return string
     */
    static function uploadErrorMmessage($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Превышен максимально допустимый размер файла';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Превышен максимально допустимый размер, указанный в html форме';
            case UPLOAD_ERR_PARTIAL:
                return 'Файл загружен не полностью';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Файл не сохранен во временной директории';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Ошибка записи файла на диск';
            case UPLOAD_ERR_EXTENSION:
                return 'Загрузка файла прервана сервером';
            case UPLOAD_ERR_NO_FILE:
            default:
                return 'Файл не загружен';
        }
    }

    /**
     * Фильтр имени файла. Транслит и удаление спец.символов
     * @param string $filename Имя файла
     * @return string
     */
    static function clearFileName($filename)
    {
        $filename = str_replace(' ', '_', F::translit($filename));
        return preg_replace('/[^0-9A-Z\x5F\x61-\x7A\x2D\x5F\x5B\x5D\x2E\x28\x29]/u', '', $filename);
    }

    /**
     * Размер директории в байтах
     * @param string $dir Путь на директорию
     * @return int
     */
    static function getDirSize($dir)
    {
        $size = 0;
        $dirs = array_diff(scandir($dir), array('.', '..'));
        foreach ($dirs as $d){
            $d = $dir.'/'.$d;
            $size+= filesize($d);
            if (is_dir($d)){
                $size+= self::getDirSize($d);
            }
        }
        return $size;
    }

    /**
     * Создание пути на директорию из идентификатора
     * @param string $id Идентификатор, который режится на имена директорий. При недостаточности длины добавляются нули.
     * @param int $size Длина для имен директорий
     * @param int $depth Вложенность директорий
     * @return string
     */
    static function makeDirName($id, $size=3, $depth=3)
    {
        $size = intval(max(1, $size));
        $depth = intval(max(1,$depth));
        $id = self::clearFileName($id);
        $id = str_repeat('0',max(0, $size*$depth-strlen($id))).$id;
        $dir = '';
        for ($i=1; $i<$depth; $i++){
            $dir = substr($id,-$size*$i, $size).'/'.$dir;
        }
        return (substr($id,0, -$size*($i-1))).'/'.$dir;
    }

    /**
     * Раскрывает переход типа '/../
     * @warning Не понимает вложенные переходы типа /../../
     * @param string $path
     * @return string
     */
    static function realPath($path)
    {
        return preg_replace('/[^\/]+\/\.\.\//', '', $path);
    }

    /**
     * Создание виртуального диска в Windows, для увеличения лимита на длину пути к файлам
     * @param $dir
     * @param bool $mkdir
     * @return mixed|string
     */
    static function makeVirtualDir($dir, $mkdir = true)
    {
        if (self::$IS_WIN && mb_strlen($dir) > 248){
            $dir = preg_replace('/\\\\/u','/', $dir);
            $vdir = mb_substr($dir, 0, 248);
            $vdir = F::splitRight('/', $vdir);
            if ($vdir[0]){
                $vdir = $vdir[0];
                if (!is_dir($vdir)){
                    if ($mkdir){
                        mkdir($vdir, 0775, true);
                    }else{
                        return $dir;
                    }
                }
                $dir = self::VIRT_DISK.':/'.mb_substr($dir, mb_strlen($vdir)+1);
                system('subst '.self::VIRT_DISK.': '.$vdir);
            }
        }
        return $dir;
    }

    /**
     * Удаление виртуального диска в Windows
     * @param string $vdir Директория с виртуальным диском
     */
    static function deleteVirtualDir($vdir)
    {
        if (self::$IS_WIN && mb_substr($vdir,0,1) == self::VIRT_DISK){
            system('subst '.self::VIRT_DISK.': /d');
        }
    }

    static function deleteEmptyDirs($path)
    {
        $path = DIR.trim($path,'/\\');
        $empty = true;
        foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file){
            $empty &= is_dir($file) && self::deleteEmptyDirs($file);
        }
        return $empty && rmdir($path);
    }
}
