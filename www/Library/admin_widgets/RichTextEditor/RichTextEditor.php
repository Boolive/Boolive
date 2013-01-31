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
                        'call' => Rule::string()->default('')->required(),
                        'saveStyle' => Rule::arrays(Rule::string())
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        if (!empty($this->_input['REQUEST']['call'])){
            // Сохранение атрибутов
            if ($this->_input['REQUEST']['call'] == 'new_p'){
                return $this->new_p();
            }else
            // Редактирование стиля
            if (isset($this->_input['REQUEST']['saveStyle'])){
                return $this->callSaveStyle(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['saveStyle']
                );
            }
            return null;
        }else{
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
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

    /**
     * Сохранение стиля объекта (если есть свойство style)
     * @param \Boolive\data\Entity $object Сохраняемый объект
     * @param array $styles Свойства стиля
     * @return mixed
     */
    protected function callSaveStyle($object, $styles)
    {
        $style = $object->style;
        if ($style->isExist()){
            foreach ($styles as $name => $value){
                $s = $style->{$name};
                if ($s->isExist()){
                    $s->value($value);
                }else{
                    unset($style->{$name});
                }
            }
            $style->save();
        }
        return true;
    }
}
