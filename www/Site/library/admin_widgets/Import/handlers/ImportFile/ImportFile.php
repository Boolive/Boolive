<?php
/**
 * Базовый
 * Обрабатывает info файлы для любых объектов
 * @version 1.0
 */
namespace Site\library\admin_widgets\Import\handlers\ImportFile;

use Site\library\views\Task\Task;

class ImportFile extends Task
{
    function usageCheck($params)
    {
        return true;   
    }

    function work()
    {

    }
}