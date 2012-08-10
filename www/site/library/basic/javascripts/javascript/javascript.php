<?php
/**
 * JavaScript
 * Эталон для всех клиентских скриптов на языке JavaScript
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\basic\javascripts\javascript;

use Boolive\data\Entity;

class javascript extends Entity{

	public function defineRule(){
		parent::defineRule();
		$this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns('*.js');
	}

	public function work(){
		// Исполнение зависимых объектов
		$this->depends->start($this->_commands, $this->_input);
		// Подключение javascript файла
		if ($file = $this->getFile()){
			$this->_commands->addHtml('script', array('type'=>'text/javascript', 'src'=>$file));
		}
	}
}