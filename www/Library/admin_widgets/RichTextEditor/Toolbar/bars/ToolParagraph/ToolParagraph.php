<?php
/**
 * Название
 *
 * @version 1.0
 * @date 15.04.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\RichTextEditor\Toolbar\bars\ToolParagraph;

use Library\views\Widget\Widget,
    Boolive\data\Data;

class ToolParagraph extends Widget
{
    public function work($v = array())
    {
        // Типы абзацев
        $plist = Data::read(array(
            'select' => 'children',
            'from' => '/Library/content_samples/paragraphs',
            'depth' => 1,
            'where' => array(
                array('is', '/Library/content_samples/paragraphs/TextBlock'),
                array('attr', 'is_hidden', '=', false)
            )
        ));
        $v['plist'] = array();
        foreach ($plist as $p){
            $v['plist'][] = array(
                'id' => $p->id(),
                'title' => $p->title->isExist()? $p->title->value() : $p->name()
            );
        }
        return parent::work($v);
    }
}