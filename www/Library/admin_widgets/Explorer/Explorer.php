<?php
/**
 * Обозреватель
 * Отображает списком свойства объекта
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer;

use Library\views\AutoWidgetList\AutoWidgetList, Boolive\values\Rule;;

class Explorer extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required(),
                        'filter' => Rule::arrays(Rule::string())
                           )
                        )
                    )
                );
    }

    public function work($v = array())
    {
        if (!empty($this->_input['REQUEST']['filter'])) {
            $this->filter->real->value($this->_input['REQUEST']['filter']['real']);
            $this->filter->hidden->value($this->_input['REQUEST']['filter']['hidden']);
            $this->filter->deleted->value($this->_input['REQUEST']['filter']['deleted']);
            $this->filter->virtual->value($this->_input['REQUEST']['filter']['virtual']);
            $this->filter->save();
        }
        $obj = $this->filter;
        $v['real'] = $obj->real->value();
        $v['hidden'] = $obj->hidden->value();
        $v['deleted'] = $obj->deleted->value();
        $v['virtual'] = $obj->virtual->value();
        $v['uri'] = $this->_input['REQUEST']['object']->uri();
        $v['head'] = $this->_input['REQUEST']['object']->title->value();
        if (empty($v['head'])) $v['head'] = $this->_input['REQUEST']['object']->name();
        return parent::work($v);
    }

    protected function getList($cond = array())
    {
        $obj = $this->filter;
        $any = array();
        if ($obj->real->value()) {
            $any[] = array('all', array(
                array('attr', 'is_hidden', '=', 0),
                array('attr', 'is_delete', '=', 0),
                array('attr', 'is_virtual', '=', 0)
            ));
        }
        if ($obj->hidden->value()) {
            $any[] = array('attr', 'is_hidden', '=', 1);
        }else{
            $cond['where'][] = array('attr', 'is_hidden', '=', 0);
        }
        if ($obj->deleted->value()) {
            $any[] = array('attr', 'is_delete', '=', 1 );
        }else{
            $cond['where'][] = array('attr', 'is_delete', '=', 0);
        }
        if ($obj->virtual->value()) {
            $any[] = array('attr', 'is_virtual', '=', 1);
        }else{
            $cond['where'][] = array('attr', 'is_virtual', '=', 0);
        }
        if (empty($any)) {
            return array();
        } else {
            $cond['where'][] = array('any', $any);
        }

        return parent::getList($cond);
    }
}
