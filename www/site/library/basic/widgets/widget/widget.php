<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace library\basic\widgets;

use Engine\Entity,
	Engine\Template;

class widget extends Entity{

	public function work($v = array()){
		return Template::Render($this, $v);
	}
}