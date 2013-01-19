<?php
/**
 * Виджет Таблицы
 *
 * @version 1.0
 */
namespace Library\content_widgets\Table;

use Library\views\Widget\Widget;

class Table extends Widget
{
    public function work($v = array())
    {
        $v['rowheadercount'] = 0;
        $v['rowfootercount'] = 0;
        $v['rowcount'] = 0;
        /** @var $object \Boolive\data\Entity */
        $object = $this->_input['REQUEST']['object'];
        $v['rows'] = $object->find(array('where' => array(array('is', '/Library/content_samples/tables/Row'))));

        $i = 0;
        foreach ($v['rows'] as $row) {
            //Определим количество строк в каждой секции таблицы
           if ($row->header->isExist()) {
                $v['rowheadercount'] = $v['rowheadercount'] + 1;
                $v['rows'][$i]['headercells'] = $row->find(array('where' => array(array('is', '/Library/content_samples/tables/Cell'))));
                //сформируем ячейки для  секции thead
                foreach ($v['rows'][$i]['headercells'] as $cell) {
                    $this->_input_child['REQUEST']['object'] = $cell;
                    //запуск виджета ячейки
                    $v['rows'][$i]['header'][] = $this->startChild('Cell');
                }
            }elseif ($row->footer->isExist()) {
                $v['rowfootercount'] = $v['rowfootercount'] + 1;
                $v['rows'][$i]['footercells'] = $row->find(array('where' => array(array('is', '/Library/content_samples/tables/Cell'))));
                //сформируем ячейки для  секции tfoot
                foreach ($v['rows'][$i]['footercells'] as $cell) {
                    $this->_input_child['REQUEST']['object'] = $cell;
                    //запуск виджета ячейки
                    $v['rows'][$i]['footer'][] = $this->startChild('Cell');
                }
            }else{
                $v['rowcount'] = $v['rowcount'] + 1;
                $v['rows'][$i]['cells'] = $row->find(array('where' => array(array('is', '/Library/content_samples/tables/Cell'))));

                //сформируем ячейки для  секции tbody
                foreach ($v['rows'][$i]['cells'] as $cell) {
                    $this->_input_child['REQUEST']['object'] = $cell;
                    //запуск виджета ячейки
                    $v['cells'][] = $this->startChild('Cell');

                }
            }
            $i++;
        }
        return parent::work($v);
    }
}