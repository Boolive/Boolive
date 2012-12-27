<?php
/**
 * Виджет Облако ключевых слов
 *
 * @version 1.0
 * @autor Hitev Kirill <nomer_47@mail.ru>
 */
namespace Library\content_widgets\KeywordsCloud;

use \Library\views\Widget\Widget;

class KeywordsCloud extends Widget
{

     public function work($v = array())
     {
       $list = $this->object->notLink()->findAll2(array(
           "where" => array(
               array('is', '/Library/content_samples/Keyword')
          )
        ));
     foreach ($list as $name=>$item){
         if($item['value']!=0){
            $v['list'][$name]['href']=$item['uri'];
            $v['list'][$name]['value']=$item['value'];
         }
     }
       return parent::work($v);
     }
}
