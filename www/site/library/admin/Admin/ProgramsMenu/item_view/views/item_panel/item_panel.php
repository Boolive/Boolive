<?php
/**
 * Пункт панели настроек
 * Элемент пользовательского интерфейса. Отображает данные с помощью шаблонизации.
 * @version 1.0
 */
namespace site\library\admin\Admin\ProgramsMenu\item_view\views\item_panel;

use boolive\values\Rule;
use site\library\admin\widgets\Panel\Panel;
use site\library\views\Widget\Widget;

class item_panel extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'program' => Rule::entity(array('is', '/library/admin/widgets/Panel'))->required(),
                'base_uri' => Rule::string()->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        /** @var Panel $obj */
        $obj = $this->_input['REQUEST']['program'];
        $real = $obj->linked();
        $v['title'] = $real->title->value();
        $v['description'] = $real->title->value();
        $v['icon'] = $real->icon->inner()->file();
        $v['program'] = preg_replace('/'.preg_quote($this->_input['REQUEST']['base_uri'],'/').'/u', '', $obj->uri());
        return parent::show($v, $commands, $input);
    }
}