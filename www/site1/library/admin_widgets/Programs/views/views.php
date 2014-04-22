<?php
/**
 * Список программ
 * Из этого списка автоматически выбирается один "вид" для отображения объекта
 * @version 1.0
 */
namespace Site\library\admin_widgets\Programs\views;

use Site\library\views\ViewSingle\ViewSingle;

class views extends ViewSingle
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['view_name']->default('views');
        return $rule;
    }
}