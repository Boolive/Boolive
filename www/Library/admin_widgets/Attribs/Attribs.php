<?php
/**
 * Атрибуты
 * Редактор атрибутов любого объекта
 * 1. При обычном запросе возвращает пустой html со стилями и скриптом
 * 2. По AJAX отдаёт атрибуты редактируемого объекта, проверяет и сохраняет объект
 * @version 1.0
 */
namespace Library\admin_widgets\Attribs;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class Attribs extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->default($this->object)->required(),
                        'call' => Rule::string()->default('')->required(),
                        'attrib' => Rule::arrays(Rule::string())
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
        // Отправка атрибутов
        if ($this->_input['REQUEST']['call'] == 'load'){
             return array('attrib'=>$this->callLoad());
        }
        // Сохранение атрибутов
        if ($this->_input['REQUEST']['call'] == 'save'){
            return $this->callSave();
        }
        // Передача шаблона, скриптов, стилей
        $v['object_uri'] = $this->_input['REQUEST']['object']['uri'];
        $v['head'] = $this->title->getValue();
        return parent::work($v);
    }

    /**
     * Сохранение атрибутов объекта
     * @return mixed
     */
    protected function callSave()
    {
        $v = array();
        if (empty($this->_input['REQUEST']['attrib'])){
            // Если запрос действителен, то его пустота из-за лимита post_max_size
            $v['error']['value'] = 'Превышен допустимый размер отправляемых данных';
        }else{
            /** @var $obj \Boolive\data\Entity */
            $obj  = $this->_input['REQUEST']['object'];

            $attribs = $this->_input['REQUEST']['attrib'];
            // Значение
            if (empty($attribs['value_set_null'])){
                if (isset($attribs['value'])) $obj['value'] = $attribs['value'];
            }else{
                // Обнуление значения
                $obj['value'] = null;
            }
            // Файл
            if (isset($this->_input['FILES']['attrib']['file'])){
                $obj['file'] = $this->_input['FILES']['attrib']['file'];
            }else
            if (empty($attribs['is_file'])){
                $obj['is_file'] = false;
            }

            // Прототип
            //if (isset($attribs['proto'])) $obj['proto'] = $attribs['proto'];
            // Язык
            //if (isset($attribs['lang'])) $obj['lang'] = $attribs['lang'];
            // Владелец
            //if (isset($attribs['owner'])) $obj['owner'] = $attribs['owner'];

            // Порядковый номер
            if (isset($attribs['order'])) $obj['order'] = $attribs['order'];

            // Признаки
            $obj['is_logic'] = !empty($attribs['is_logic']);
            $obj['is_hidden'] = !empty($attribs['is_hidden']);
            $obj['is_link'] = !empty($attribs['is_link']);
            $obj['override'] = !empty($attribs['override']);

            // Проверка и сохранение
            /** @var $error \Boolive\errors\Error */
            $error = null;
            $obj->save(false, $error);
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
        }
        return $v;
    }

    /**
     * Отправка атрибутов объекта
     * @return mixed
     */
    protected function callLoad()
    {
        /** @var $obj \Boolive\data\Entity */
        $obj  = $this->_input['REQUEST']['object'];
        $v = array(
            'uri' => $obj['uri'],
            'proto' => $obj['proto'],
            'value' => (string)$obj->getValue(),
            'is_null' => is_null($obj['value']),
            'value_null' => $obj->proto() ? (string)$obj->proto()->getValue() : '',
            'is_file' => $obj->isFile(),
            'is_file_null' => $obj->proto() ? $obj->proto()->isFile() : false,
            'lang' => $obj['lang'],
            'owner' => '',//$obj['owner'],
            'date' => date('j.m.Y, G:s', $obj['date']),
            'order' => $obj['order'],
            'is_logic' => (bool)$obj['is_logic'],
            'is_hidden' => (bool)$obj['is_hidden'],
            'is_link' => (bool)$obj['is_link'],
            'override' => (bool)$obj['override'],
        );
        return $v;
    }
}