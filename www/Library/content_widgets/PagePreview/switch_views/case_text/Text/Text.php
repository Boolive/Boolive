<?php
/**
 * Текст анонса
 *
 * @version 1.0
 */
namespace Library\content_widgets\PagePreview\switch_views\case_text\Text;

use Library\content_widgets\RichText\RichText;

class Text extends RichText
{
    protected function getList()
    {
        $cases = $this->linked(true)->switch_views->getCases();
        $cnt = sizeof($cases);
        $protos = array();
        while ($cnt > 0){
            $cnt--;
            if ($cases[$cnt]->value() == 'all'){
                $protos = array();
                $cnt = 0;
            }else{
                $protos[] = $cases[$cnt]->value();
            }
        }
        // @todo Сделать настраиваемый фильтр
        return $this->_input['REQUEST']['object']->find(array(
            'where' => array('is', $protos),
            'limit' => array(0, 2)
        ), null);
    }
}