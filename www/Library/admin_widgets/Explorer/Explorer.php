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
                        'filter' => Rule::arrays(Rule::string()),
                        'call' => Rule::string(),
                        // Аргументы вызываемых методов (call)
                        'saveOrder' => Rule::arrays(Rule::arrays(Rule::string()))
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
    }

    protected function getList($cond = array())
    {
        $obj = $this->filter;
        if ($obj->isExist()){
            $any = array();
            if ($obj->real->value()) {
                $any[] = array('all', array(
                    array('attr', 'is_hidden', '=', 0),
                    array('attr', 'is_delete', '=', 0),
                    array('attr', 'is_virtual', '=', 0)
                ));
            }
            if ($obj->hidden->value()) {
                $any[] = array('attr', 'is_hidden', '!=', 0);
            }else{
                $cond['where'][] = array('attr', 'is_hidden', '=', 0);
            }
            if ($obj->deleted->value()) {
                $any[] = array('attr', 'is_delete', '!=', 0 );
            }else{
                $cond['where'][] = array('attr', 'is_delete', '=', 0);
            }
            if ($obj->virtual->value()) {
                $any[] = array('attr', 'is_virtual', '!=', 0);
            }else{
                $cond['where'][] = array('attr', 'is_virtual', '=', 0);
            }
            if (empty($any)) {
                return array();
            } else {
                $cond['where'][] = array('any', $any);
            }
        }
        return parent::getList($cond);
    }

    /**
     * Устанавливает новый порядок объектов
     */
    protected function callSaveOrder($object, $next){
        $obj = \Boolive\data\Data::read($object['uri']);
        if(!empty($next)){
            $nextObject = \Boolive\data\Data::read($next['uri']);
            if($nextObject->isExist()){
                if($next['next']>0){
                    $obj->order($nextObject->order());
                }else{
                    $obj->order($nextObject->order()+1);
                }
            }
        }

        $obj->save();
        return true;
    }
}
