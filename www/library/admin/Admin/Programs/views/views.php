<?php
/**
 * Список программ
 * Из этого списка автоматически выбирается один "вид" для отображения объекта
 * @version 1.0
 */
namespace site\library\admin\Admin\Programs\views;

use site\library\views\ViewSingle\ViewSingle;

class views extends ViewSingle
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['view_name']->default('views');
        return $rule;
    }
}