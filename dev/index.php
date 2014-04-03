<?php
/**
 * Boolive!
 * Главный исполняемый файл. Запуск движка и проекта
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 * @requirement PHP 5.3 или новее
 */
// Подключение конфигурации путей
include 'config.php';
// Подключение движка Boolive
include DIR_SERVER.'Boolive/Boolive.php';
// Активация Boolive
if (Boolive\Boolive::activate()){
    // Исполнение корневого объекта. Передаётся экземпляр команд и все входящие данные. Вывод результата клиенту
    echo Boolive\data\Data::read()->start(new Boolive\commands\Commands(), Boolive\input\Input::getSource());
}else{
    // Запуск установщика, если Boolive не активирован
    include DIR_SERVER.'Boolive/installer/Installer.php';
    Boolive\installer\Installer::start();
}
//print ini_get("extension_dir");//preg_replace("@/lib(64)?/.*$@", "/bin/php", ini_get("extension_dir"));

//function execInBackground($cmd)
//{
//    if (substr(php_uname(), 0, 7) == "Windows"){
//        pclose(popen("start /B ". $cmd, "r"));
//    }else{
//        exec($cmd . " > /dev/null &");
//    }
//}
//function getPHPExecutableFromPath() {
//  $paths = explode(PATH_SEPARATOR, getenv('PATH'));
//  foreach ($paths as $path) {
//    // we need this for XAMPP (Windows)
//    if (isset($_SERVER["WINDIR"]) && strstr($path, 'php.exe') && file_exists($path) && is_file($path)) {
//        return $path;
//    }else{
//        $php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
//        if (file_exists($php_executable) && is_file($php_executable)) {
//           return $php_executable;
//        }
//    }
//  }
//  return false;
//}
////echo getPHPExecutableFromPath();
//execInBackground('D:\SERVER\XAMP\php\php.exe D:\SERVER\Sites\boolive2-git\www\script.php');
//
//echo 'Work';
//
//header("Connection: close");
//ob_start();
////phpinfo();
//print '555';
////$a = 10;
////echo "\n";
//$size=ob_get_length();
//header("Content-Length: $size");
//ob_end_flush();
//flush();
//sleep(30);
//echo 10;

//header("Connection: close");
//ob_start();
//
//echo 'Привет';
//
//$size=ob_get_length();
//header("Content-Length: $size");
//ob_end_flush();
//flush();
//ob_start();

//sleep(1000);
    //$f = fopen(DIR_SERVER.'test.txt', "w");
    //fwrite($f, "Test");
    //fclose($f);

//ob_end_clean();