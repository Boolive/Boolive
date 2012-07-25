<?php
/**
 * Ядро. Главный класс системы.
 * @version 1.0
 * @link http://boolive.ru/createcms/cms-engine
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

class Engine{
	/**
	 * Запуск системы. Инициализация.
	 */
	static function Start(){
		// Регистрация метода-обработчка завершения выполнения системы
		register_shutdown_function(array('\Engine\Engine', 'Stop'));
		// Подключение файла класса для управления всеми классами.
		// Остальные файлы классов будут подключаться автоматически при первом обращении
		include_once DIR_SERVER_ENGINE.'classes/Classes.php';
		// Принудельная активация необходимых системе классов
		Classes::Activate('Engine\Classes');
		Classes::Activate('Engine\Benchmark');
		Classes::Activate('Engine\Trace');
		Classes::Activate('Engine\Unicode');
		Classes::Activate('Engine\ErrorsHandler');
		// При необходимости, каждый класс может автоматически подключиться и активироваться, обработав событие START.
		Events::Send('START');
		Engine::Work();
	}

	/**
	 * Выполнение системы
	 * Передача управления объектам интерфейса и вывод результатов их работы
	 */
	static function Work(){
		// Исполнение корневого объекта и вывод клиенту результата
		echo Data::Object('')->start(new Commands(), Input::all());
	}

	/**
	 * Завершение выполнения системы
	 * Метод вызывается автоматически интерпретатором при завершение всех действий системы
	 * или при разрыве соединения с клиентом
	 */
	static function Stop(){
		// Любой класс может выполнить завершающие действия при звершении работы системы, обработав событие STOP
		Events::Send('STOP');
	}

	/**
	 * Проверка системных требований для установки класса Engine
	 * @return array Массив сообщений - требований для установки
	 */
	static function SystemRequirements(){
		$requirements = array();
		// Проверка совместимости версии PHP
		if (!version_compare(PHP_VERSION, "5.3.3", ">=")){
			$requirements[] = 'Несовместимая версия PHP. Установлена '.PHP_VERSION.' Требуется 5.3.3 или выше';
		}
		if (!ini_get('short_open_tag')){
			//$requirements[] = 'В настройках PHP включите параметр "короткие теги" (short_open_tag On)';
		}
		if (ini_get('register_globals')){
			$requirements[] = 'В настройках PHP отключите "регистрацию глобальных переменных" (register_globals Off)';
		}
		if (ini_get('magic_quotes_runtime')){
			$requirements[] = 'В настройках PHP отключите "магические кавычки для функций" (magic_quotes_runtime Off)';
		}
		// Проверка наличия модуля mod_rewrite
		if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())){
			$requirements[] = 'Требуется включить модуль "mod_rewrite" для сервера Apache. Обратитесь в тех. поддержку или настройте сервер самостоятельно.
			Включение выполняется в файле конфигурации "...\Apache\conf\httpd.conf" опцией LoadModule';
		}
		$file = DIR_SERVER.'config.php';
		if (!is_writable($file)){
			$requirements[] = 'Установите права на запись для файла "'.$file.'". Необходимо для автоматической записи настроек системы';
		}
		$file = DIR_SERVER.'.htaccess';
		if (!is_writable($file)){
			$requirements[] = 'Установите права на запись для файла "'.$file.'". Необходимо для автоматической записи настроек системы';
		}
		if (!is_writable(DIR_SERVER)){
			$requirements[] = 'Установите права на запись для директории "'.DIR_SERVER_PROJECT.'" и всех её вложенных директорий и файлов';
		}
		return $requirements;
	}

	/**
	 * Установка класса
	 *
	 */
	static function Install(){
		$file = DIR_SERVER.'.htaccess';
		if (is_writable($file)){
			$content = file_get_contents($file);
			// Прописывание базовой директории для mod_rewrite
			$content = preg_replace('/\n[ \t]*RewriteBase[ \t\S]*/u', "\n\tRewriteBase ".DIR_WEB, $content);
			$fp = fopen($file, 'w');
			fwrite($fp, $content);
			fclose($fp);
		}
	}

	/**
	 * Отключение класса
	 *
	 */
	static function Uninstall(){

	}
}