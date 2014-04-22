<?php
/**
 * Плитка
 * Обозреватель объектов в виде плитки
 * @version 1.0
 */
namespace site\library\admin_widgets\TileExplorer;

use boolive\values\Rule;
use site\library\admin_widgets\BaseExplorer\BaseExplorer;

class TileExplorer extends BaseExplorer
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in(null, 'structure', 'heirs', 'protos', 'parents')->required();
        return $rule;
    }
}