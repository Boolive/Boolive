<?php
/**
 * Базовый обозреватель
 * Используется для создания программ отображения объектов. Имеет базовые функции фильтра, сортировки, отображения, постраничной навигации и другие.  
 * @version 1.0
 */
namespace Library\admin_widgets\BaseExplorer;

use Boolive\data\Data;
use Boolive\data\Entity;
use Boolive\values\Rule;
use Library\views\AutoWidgetList2\AutoWidgetList2;

class BaseExplorer extends AutoWidgetList2
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(),
//                'filter' => Rule::arrays(Rule::string()),
                'call' => Rule::string(),
                // Аргументы вызываемых методов (call)
                'saveOrder' => Rule::arrays(Rule::arrays(Rule::string())),
            ))
        ));
    }

    function work()
    {
        if (!empty($this->_input['REQUEST']['call'])){
            //Изменение порядка элемента при сортировке drag-and-drop
            if (isset($this->_input['REQUEST']['saveOrder'])){
                return $this->callSaveOrder(
                    $this->_input['REQUEST']['saveOrder']['object_uri'],
                    $this->_input['REQUEST']['saveOrder']['next_uri']
                );
            }
            return null;
        }
        return parent::work();
    }


    function show($v = array(), $commands, $input)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        $v['object'] = $obj->uri();
        $v['title'] = $obj->title->inner()->value();
        $v['description'] = $obj->description->inner()->value();
        if ($v['title'] === '') $v['title'] = $obj->name();
        if ($p = $obj->proto()){
            $v['proto-uri'] = $p->uri();
            $v['proto-title'] = $p->title->inner()->value();
            $v['proto-description'] = $p->description->inner()->value();
        }else{
            $v['proto-uri'] = '//0';
            $v['proto-title'] = 'Сущность';
            $v['proto-description'] = $obj->description->inner()->value();
        }
        if (!$obj->isExist()){
            $v['empty'] = 'Не найден';
            $v['empty_description'] = 'Объект, к которому вы обращаетесь отсутсвует';
        }else
        if (!$obj->isAccessible()){
            $v['empty'] = 'Нет доступа';
            $v['empty_description'] = 'Недостаточно прав для просмотра объекта';
        }else{
            $v['empty'] = 'Пусто';
            $v['empty_description'] = 'У объекта нет подчиненных или они не соответсятвуют фильтру';
        }
        return parent::show($v,$commands, $input);
    }

    protected function getList($cond = array())
    {
        $obj = array(
            'is_hidden' => $this->_input['REQUEST']['object']->attr('is_hidden'),
            'is_draft' => $this->_input['REQUEST']['object']->attr('is_draft'),
        );
        // Выбор свойств отображаемого объекта с учётом текущего фильтра
        $filters = $this->filter->linked()->find(array('key'=>'name', 'cache'=>2));
        $any = array();
        // Обычные объекты. У которых все признаки false
        if ($filters['real']->value()) {
            $any[] = array('all', array(
                array('attr', 'is_hidden', '=', $obj['is_hidden']),
                array('attr', 'is_draft', '=', $obj['is_draft']),
                array('attr', 'is_mandatory', '=', 0),
                array('attr', 'diff', '!=', Entity::DIFF_ADD)
            ));
        }
        // Скрытые объекты
        if ($filters['hidden']->value()) {
            $any[] = array('attr', 'is_hidden', '!=', $obj['is_hidden']);
        }else{
            $cond['where'][] = array('attr', 'is_hidden', '=', $obj['is_hidden']);
        }
        // Черновики
        if ($filters['draft']->value()) {
            $any[] = array('attr', 'is_draft', '!=', $obj['is_draft']);
        }else{
            $cond['where'][] = array('attr', 'is_draft', '=', $obj['is_draft']);
        }
        // Свойства
        if ($filters['mandatory']->value()) {
            $any[] = array('attr', 'is_mandatory', '!=', 0);
        }else{
            $cond['where'][] = array('attr', 'is_mandatory', '=', 0);
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
        $obj = Data::read($object['uri']);
        if (!empty($next)) {
            $next_object = Data::read($next['uri']);
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