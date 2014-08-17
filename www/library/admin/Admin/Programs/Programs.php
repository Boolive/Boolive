<?php
/**
 * Класс
 *
 * @version 1.0
 */
namespace site\library\admin\Admin\Programs;

use boolive\values\Rule;
use site\library\views\AutoWidget2\AutoWidget2;

class Programs extends AutoWidget2
{
    function startRule()
    {
        return Rule::any();
    }
}