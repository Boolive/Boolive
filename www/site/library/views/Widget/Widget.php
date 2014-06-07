<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace site\library\views\Widget;

use boolive\template\php\PHPTemplateValues;
use boolive\template\Template,
    site\library\views\View\View,
    boolive\values\Rule;

class Widget extends View
{

    /**
     * Инициализация Виджета
     */
    protected function init()
    {
//        if (!$this->isLink()){
//            $this->find(array('select'=>array('tree'), 'depth'=>array(1,'max'), 'return'=>false, 'comment' => 'read tree of widgets', 'order' => array('order', 'asc')), false);
//        }
    }

    /**
     * Инициализация входящих данных
     * Если нету своего объекта (модели), то берется объект из входящих данных
     * @param $input
     * @return mixed|void
     */
    function startInit($input)
    {
        if ($this->object->isExist() && !$this->object->isDraft(null, false)){
            $input['REQUEST']['object'] = $this->object;
        }
        parent::startInit($input);
    }

    /**
     * Правило на входящие данные - условие работы виджета
     * @return null|\boolive\values\Rule
     */
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                'path' => Rule::regexp($this->path_rule->value())->required(),
            ))
        ));
    }

    function work()
    {
        return $this->show(array(), $this->_commands, $this->_input);
    }

    /**
     * Формирование отображения с помощью шаблонизации
     * @param array $v
     * @param $commands
     * @param $input
     * @return string
     */
    function show($v = array(), $commands, $input)
    {
        $this->startChild('res');
        $v['view_id'] = $this->key();
        $v['view_uri'] = $this->uri();
        return $this->render($this, $v);
    }

    function render($entity, $v)
    {
        try{
            if ($this->isFile()){
                ob_start();
                    // Массив $v достпуен в php-файле шаблона, подключамом ниже
                    $v = new PHPTemplateValues($v, null, $this);
                    include($this->file(null, true));
                    $result = ob_get_contents();
                ob_end_clean();
                return $result;
            }else{
                return $this->value();
            }
        }catch (\Exception $e){
            ob_end_clean();
//          if ($e->getCode() == 2){
              echo "Error in template:".$e->getMessage();
//          }else{
//                throw $e;
//          }
        }
    }

    function classTemplate($methods = array(), $use = array())
    {
//        $use[] = 'boolive\values\Rule';
//        $methods['startRule'] =
//<<<php
//    /**
//     * Правило на входящие данные - условие работы виджета
//     * @return null|\boolive\values\Rule
//     */
//    function startRule()
//    {
//        return Rule::arrays(array(
//            'REQUEST' => Rule::arrays(array(
//                'object' => Rule::entity(\$this->object_rule->value())->required(),
//                'path' => Rule::regexp(\$this->path_rule->value())->required(),
//            ))
//        ));
//    }';
//php;
        if (!isset($methods['show'])){
            $methods['show'] =
<<<php
    function show(\$v = array(), \$commands, \$input)
    {
        return parent::show(\$v,\$commands, \$input);
    }
php;
        }
        return parent::classTemplate($methods, $use);
    }


    function fileTemplate()
    {
        $name = $this->name();
        if ($this->isFile() && ($file = $this->fileContent())){
            return preg_replace('/(<[a-z]+[^>]+class=("|\')([^"\']+)?)/ui', '$1 '.htmlspecialchars($name), $file['content'], 1);
        }
        return <<<html
<div class="{$name}">{$this->title->inner()->value()}</div>
html;
    }
}