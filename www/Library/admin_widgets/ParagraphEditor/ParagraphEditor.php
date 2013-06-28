<?php
/**
 * Редактор абзаца
 *
 * @version 1.0
 * @date 17.01.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\admin_widgets\ParagraphEditor;

use Boolive\data\Entity;
use Library\views\Widget\Widget,
    Boolive\values\Rule,
    Boolive\data\Data;

class ParagraphEditor extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        // Отображаемый объект или над которым выполняется действие
                        'object' => Rule::entity(array('is', '/Library/content_samples/paragraphs/TextBlock')),
                        'call' => Rule::string(),
                        // Аргументы вызываемых методов (call)
                        'devide' => Rule::arrays(array(
                            'value1' => Rule::string()->default('')->required(),
                            'value2' => Rule::string()->default('')->required()
                        )),
                        'merge' => Rule::arrays(array(
                            'primary' => Rule::entity(array('is', '/Library/content_samples/paragraphs/TextBlock'))->required(),
                            'secondary' => Rule::entity(array('is', '/Library/content_samples/paragraphs/TextBlock'))->required()
                        )),
                        'save' => Rule::string(),
                        'saveProperties' => Rule::arrays(array(
                            'proto' => Rule::entity(),
                            'style' => Rule::arrays(Rule::string())
                            )
                        )
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        if (isset($this->_input['REQUEST']['call'])){
            // Отправка атрибутов
            if ($this->_input['REQUEST']['call'] == 'load'){
                 return array('attrib'=>$this->callLoad());
            }else
            // Удаление
            if ($this->_input['REQUEST']['call'] == 'delete'){
                return $this->callDelete($this->_input['REQUEST']['object']);
            }else
            // Разделение
            if (isset($this->_input['REQUEST']['devide'])){
                return $this->callDevide(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['devide']['value1'],
                    $this->_input['REQUEST']['devide']['value2']
                );
            }else
            // Соединение
            if (isset($this->_input['REQUEST']['merge'])){
                 return $this->callMerge(
                     $this->_input['REQUEST']['merge']['primary'],
                     $this->_input['REQUEST']['merge']['secondary']
                 );
            }else
            // Сохранение атрибутов
            if (isset($this->_input['REQUEST']['save'])){
                return $this->callSave(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['save']
                );
            }else
            // Сохранение стиля
            if (isset($this->_input['REQUEST']['saveProperties'])){
                return $this->callSaveProperties(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['saveProperties']
                );
            }
            return null;
        }else{
            $v['attrib'] = $this->callLoad();
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            return parent::work($v);
        }
    }

    /**
     * Объединение
     * @param \Boolive\data\Entity $primary
     * @param \Boolive\data\Entity $secondary
     * @return array
     */
    protected function callMerge($primary, $secondary)
    {
        $primary->value($primary->value().$secondary->value());
        $primary->save();
        $secondary->isDelete(true);
        $secondary->save();
        return true;
    }

    /**
     * Раздление текстового блока на два
     * @param \Boolive\data\Entity $object Объект разделяемого текстового блока
     * @param $value1 Значение для первого блока
     * @param $value2 Значение для второго блока
     * @return array
     */
    protected function callDevide($object, $value1, $value2)
    {
        $obj = array();
        $obj[0] = $object;
        // Обновить (обрезать) значение объекта
        $obj[0]->value($value1);
        // Создать новый объект с прототипом как у $obj со вторым значением
        // Отправить uri объектов или сгенерировать их html???
        $obj[1] = $object->proto()->birth($object->parent());
        $obj[1]->order($object->order()+1);
        $obj[1]->value($value2);

        $result = array();
        foreach ($obj as $o){
            $v = array();
            $o->save();
            $this->_input['REQUEST']['object'] = $o;
            $v['attrib'] = $this->callLoad();
            $v['object'] = $o->uri();
            $result[] = array(
                'html' => parent::work($v),
                'uri' => $o->uri()
            );
        }
        return $result;
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

    /**
     * Сохранение значения объекта
     * @param \Boolive\data\Entity $object Сохраняемый объект
     * @param string $value Новое значение
     * @return mixed
     */
    protected function callSave($object, $value)
    {
        $v = array();
        //$value = htmlentities($value, null, 'UTF-8');
        $object->value($value);
        // Проверка и сохранение
        /** @var $error \Boolive\errors\Error */
        $error = null;
        $object->save(false, false, $error);
        if (isset($error) && $error->isExist()){
            $v['error'] = array();
            if ($error->isExist('_attribs')){
                foreach ($error->_attribs as $key => $e){
                    /** @var $e \Boolive\errors\Error */
                    $v['error'][$key] = $e->getUserMessage(true,' ');
                }
                $error->delete('_attribs');
            }
            $v['error']['_other_'] = $error->getUserMessage(true);
        }else{
            $v['attrib'] = $this->callLoad();
        }
        return $v;
    }

    /**
     * Сохранение стиля объекта (если есть свойство style)
     * @param \Boolive\data\Entity $object Сохраняемый объект
     * @param array $properties Свойства стиля
     * @return mixed
     */
    protected function callSaveProperties($object, $properties)
    {
        // Смена прототипа (типа абзаца)
        if (isset($properties['proto']) && $properties['proto']->isExist() && $properties['proto']->is('/Library/content_samples/paragraphs/TextBlock')){
            $object->proto($properties['proto']);
            $object->save();
        }
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
     * @return array
     */
    protected function callLoad()
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        $v['tag'] = 'p';
        if ($obj->is('/Library/content_samples/paragraphs/H1')){
            $v['tag'] = 'h1';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H2')){
            $v['tag'] = 'h2';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H3')){
            $v['tag'] = 'h3';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H4')){
            $v['tag'] = 'h4';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H5')){
            $v['tag'] = 'h5';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/H6')){
            $v['tag'] = 'h6';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/Head')){
            $v['tag'] = 'h1';
        }
        if ($obj->is('/Library/content_samples/paragraphs/Blockquote')){
            $v['tag'] = 'blockquote';
        }else
        if ($obj->is('/Library/content_samples/paragraphs/Code')){
            $v['tag'] = 'pre';
        }
        $v['style'] = $obj->style->getStyle();
        $v['value'] = $obj->value();
        $v['uri'] = $obj->uri();
        $v['is_hidden'] = $obj->isHidden(null, false);
        $v['is_delete'] = $obj->isDelete(null, false);
        $v['proto'] = $obj->proto()? $obj->proto()->id() : null;
        return $v;
    }
}