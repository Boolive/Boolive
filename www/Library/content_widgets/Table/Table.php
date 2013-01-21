<?php
/**
 * Виджет Таблицы
 *@author: polinа Putrolaynen
 * @date: 21.01.13
 * @version 1.0
 */
namespace Library\content_widgets\Table;

use Library\views\Widget\Widget;

class Table extends Widget
{
    public function work($v = array())
    {
        /** @var $object \Boolive\data\Entity */
        $object = $this->_input['REQUEST']['object'];
        $header = $object->find(array('where' => array(array('is', '/Library/content_samples/tables/Table/Header'))),null);
        $footer = $object->find(array('where' => array(array('is', '/Library/content_samples/tables/Table/Footer'))),null);
        $body = $object->find(array('where' => array(array('is', '/Library/content_samples/tables/Table/Body'))),null);
        if(sizeof($header)>0){
            //Стиль строк заголовка
            if($header[0]->style->isExist()){
              $v['header_style'] = $header[0]->style->getStyle();
            }
            //строки заголовка
            $header['rows'] = $header[0]->find(array('where' => array(array('is', '/Library/content_samples/tables/Row'))));
            $v['header']['rows'] = $this->getRowCells($header['rows']);
        }
        //Подвал
        if(sizeof($footer)>0){
            if($footer[0]->style->isExist()){
               $v['footer_style'] = $footer[0]->style->getStyle();
             }
            $footer['rows'] = $footer[0]->find(array('where' => array(array('is', '/Library/content_samples/tables/Row'))));
            $v['footer']['rows'] = $this->getRowCells($footer['rows']);
        }
        //Если есть в таблицы элемент body
        if(sizeof($body)>0){
            if($body[0]->style->isExist()){
               $v['body_style'] = $body[0]->style->getStyle();
            }
            $body['rows'] = $body[0]->find(array('where' => array(array('is', '/Library/content_samples/tables/Row'))));
        }else{
            //Если его нет, то просто выводим все строки
            $body['rows'] = $object->find(array('where' => array(array('is', '/Library/content_samples/tables/Row'))));
        }
        $v['body']['rows'] = $this->getRowCells($body['rows']);
        //Стиль таблицы
        $v['style'] = $object->style->getStyle();
        return parent::work($v);
    }

    /**
     * Возвращает сформированные ячейки по строкам
     * @param $rows объект содержащий строки
     */

    private function getRowCells($rows){
        $i=0;
        foreach($rows as $row){
            if($row->is('/Library/content_samples/tables/Row')){
                $rows[$i]['cells'] = $row->find(array('where' => array(array('is', '/Library/content_samples/tables/Cell'))));
                foreach($rows[$i]['cells'] as $cell){
                    $this->_input_child['REQUEST']['object'] = $cell;
                    //Запускаем подчиненный виджет - виджет ячейки.
                    $rows[$i]['cells'][] = $this->startChild('Cell');
                }
            }
            $i++;
        }
        return $rows;
    }


}