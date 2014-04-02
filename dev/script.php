<?php
$count = isset($_POST['count'])?$_POST['count']:1;

while($count < 100){
    sleep(1);
    $f = fopen(dirname($_SERVER['SCRIPT_FILENAME']).'/test.txt', "w+");
    fwrite($f, ++$count.' '.date('G:i:s',time())."\n");
    fclose($f);
}


//sleep(1);
//if (++$count<100000){
//    $params = array('count'=>$count);
//    $fp = fsockopen($_SERVER['HTTP_HOST'], 80, $errno, $errstr, 30);
//    $data = http_build_query($params, '', '&'); //$params - массив с данными для б.пхп
//    fwrite($fp, "POST " . '/script.php' . " HTTP/1.1\r\n");
//    fwrite($fp, "Host: ".$_SERVER['HTTP_HOST']."\r\n");
//    fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
//    fwrite($fp, "Content-Length: " . strlen($data) . "\r\n");
//    fwrite($fp, "Connection: Close\r\n\r\n");
//    fwrite($fp, $data);
//    fclose($fp);
//}