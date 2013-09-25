<?php
/**
 * Обозреватель
 * Отображает свойства объекта (его подчиненных)
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer;

use Boolive\data\Entity,
    Library\views\AutoWidgetList2\AutoWidgetList2,
    Boolive\values\Rule;

class Explorer extends AutoWidgetList2
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required(),
                        'filter' => Rule::arrays(Rule::string()),
                        'call' => Rule::string(),
                        // Аргументы вызываемых методов (call)
                        'saveOrder' => Rule::arrays(Rule::arrays(Rule::string())),
                        'view_kind' => Rule::string(),
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
		if (!empty($this->_input['REQUEST']['call'])){
            //Изменение порядка элемента при сортировке drag-and-drop
            if (isset($this->_input['REQUEST']['saveOrder'])){
                return $this->callSaveOrder(
                    $this->_input['REQUEST']['saveOrder']['objectUri'],
                    $this->_input['REQUEST']['saveOrder']['nextUri']
                );
            }
            return null;
        }else{
            $filters = $this->filter->find(array('key'=>'name', 'cache'=>2), true);
            // Установка нового фильтра
            if (!empty($this->_input['REQUEST']['filter'])) {
                $this->filter->real->value($this->_input['REQUEST']['filter']['real']);
                $this->filter->hidden->value(!empty($this->_input['REQUEST']['filter']['hidden']));
                $this->filter->deleted->value(!empty($this->_input['REQUEST']['filter']['deleted']));
                $this->filter->updates->value(!empty($this->_input['REQUEST']['filter']['updates']));
                $this->filter->save();
            }
            // Текущий фильтр для отображения меню фильтра
            $v['filters'] = array();
            foreach ($filters as $name => $f) {
                if ($f instanceof \Library\basic\Boolean\Boolean) {
                    $v['filters'][$name] = array('title' => $f->title->value(), 'value' => $f->value());
                }
            }
            // Информация и видах для меню видов
            $kinds = $this->view_kind->find(array('key'=>'name'), true);
            $kind_set = empty($this->_input['REQUEST']['view_kind']) ? null : $this->_input['REQUEST']['view_kind'];
            $v['view-kinds'] = array();
            foreach ($kinds as $name => $k) {
                if ($k instanceof \Library\basic\Boolean\Boolean) {
                    if ($name == $kind_set) {
                        $this->view_kind->{$name}->value(true);
                    } else if ($kind_set) {
                        $this->view_kind->{$name}->value(false);
                    }
                    if ($k->value()) {
                        $this->_input_child['REQUEST']['view_kind'] = $name;
                    }
                    $v['view-kinds'][$name] = array('title' => $k->title->value(), 'value' => $k->value());
                }
            }
            $this->view_kind->save();

            $v['uri'] = $this->_input['REQUEST']['object']->uri();
            $v['head'] = $this->_input['REQUEST']['object']->title->value();
            if (empty($v['head']))
                $v['head'] = $this->_input['REQUEST']['object']->name();
            return parent::work($v);
        }
    }

    protected function getList($cond = array())
    {
        // Выбор свойств отображаемого объекта с учётом текущего фильтра
        $filters = $this->filter->find(array('key'=>'name', 'cache'=>2));
        $any = array();
        // Реальные объекты. У которых все признаки false
        if ($filters['real']->value()) {
            $any[] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_delete', '=', 0),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
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
        // Обновления
        if ($filters['updates']->value()) {
            $any[] = array('attr', 'diff', '!=', Entity::DIFF_NO);
        }else{
            $cond['where'][] = array('attr', 'diff', '!=', Entity::DIFF_ADD);
        }
        // Никакие
        if (empty($any)) {
            return array();
        } else {
            $cond['where'][] = array('any', $any);
        }
        $cond['group'] = true;
        return parent::getList($cond);
    }

    /**
     * Устанавливает новый порядок объектов
     */
    protected function callSaveOrder($object, $next)
    {
        $obj = \Boolive\data\Data::read($object['uri']);
        if (!empty($next)) {
            $next_object = \Boolive\data\Data::read($next['uri']);
            if ($next_object->isExist()) {
                if ($next['next'] > 0) {
                    $obj->order($next_object->order());
                } else {
                    $obj->order($next_object->order() + 1);
                }
            }
        }
        $obj->save();
        return true;
    }
}
