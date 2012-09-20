<?php
/**
 * Переключатель вариантов отображения (исполнения)
 *
 * Содержит варианты, значения которых - условие исполнения.
 * Условие исполнение - это uri отображаемого объекта или uri прототипов отображаемого объекта.
 * Может оказаться несколько вариантов с выполняемым условием, но выбирается только первый вариант.
 * В качестве вараинтов предполагается использовать объекты SwithCase.
 *
 * @version 1.0
 */
namespace Library\views\SwitchViews;

use Boolive\values\Rule,
    Boolive\input\Input,
    Library\views\Widget\Widget;

class SwitchViews extends Widget
{
    protected $_cases;

    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->default(null)->required(),
                    )
                )
            )
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild(array_replace_recursive($input, $this->_input));
    }

    public function work($v = array())
    {
        // Все варианты отображений для последующего поиска нужного
        $options = $this->getCases();
        $obj = $this->_input['REQUEST']['object'];
        $v['object'] = null;
        $uri = $obj['uri'];
        $do = true;
        // Поиск варианта отображения для объекта
        while ($do){
            if (isset($options[$uri])){
                $v['object'] = $options[$uri]->start($this->_commands, $this->_input_child);
                if ($v['object'] != null){
                    $this->_input_child['previous'] = true;
                }
            }
            // Если виджеты не исполнялись, тогда ищем соответсвие по прототипу
            if ($v['object'] == null){
                if ($uri == 'all'){
                    $do = false;
                }else
                if (isset($obj) && ($obj = $obj->proto())){
                    $uri = $obj['uri'];
                }else{
                    $uri = 'all';
                }
            }else{
                $do = false;//чтоб остановить цикл
            }
        }
        return parent::work($v);
    }

    protected function getCases(){
        if (!isset($this->_cases)){
            $this->_cases = $this->findAll(array('where'=>'is_history=0 and is_delete=0'), false, 'value');
        }
        return $this->_cases;
    }
}