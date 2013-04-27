<?php
/**
 * Название
 *
 * @version 1.0
 * @date 15.04.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\RichTextEditor\Toolbar\tabs;

use Library\views\Widget\Widget;

class tabs extends Widget
{
    public function work($v = array())
    {
        $bars = $this->bars->linked()->find();
        $v['items'] = array();
        foreach ($bars as $p){
            $p = $p->linked();
            if ($p instanceof Widget){
                $item = array(
                    'title' => $p->title->value(),
                    'bar' => $p->id(),
                    'icon' => $p->icon->file()
                );
                $v['items'][] = $item;
            }
        }
        return parent::work($v);
    }
}