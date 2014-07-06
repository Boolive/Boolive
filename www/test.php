<?php
/**
 * Код для профилирования
 *
 * @version 1.0
 */
// Подключение конфигурации путей
include 'config.php';
// Подключение движка Boolive
include DIR_SERVER.'boolive/Boolive.php';
// Активация Boolive
\boolive\Boolive::activate();
for ($i=0; $i<1000; $i++){
    \boolive\data\Data2::read(array('from'=>'library', 'calc'=>false));
}
var_dump(\boolive\develop\Benchmark::stop('all', true));