<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace library\basic\widgets\widget;

use Boolive\data\Entity,
	Boolive\template\Template;

class widget extends Entity{

	public function work($v = array()){
		return Template::Render($this, $v);
	}
}
