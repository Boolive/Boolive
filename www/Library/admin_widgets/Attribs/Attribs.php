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
    Boolive\values\Rule,
    Boolive\data\Data;

class Attribs extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required(),
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
        $v['object_uri'] = $this->_input['REQUEST']['object']->uri();
        $v['head'] = $this->title->value();
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

            // Название
            if (isset($attribs['name']) && $attribs['name']!=$obj->name()) $obj->name($attribs['name'], true);

            // Значение
            if (empty($attribs['is_null'])){
                if (isset($attribs['value'])) $obj->value($attribs['value']);
            }else{
                // Обнуление значения
                $obj->isDefaultValue(true);
            }
            // Файл
            if (isset($this->_input['FILES']['attrib']['file'])){
                $obj->file($this->_input['FILES']['attrib']['file']);
            }else{
                $obj->isFile(!empty($attribs['is_file']));
            }

            // Прототип
            if (isset($attribs['proto'])) $obj->proto(Data::read($attribs['proto']));
            // Язык
            //if (isset($attribs['lang'])) $obj['lang'] = $attribs['lang'];
            // Владелец
            //if (isset($attribs['owner'])) $obj['owner'] = $attribs['owner'];

            // Родитель
            if (isset($attribs['parent'])) $obj->parent(Data::read($attribs['parent']));

            // Порядковый номер
            if (isset($attribs['order'])) $obj->order($attribs['order']);

            // Признаки
            $c = $obj->isDefaultClass();
            $class_changed = (bool)$obj->isDefaultClass() != empty($attribs['is_logic']);
            $obj->isDefaultClass(empty($attribs['is_logic']));
            $obj->isHidden(!empty($attribs['is_hidden']));
            $obj->isLink(!empty($attribs['is_link']));
            $obj->isDefaultChildren(!empty($attribs['override']));

            // Проверка и сохранение
            /** @var $error \Boolive\errors\Error */
            $error = null;
            $obj->save(false, false, $error);
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
                if ($class_changed){
                    $this->_input['REQUEST']['object'] = Data::read($obj->id(), $obj->owner(), $obj->lang(), 0, true, false);
                }
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
            'uri' => $obj->uri(),
            'name' => $obj->name(),
            'proto' => ($p = $obj->proto()) ? $p->uri() : 'null',
            'parent' => ($p = $obj->parent()) ? $p->uri() : 'null',
            'owner' => ($p = $obj->owner()) ? $p->uri() : 'null',
            'lang' => ($p = $obj->lang()) ? $p->uri() : 'null',
            'value' => $obj->value(),
            'is_null' => $obj->isDefaultValue(),
            'value_null' => $obj->proto() ? (string)$obj->proto()->value() : '',
            'is_file' => $obj->isFile(),
            'is_file_null' => $obj->proto() ? $obj->proto()->isFile() : false,
//            'lang' => $obj->_attribs['lang'],
//            'owner' => $obj->_attribs['owner'],
            'date' => date('j.m.Y, G:s', $obj->date()),
            'order' => $obj->order(),
            'is_logic' => (bool)$obj->isDefaultClass()!=\Boolive\data\stores\MySQLStore::MAX_ID,//['is_logic'],
            'is_hidden' => (bool)$obj->isHidden(),
            'is_link' => (bool)$obj->isLink(),
            'override' => (bool)$obj->isDefaultChildren(),
            'class' => get_class($obj),
            'class_self' => trim(str_replace('/', '\\', $obj->dir().$obj->name()), '\\')
        );
        return $v;
    }
}