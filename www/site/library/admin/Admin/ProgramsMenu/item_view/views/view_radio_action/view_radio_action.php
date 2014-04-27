<?php
/**
 * Пункт действия "выбор варианта"
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace site\library\admin\Admin\ProgramsMenu\item_view\views\view_radio_action;

use boolive\values\Rule,
    site\library\admin\widgets\RadioAction\RadioAction,
    site\library\views\Widget\Widget;

class view_radio_action extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'program' => Rule::entity(array('is', '/library/admin/widgets/RadioAction'))->required(),
                'show' => Rule::bool()->default(true)->required(), // Показывать пункт или только его подчиненных?
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['show']){
            /** @var RadioAction $obj */
            $obj = $this->_input['REQUEST']['program']->linked();
            $v['title'] = $obj->title->value();
            $v['icon'] = $obj->icon->inner()->file();
            $v['list'] = $obj->radioItems();
            foreach ($v['list'] as $item){
                if ($item['active']){
                    $v['title'] = $item['title'];
                }
            }
            $v['show-item'] = true;
        }else{
            $v['show-item'] = false;
        }
        $v['program_uri'] = $this->_input['REQUEST']['program']->linked()->uri();
        return parent::show($v, $commands, $input);
    }
}