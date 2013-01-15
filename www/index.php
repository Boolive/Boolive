<?php
/**
 * Boolive!
 * Главный исполняемый файл. Запуск движка и проекта
 *
 * @version 2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @link http://boolive.ru
 */
use Boolive\Boolive,
    Boolive\data\Data,
    Boolive\commands\Commands,
    Boolive\input\Input;
// Подключение конфигурации путей
require 'config.php';
// Подключение движка Boolive
require DIR_SERVER_ENGINE.'Boolive.php';
// Активация Boolive
Boolive::activate();
//\Boolive\session\Session::set('test', 111);
//trace(\Boolive\session\Session::get('test'));
// Исполнение корневого объекта. Вывод результата клиенту
echo Data::read()->start(new Commands(), Input::getSource());

//$store = \Boolive\data\Data::getStore('');
//$store->rebuildTree();

//$o1 = Data::read('/Members');
//$o2 = $o1->proto();
//$news = Data::read('/Contents/news');
//
//$list = $news->find(array(
//   'where' => array('is', '/Library/content_samples/Page'),
//   'order' => array('date', 'ASC'),
//   'limit' => array(0,20)
//));
//
//\Boolive\develop\Trace::groups('DB')->out();
//
//trace($list);
//$u = \Boolive\auth\Auth::getUser();
//
//trace($u);

//$m = $u->ggg;

//trace($m);

/** @var $user \Library\access\User\User */
//$user = Data::read('/Members/registered/admins/vova');
//
//trace($user->getAccessCond('read'));
//trace(Data::read('/Members/guests/rights/visitor'));

////return;
//$config = array(
//    // Имя источника данных
//    'dsn' => array(
//        // Тип СУБД
//        'driver' => 'mysql',
//        // Имя базы данных
//        'dbname' => 'boolive2-git',
//        // Адрес сервера
//        'host' => 'localhost',
//        // Порт
//        'port' => '3306'
//    ),
//    // Имя пользователя для подключения к базе данных
//    'user' => 'root',
//    // Пароль
//    'password' => 'proot',
//    // Опции подключения
//    'options' => array(
//        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_bin"'
//    ),
//    // Префикс к таблицам индекса
//    'prefix' => '',
//    // Признак, включен или нет режим отладки. В режиме отладки трассируются запросы и подсчитывается их кол-во
//    'debug' => true
//);
//
//$store = new \Boolive\data\stores\MySQLStore('', $config);



//
//$cond = $user->getAccessCond('read');
//
//trace($cond);
//
//$x = $store->getCondSQL(array(
//        'from'=> array('//3'),
//        'where'=> array($cond)
//    ), 0,0, true
//);
//trace($x);
//
//
////
//$db = $store->db;
//$cnt = 0;
//$q2 = $db->prepare('INSERT INTO ids (id, uri) VALUES (null, ?)');
//$q3 = $db->prepare('SELECT 1 FROM ids WHERE uri = ? LIMIT 0,1');
//$q = $db->query('SELECT * FROM ids');
//while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
//    if ($row['uri']){
//        $names = \Boolive\functions\F::splitRight('/', $row['uri']);
//        $q3->execute(array($names[0]));
//        if (!$q3->fetch()){
//            $q2->execute(array($names[0]));
//            $cnt+=$q2->rowCount();
//        }
//    }
//}
//
//trace($cnt);


//
//$q = $db->query('
//    SELECT ids.* FROM ids
//');
//$update = $db->prepare('UPDATE objects SET parent_cnt = ? WHERE id = ?');
//$a = 0;
//while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
//    $update->execute(array(mb_substr_count($row['uri'], '/'), $row['id']));
//    $a+=$update->rowCount();
//}
//trace($a);
//
//$db = $store->db;
//$q = $db->prepare('
//    SELECT {ids}.uri, {objects}.id, {objects}.parent, {objects}.proto, {objects}.parent_cnt, {objects}.proto_cnt
//    FROM {objects}, {ids}
//    WHERE {ids}.id = {objects}.id
//    ORDER BY {ids}.uri');
//$q->execute();
//$list = array();
//while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
//
//    $store->makeTree($row['id'], $row['parent'], $row['proto'], false);
//    //$list[] = $row;
//
//}
//trace('ok');
//$q = $db->prepare('
//    SELECT {ids}.uri, {objects}.id, {objects}.parent, {objects}.proto, {objects}.parent_cnt, {objects}.proto_cnt
//    FROM {objects}, {ids}
//    WHERE {ids}.id = {objects}.id
//    ORDER BY {objects}.proto_cnt');
//$q->execute();
//$list = array();
//while ($row = $q->fetch(\Boolive\database\DB::FETCH_ASSOC)){
//
//    $store->makeTree($row['id'], $row['parent'], $row['proto'], true);
//    //$list[] = $row;
//
//}
//trace('ok');
//trace($list);

//
//
//$store->makeTree(11, 2, 0, false);

//$object = Data::read('');

//$x = new GGGF();
//trace($store->select(array(
//    'from'=> array('//3'),
//    'where'=> array(
//        array('child', 'f', array())
//    )
//),'id'));
//
////$store->write($object);
//
//$obj = \Boolive\data\Data::read('');
//
//$x = $obj->yyy;
//$x->parent($obj->test);
//
//$x->save();
//
//trace($x);
//
//\Boolive\develop\Trace::groups('DB')->out();
?>