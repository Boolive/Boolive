<?php
/**
 * Класс
 *
 * @version 1.0
 */
 

namespace phpunit\tests;


use boolive\Boolive;
use boolive\data\Data2;

class BooliveTest extends \PHPUnit_Framework_TestCase {

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $return = parent::__construct($name, $data, $dataName);
        $this->init();
        return $return;
    }

    private function init(){
        // Подключение конфигурации путей
        include '../config.php';
        // Подключение движка Boolive
        include DIR_SERVER.'boolive/Boolive.php';
        // Активация Boolive
        Boolive::activate();
    }

    function test()
    {
        trace(Data2::getStore()->getId('/library1', true));
        $this->assertTrue(true);
    }
}
 