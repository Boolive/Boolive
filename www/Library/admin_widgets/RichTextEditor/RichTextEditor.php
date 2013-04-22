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
                        'object' => Rule::entity()->required(),
                        'call' => Rule::string()->default('')->required(),
                        'saveProperties' => Rule::arrays(array(
                            //'proto' => Rule::entity(),
                            'filter' => Rule::arrays(Rule::bool()),
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
        if (!empty($this->_input['REQUEST']['call'])){
            // Сохранение атрибутов
            if ($this->_input['REQUEST']['call'] == 'new_p'){
                return $this->new_p();
            }else
            // Редактирование стиля
            if (isset($this->_input['REQUEST']['saveProperties'])){
                return $this->callSaveProperties(
                    $this->_input['REQUEST']['object'],
                    $this->_input['REQUEST']['saveProperties']
                );
            }
            return null;
        }else{
            $v['object'] = $this->_input['REQUEST']['object']->uri();
            $v['style'] = $this->_input['REQUEST']['object']->style->getStyle();
            // Текущий фильтр для отображения меню фильтра
            $filters = $this->filter->find(array(), 'name', true);
            $v['filter'] = array();
            foreach ($filters as $name => $f) {
                if ($f instanceof \Library\basic\simple\Boolean\Boolean) {
                    $v['filter'][$name] = $f->value();
                }
            }
            $v['filter'] = json_encode($v['filter']);
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
     * @param array $properties Свойства стиля
     * @return mixed
     */
    protected function callSaveProperties($object, $properties)
    {
        // Фильтр
        if (isset($properties['filter'])){
            $filter = $this->filter;
            foreach ($properties['filter'] as $name => $value){
                $s = $filter->{$name};
                if ($s->isExist()){
                    $s->value($value);
                }else{
                    unset($filter->{$name});
                }
            }
            $filter->save();
        }

        // Стиль
        $style = $object->style;
        if ($style->isExist() && isset($properties['style'])){
            foreach ($properties['style'] as $name => $value){
                $s = $style->{$name};
                if ($s->isExist()){
                    $s->value($value);
                }else{
                    unset($style->{$name});
                }
            }
        }
        $object->save();
        return true;
    }

    protected function getList($cond = array())
    {
        // Выбор свойств отображаемого объекта с учётом текущего фильтра
        $filters = $this->filter->find();
        $any = array();
        // Реальные объекты. У которых все признаки false
        if ($filters['real']->value()) {
            $any[] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_delete', '=', 0)
            ));
        }
        // Скрытые объекты
        if ($filters['hidden']->value()) {
            $any[] = array('attr', 'is_hidden', '!=', 0);
        }else{
            $cond['where'][] = array('attr', 'is_hidden', '=', 0);
        }
        // Удаленные объекты
        if ($filters['deleted']->value()) {
            $any[] = array('attr', 'is_delete', '!=', 0 );
        }else{
            $cond['where'][] = array('attr', 'is_delete', '=', 0);
        }
        // Никакие
        if (empty($any)) {
            return array();
        } else {
            $cond['where'][] = array('any', $any);
        }
        return parent::getList($cond);
    }
}
