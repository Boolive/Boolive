<?php
/**
 * Редактор изображения
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\ImageEditor;

use Boolive\data\Entity,
    Boolive\errors\Error,
    Library\views\Widget\Widget,
    Boolive\values\Rule;

class ImageEditor extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        // Отображаемый объект или над которым выполняется действие
                        'object' => Rule::entity()->required(),
                        'call' => Rule::string(),
                        'attrib' => Rule::arrays(Rule::string()),
                        // Аргументы вызываемых методов (call)
                        'saveProperties' => Rule::arrays(array(
                            //'proto' => Rule::entity(),
                            'style' => Rule::arrays(Rule::string())
                            )
                        )
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
            if (isset($this->_input['REQUEST']['saveProperties'])){
                return $this->callSaveProperties(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['saveProperties']
                );
            }
            // Сохранение новой картинки
            if ($this->_input['REQUEST']['call'] == 'save'){
                return $this->callSave();
            }
            // Удаление
            if ($this->_input['REQUEST']['call'] == 'delete'){
                return $this->callDelete($this->_input['REQUEST']['object']);
            }
            return null;
        }else{
            $v['file'] = $this->_input['REQUEST']['object']->file();
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
            $v['is_hidden'] = $this->_input['REQUEST']['object']->isHidden(null, false);
            $v['is_delete'] = $this->_input['REQUEST']['object']->isDelete(null, false);
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
                //Cохранение
                try{
                    $obj->save(false, false);
                    $v['attrib'] = $this->callLoad();
                    $v['attrib']['file'].='?'.rand();
                }catch (Error $error){
                    $v['error']['value'] = $error->getUserMessage(true);
                }
                return $v;
            }
        }
        return null;
    }

    /**
     * Сохранение стиля объекта (если есть свойство style)
     * @param \Boolive\data\Entity $object Сохраняемый объект
     * @param array $properties Свойства стиля
     * @return mixed
     */
    protected function callSaveProperties($object, $properties)
    {
        $style = $object->style;
        if ($style->isExist() && isset($properties['style'])){
            foreach ($properties['style'] as $name => $value){
                $s = $style->{$name};
//                if ($s->isExist()){
                    $s->value($value);
//                }else{
//                    unset($style->{$name});
//                }
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

    /**
     * Удаление объекта
     * @param Entity $obj
     * @return mixed
     */
    protected function callDelete($obj)
    {
        /** @var $obj \Boolive\data\Entity */
        $obj->isDelete(true);
        $obj->save();
        return true;
    }
}