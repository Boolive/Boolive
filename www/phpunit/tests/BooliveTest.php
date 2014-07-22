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

    function _test()
    {
        echo PHP_MAXPATHLEN;
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

    function _test_move()
    {
        $obj = Data2::read('/library/basic/some_package');
        $obj->parent('/test');
//        $obj = new Entity(array(
//            'value' => '1',
//            'name' => 'Object',
//            'parent' => '/library/basic/some_package',
//            'proto' => 0,
//            'order' => 0,
//            'is_draft' => 0,
//            //'value_type'=> Entity::VALUE_TEXT
//        ));
        //$obj->parent($p);
        Data2::getStore()->write($obj);
        trace($obj);
    }

    function _test_add()
    {
        $obj = new Entity(array(
            'value' => 'Is real my value??',
            'name' => 'Object-proto',
            'parent' => '/library/basic2/some_package2',
            'proto' => '/content/X',
            'order' => 0,
            'is_draft' => 0
        ));
        $obj->name(null, true);
        $obj->isDefaultValue(true);
        Data2::getStore()->write($obj);
    }

    function _test_proto2()
    {
        $obj = new Entity(array(
            'value' => '100',
            'name' => 'object',
            'parent' => '/parent/not-exists',
            'proto' => '/proto/not-exists',
        ));
        $obj->name(null, true);//уникальность имени
        //$obj->isDefaultValue(true);
        Data2::getStore()->write($obj);
        trace($obj);
    }

    function _test_proto()
    {
        $obj = Data2::read('/library/basic/some_package/Object');
        $obj = $obj->birth('/test');
        $obj->isDefaultValue(true);
        Data2::write($obj);
        trace($obj);
    }

    function _test_edit_proto()
    {
        $obj = Data2::read('/library/basic/some_package/Object');
        $obj->value('Новое значение2499');
        Data2::write($obj);
        trace($obj);
    }

    function _test_read()
    {
        $obj = Data2::read('library');
        trace($obj->parent());
    }

    function test_error()
    {
        $obj = Data2::read('/library/javascripts/patterns/Boolive.Widget');
        trace($obj);
    }
}
 