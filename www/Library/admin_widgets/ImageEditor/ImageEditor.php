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
                        'object' => Rule::entity()->required(),
                        'call' => Rule::string(),
                        'attrib' => Rule::arrays(Rule::string()),
                        // Аргументы вызываемых методов (call)
                        'saveStyle' => Rule::arrays(Rule::string())
                    )
                ),
                'FILES' => Rule::arrays(array(
                         'attrib' => Rule::arrays(array(
                                 'file' => Rule::arrays(Rule::string())
                             )
                         )
                     )
                 )
            )
        );
    }

    public function work($v = array())
    {
        if (!empty($this->_input['REQUEST']['call'])){
            // Редактирование стиля
            if (isset($this->_input['REQUEST']['saveStyle'])){
                return $this->callSaveStyle(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['saveStyle']
                );
            }
            // Сохранение новой картинки
            if ($this->_input['REQUEST']['call'] == 'save'){
                return $this->callSave();
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
     * Сохранение атрибутов объекта
     * @return mixed
     */
    protected function callSave()
    {
        $v = array();
        if (empty($this->_input['REQUEST']['attrib'])) {
            // Если запрос действителен, то его пустота из-за лимита post_max_size
            $v['error']['value'] = 'Превышен допустимый размер отправляемых данных';
        } else {
            /** @var $obj \Boolive\data\Entity */
            $obj = $this->_input['REQUEST']['object'];
            if (isset($this->_input['FILES']['attrib']['file'])) {
                $obj->file($this->_input['FILES']['attrib']['file']);
                // Проверка и сохранение
                /** @var $error \Boolive\errors\Error */
                $error = null;
                $obj->save(false, false, $error);
                if (isset($error) && $error->isExist()) {
                    $v['error']['value'] = $error->getUserMessage(true);
                } else {
                    $v['attrib'] = $this->callLoad();
                    $v['attrib']['file'].='?'.rand();
                }
                return $v;
            }
        }
        return null;
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
    /**
    * Отправка атрибутов объекта
    * @return mixed
    */
    protected function callLoad()
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        $v = array(
            'file' => $obj->file(),
        );
        return $v;
    }
}