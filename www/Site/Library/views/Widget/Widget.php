<?php
/**
 * Виджет
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\views\Widget;

use Boolive\template\Template,
    Library\views\View\View,
    Boolive\values\Rule;

class Widget extends View
{

    /**
     * Инициализация Виджета
     */
    protected function init()
    {
        //if (!$this->isLink()){
            $this->find(array('select'=>array('tree'), 'depth'=>array(1,'max'), 'return'=>false, 'comment' => 'read tree of widgets', 'order' => array('order', 'asc')), false);
        //}
    }

    /**
     * Инициализация входящих данных
     * Если нету своего объекта (модели), то берется объект из входящих данных
     * @param $input
     * @return mixed|void
     */
    protected function initInput($input)
    {
        if ($this->object->isExist() && !$this->object->isDelete() && !$this->object->isHistory()){
            $input['REQUEST']['object'] = $this->object;
        }
        parent::initInput($input);
    }

    /**
     * Возвращает правило на входящие данные
     * @return null|\Boolive\values\Rule
     */
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        $this->startChild('res');
        $v['view_uri'] = $this->id();
        return Template::render($this, $v);
    }

    public function exportedProperties()
    {
        $names = parent::exportedProperties();
        $names[] = 'res';
        $names[] = 'object';
        return $names;
    }
}