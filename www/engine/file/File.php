<?php
/**
 * Класс для работы с файлами
 * Особенности
 * 1. Отложенное исполнение методов. Имитация транзакций
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Calls,
	Engine\Error;

class File{

	/**
	 * Создание файла
	 * @param string $content Содержимое файла
	 * @param string $to Путь к создаваемому файлу
	 * @param bool $create_uniqname Признак, генерировать ли уникальное имя, если указанный файл в $to уже существует
	 * @throws Error
	 * @return bool
	 */
	static function Create($content, $to, $create_uniqname = false){
		$dir = dirname($to);
		$info =  self::FileInfo($to);
		if (empty($info['name'])){
			throw new Error('File is not defined');
		}
		// Подбор уникального имени
		if ($create_uniqname){
			$i = 0;
			while (is_file($to) && $i<100){
				$to = $dir.'/'.$info['base'].(++$i).'.'.$info['ext'];
			}
			if ($i==100){
				throw new Error('File name is already used');
			}
		}
		if (!Calls::PullMethod('\\Engine\\File', 'Create', array($content, $to, $create_uniqname))){
			// Если папки нет, то создаем её
			$dir = dirname($to);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}
			// Создание файла
			if (($f = fopen($to, "w"))) {
				stream_set_write_buffer($f, 20);
				fwrite($f, $content);
				fclose($f);
			}
		}
		return true;
	}

	/**
	 * Перемешщение загруженного файла по указанному пути
	 * @param string $from Путь к исходному файлу
	 * @param string $to Путь, куда файл копировать. Путь должен содерджать имя файла
	 * @param bool $create_uniqname Признак, генерировать ли уникальное имя, если указанный файл в $to уже существует
	 * @throws Error
	 * @return bool Результат перемещения
	 */
	static function Upload($from, $to, $create_uniqname = true){
		if(is_uploaded_file($from)){
			$dir = dirname($to);
			$info =  self::FileInfo($to);

			if(empty($info['name'])){
				throw new Error('File is not defined');
			}
			// Подбор уникального имени
			if ($create_uniqname){
				$i = 0;
				while (is_file($to) && $i<100){
					$to = $dir.'/'.$info['base'].(++$i).'.'.$info['ext'];
				}
				if ($i==100){
					throw new Error('File name is already used');
				}
			}
			if (!Calls::PullMethod('\\Engine\\File', 'Upload', array($from, $to, $create_uniqname))){
				// Если папки нет, то создаем её
				$dir = dirname($to);
				if(!is_dir($dir)){
					mkdir($dir, 0777, true);
				}
				if (is_file($to)){
					unlink($to);
				}
				//Перемещаем файл если он загружен через POST
				return move_uploaded_file($from, $to);
			}
			return true;
		}else{
			throw new Error('File not uploaded');
		}
	}

	/**
	 * Копирование файла
	 * @param string $from
	 * @param string $to
	 * @return bool
	 */
	static function Copy($from, $to){
		if (!Calls::PullMethod('\\Engine\\File', 'Copy', array($from, $to))){
			// Если папки нет, то создаем её
			$dir = dirname($to);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}
			if (is_file($to)){
				unlink($to);
			}
			return copy($from, $to);
		}else{
			return true;
		}
	}

	/**
	 * Переименование или перемещение файла
	 * @param string $from
	 * @param string $to
	 * @return bool
	 */
	static function Rename($from, $to){
		if (!Calls::PullMethod('\\Engine\\File', 'Rename', array($from, $to))){
			$dir = dirname($to);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}
			if (is_file($to)){
				unlink($to);
			}
			return rename($from, $to);
		}else{
			return true;
		}
	}

	/**
	 * Удаление всех файлов и поддиректорий в указанной директории
	 * @param string $dir
	 * @param bool $delete_me Удалить указанную директорию?
	 * @return bool
	 */
	static function ClearDir($dir, $delete_me = false){
		if (!Calls::PullMethod('\\Engine\\File', 'ClearDir', array($dir, $delete_me))){
			if (is_file($dir)){
				return @unlink($dir);
			}else
			if (is_dir($dir)){
				$scan = glob(rtrim($dir, '/').'/*');
				foreach ($scan as $path){
					self::ClearDir($path, true);
				}
				return $delete_me?@rmdir($dir):true;
			}
			return false;
		}else{
			return true;
		}
	}

	/**
	 * Возвращает имя и расширение файла из его пути
	 * Путь может быть относительным. Файл может отсутствовать
	 * @param string $path Путь к файлу
	 * @param null $key Какую информацию о файле возвратить? dir, name, base, ext. Если null, то возвращается всё в виде массива
	 * @return array|string Имя без расширения, расширение, полное имя файла и директория
	 */
	static function FileInfo($path, $key = null){
		$path = str_replace('\\','/',$path);
		$list = F::Explode('/', $path, -2);
		if (sizeof($list)<2){
			array_unshift($list, '');
		}
		$info = array('dir'=>$list[0], 'name'=>$list[1], 'base'=> '', 'ext'=>'', 'back'=>false);
		if (($len = mb_strlen($list[0]))>1 && mb_substr($list[0], $len-2)=='..'){
			$info['back'] = true;
		}
		$list = F::Explode('.', $info['name'], -2);
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
	static function ChangeExtention($path, $ext){
		$dir = dirname($path).'/';
		$f = self::FileInfo($path);
		return $dir.$f['base'].'.'.$ext;
	}

	/**
	 * Смена имени файла не меняя расширения.
	 * Смена имени не касается самого файла!
	 * @param string $path Путь к файлу
	 * @param string $name Новое имя файла без расширения
	 * @return string Новое имя файла
	 */
	static function ChangeName($path, $name){
		$f = self::FileInfo($path);
		return $f['dir'].$name.'.'.$f['ext'];
	}

	/**
	 * Имя файла из пути на файл
	 * @param $path string путь к файлу
	 * @return string|null
	 */
	static function FileName($path){
		$list = explode('/', $path);
		return array_pop($list);
	}



	/**
	 * Текст ошибки загрзки файла
	 * @param int $error_code Код ошибки
	 * @return string
	 */
	static function UploadErrorMmessage($error_code){
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
	static function ClearFileName($filename){
		$filename = str_replace(' ', '_', Unicode::Translit($filename));
		return preg_replace('/[^0-9A-Z\x5F\x61-\x7A\x2D\x5F\x5B\x5D\x2E\x28\x29]/u', '', $filename);
	}

	/**
	 * Размер директории в байтах
	 * @param string $dir Путь на директорию
	 * @return int
	 */
	static function GetDirSize($dir){
		$size = 0;
		$dirs = array_diff(scandir($dir), array('.', '..'));
		foreach ($dirs as $d){
			$d = $dir.'/'.$d;
			$size+= filesize($d);
			if (is_dir($d)){
				$size+= self::GetDirSize($d);
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
	static function MakeDirName($id, $size=3, $depth=3){
		$size = intval(max(1, $size));
		$depth = intval(max(1,$depth));
		$id = self::ClearFileName($id);
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
	static function RealPath($path){
		return preg_replace('/[^\/]+\/\.\.\//', '', $path);
	}
}
