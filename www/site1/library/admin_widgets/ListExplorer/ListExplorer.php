<?php
/**
 * Список
 * Обозреватель объектов в виде списка
 * @version 1.0
 */
namespace Site\library\admin_widgets\ListExplorer;

use Boolive\values\Rule;
use Site\library\admin_widgets\BaseExplorer\BaseExplorer;

class ListExplorer extends BaseExplorer
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in(null, 'structure', 'heirs', 'protos', 'parents')->required();
        return $rule;
    }
}