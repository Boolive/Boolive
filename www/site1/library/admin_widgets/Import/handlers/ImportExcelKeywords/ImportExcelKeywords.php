<?php
/**
 * Импорт клювых слов из Excel файла
 * Обрабатывает info файлы для любых объектов
 * @version 1.0
 */
namespace Site\library\admin_widgets\Import\handlers\ImportExcelKeywords;

use Boolive\data\Data;
use Boolive\errors\Error;
use Boolive\file\File;
use Boolive\functions\F;
use Boolive\values\Check;
use Boolive\values\Rule;
use Site\library\admin_widgets\Import\handlers\ImportFile\ImportFile;

class ImportExcelKeywords extends ImportFile
{
    function usageCheck($params)
    {
        Check::filter($params, Rule::arrays(array(
            'parent' => Rule::entity(),
            'file' => Rule::arrays(array(
                'name'	=> Rule::lowercase()->ospatterns('*.xls','*.xlsx')->ignore('lowercase')->required(), // Имя файла, из которого будет взято расширение
            ))
        )), $error);
        return !isset($error);
    }

    function work()
    {
        if ($this->file->isFile()){
            $file = $this->file->file(null, true);
            $info = File::fileInfo($file);
            if (!in_array($info['ext'], array('xls','xlsx'))) throw new Error('Неподдерживаемое расширение файла','ext');
            $excel = \PHPExcel_IOFactory::load($file);
            $excel_sheet = $excel->getActiveSheet();
            $row_cnt = min(500, $excel_sheet->getHighestRow());
            $parent = $this->where->linked();
            $proto = Data::read('/library/content_samples/Keyword');
            if ($excel_sheet->getCell('A1')->getValue() != 'Фраза' || $excel_sheet->getCell('B1')->getValue() != 'Частотность'){
                throw new Error('Неверная струткра файла','structure');
            }
            for ($row = 2; $row <= $row_cnt; ++$row){
                $key_title = $excel_sheet->getCell('A'.$row)->getValue();
                $freq = $excel_sheet->getCell('B'.$row)->getValue();
                $key_name = mb_strtolower(preg_replace('/\s/ui','_', F::translit($key_title)));
                $key = $parent->{$key_name};
                // Создание слова в общей коллекции ключевых слов
                if (!$key->isExist()){
                    $key = $proto->birth($parent, false);
                    $key->name($key_name);
                    $key->value(0);
                    $key->title->value($key_title);
                    $key->freq->value($freq);
                    $key->save();
                }else
                if ($key->freq->value()!=$freq){
                    $key->freq->value($freq);
                    $key->save();
                }
                $this->percent->value($row * (100 / $row_cnt));
                $this->percent->save(false, false);
            }
        }
    }
}