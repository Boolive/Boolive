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
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),  // массив объектов
                            Rule::entity() // или один объект
                        )->required()
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        $case = $this->getCase($this->_commands, $this->_input_child);
        if ($case && ($v['object'] = $case->start($this->_commands, $this->_input_child))){
            $this->_input_child['previous'] = true;
            return parent::work($v);
        }
        return false;
    }

    /**
     * Выбор всех вариантов отображения
     * @return array
     */
    public function getCases()
    {
        if (!isset($this->_cases)){
            $this->_cases = $this->find(array(
                //'where' => array('is', '/Library/views/SwitchCase'),
                'comment' => 'read cases in SwitchViews'
            ));
        }
        return $this->_cases;
    }

    /**
     * Возвращает вариант отображения в соответсвии с указанными входящими данными
     * @param \Boolive\commands\Commands $commands Команды для исполнения в соответствующих им видах
     * @param mixed $input Входящие данные
     * @return \Library\views\SwitchCase\SwitchCase|null Вариант отображения (обычно виджет) или null
     */
    public function getCase($commands, $input)
    {
        $cases = $this->getCases();
        foreach ($cases as $case){
            $case = $case->linked();
            if ($case instanceof \Library\views\SwitchCase\SwitchCase && $case->canWork($commands, $input)) return $case;
        }
        return null;
    }
}