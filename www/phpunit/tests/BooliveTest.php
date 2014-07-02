<?php
/**
 * Класс
 *
 * @version 1.0
 */
 

namespace phpunit\tests;

use boolive\Boolive;
use boolive\data\Data2;
use boolive\data\Entity;

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

    function _test_getId()
    {
        trace(Data2::getStore()->getId('/library/test2', true));
    }

    function _test_reserveId()
    {
        for ($i=0; $i<1000; $i++){
            echo Data2::getStore()->reserveId()."\n";
        }
    }

    function _test_order()
    {
        //echo Data2::getStore()->ordersShift(0,2, 5, 4);
    }

    function _test_write()
    {
        $obj = new Entity(array(
            'id' => 5,
            'uri' => '/members',
            'parent' => 2,
            'parent_cnt' => 1,
            'order' => 3
        ));
        $obj->name('library');
        Data2::getStore()->write($obj);
    }
}
 