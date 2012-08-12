<?php
/**
 * Корневой виджет
 * Определяет заголовки HTML документа, подключая необходимые теги в <HEAD>, запрашиваемые другими виджетами
 * @version 1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace library\basic\html;

use Boolive\data\Entity,
	Boolive\values\Rule,
	Boolive\template\Template,
	Boolive\input\Input;

class html extends Entity{

	public function getInputRule(){
		return Rule::arrays(array('previous' => Rule::not(true)), Rule::any());
	}

	public function work(){
		// Вызов всех подчиенных, чтобы исполнить после их команды добавления тегов
		$v = $this->startChildren();

		// Обработка своих команд для вставки тегов в заголовок HTML
		$v['head'] = '';
		$this->_commands->addHtml('base', array('href'=>'http://'.Input::SERVER()->HTTP_HOST->string().DIR_WEB));
		foreach ($this->_commands->get('addHtml') as $com){
			if (in_array($com[0], array("link", "meta", "script", "title", "base"))){
				if (isset($com[1]['text'])){
					$text = $com[1]['text'];
					unset($com[1]['text']);
				}else{
					$text='';
				}
				if (isset($com[1]['src'])) $com[1]['src'] = $com[1]['src'].'?'.TIMESTAMP;
				if (isset($com[1]['href']) && $com[0]!='base') $com[1]['href'] = $com[1]['href'].'?'.TIMESTAMP;
				$attr = '';
				foreach ($com[1] as $name => $value) $attr.=' '.$name.'="'.$value.'"';
				$v['head'].= '<'.$com[0].$attr.'>';
				if ($com[0] == "script" || $com[0] == "title"){
					$v['head'].= $text.'</'.$com[0].'>';
				}
				$v['head'].="\n";
			}
		}
		return Template::Render($this, $v);
	}
}
