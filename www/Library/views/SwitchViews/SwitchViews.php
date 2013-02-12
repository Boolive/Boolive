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
        $case = $this->getCaseFor($this->_input['REQUEST']['object']);
        if ($v['object'] = $case->start($this->_commands, $this->_input_child)){
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
                //'where' => array('is', '/Library/views/SwitchCase')
            ), null);
        }
        return $this->_cases;
    }

    /**
     * Возвращает вариант отображения для указанных объектов (объекта)
     * Если передан массив объектов, то выбирается вариант, подходящий для всех объектов
     * @param array | \Boolive\data\Entity $objects Объекты для которых выбрать вариант отображения
     * @return \Boolive\data\Entity | null Вариант отображения (обычно виджет) или null
     */
    public function getCaseFor($objects)
    {
        if (!is_array($objects)) $objects = array($objects);
        $cnt_obj = count($objects);
        $cases = $this->getCases();
        for ($i_obj = 0; $i_obj < $cnt_obj; $i_obj++){
            $case = null;
            $i_case = 0;
            $cases = array_values($cases);
            $cnt_case = count($cases);
            // Перебор всех вариантов если объектов более одного.
            // Если объект один, то поиск до первого подходящего варианта
            while ($i_case < $cnt_case && (!$case || $cnt_obj != 1)){
                // Проверка варианта
                if ($cases[$i_case] instanceof \Library\views\SwitchCase\SwitchCase){
                    $uri = $cases[$i_case]->value();
                    if ($uri == 'all'){
                        $case = $cases[$i_case];
                    }else
                    {
                        $obj = $objects[$i_obj];
                        while ($obj && !$case){
                            if ($obj->id() == $uri || $obj->uri() == $uri){
                                $case = $cases[$i_case];
                            }else{
                                $obj = $obj->proto();
                            }
                        }
                    }
//                    if ($objects[$i_obj]->is($uri)){
//                        $case = $cases[$i_case];
//                    }
                }
                if (!$case){
                    unset($cases[$i_case]);
                    $i_case++;
                }else{
                    $i_case++;
                }
            }
        }
        return array_shift($cases);
    }
}