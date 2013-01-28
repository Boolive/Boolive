<?php
/**
 * Редактор форматированного текста
 *
 * @version 1.0
 */
namespace Library\admin_widgets\RichTextEditor;

use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule,
    Boolive\data\Data;

class RichTextEditor extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->default($this->object)->required(),
                        'call' => Rule::string()->default('')->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        // Сохранение атрибутов
        if ($this->_input['REQUEST']['call'] == 'new_p'){
            return $this->new_p();
        }else{
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            return parent::work($v);
        }
    }

    protected function new_p()
    {
        $text = $this->_input['REQUEST']['object'];
        $p = Data::read('/Library/content_samples/Paragraph')->birth($text);
        $this->_input_child['REQUEST'] = array('object' => $p);
        $p->_attribs['is_exist'] = 1;
        if ($result = $this->startChild('switch_views')){
            return $result;
        }
        return false;
    }
}
