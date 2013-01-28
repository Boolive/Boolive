<?php
/**
 * Редактор изображения
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\ImageEditor;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class ImageEditor extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        // Отображаемый объект или над которым выполняется действие
                        'object' => Rule::entity()->default($this->object)->required(),
                        'call' => Rule::string(),
                        // Аргументы вызываемых методов (call)
                        'save_style' => Rule::arrays(Rule::string()),
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        if (!empty($this->_input['REQUEST']['call'])){
            // Редактирование стиля
            if (isset($this->_input['REQUEST']['save_style'])){
                return $this->callSaveStyle(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['save_style']
                );
            }
            return null;
        }else{
            $v['file'] = $this->_input['REQUEST']['object']->file();
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
            return parent::work($v);
        }

    }

    /**
     * Сохранение стиля
     * @param \Boolive\data\Entity $object Объект изображения
     * @param array $styles Массив строковых свойств стиля
     * @return bool
     */
    protected function callSaveStyle($object, $styles)
    {
        $style = $object->style;
        foreach ($styles as $name => $value){
            $style->{$name}->value($value);
        }
        $style->save();
        return true;
    }
}