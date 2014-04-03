<?php
/**
 * Импорт компаний из Excel файла
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\Import\handlers\ImportExcelCompanies;

use Boolive\data\Data;
use Boolive\errors\Error;
use Boolive\file\File;
use Boolive\functions\F;
use Boolive\values\Check;
use Boolive\values\Rule;
use Site\library\admin_widgets\Import\handlers\ImportExcelKeywords\ImportExcelKeywords;

class ImportExcelCompanies extends ImportExcelKeywords
{
    function usageCheck($params)
    {
        Check::filter($params, Rule::arrays(array(
            'parent' => Rule::entity(array('in','/contents/companies')),
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
            $row_cnt = $excel_sheet->getHighestRow();
            $parent = $this->where->linked();
            $proto = Data::read('/library/portal/company');
            $phone_proto = Data::read('/library/basic/Phone');
            $email_proto = Data::read('/library/basic/Email');
            $cat_proto = Data::read('/library/portal/category');
            $cat_list = Data::read('/contents/categories');


            if ($excel_sheet->getCell('A1')->getValue() != 'Название' ||
                $excel_sheet->getCell('C1')->getValue() != 'Телефон' ||
                $excel_sheet->getCell('E1')->getValue() != 'Email' ||
                $excel_sheet->getCell('G1')->getValue() != 'Сайт' ||
                $excel_sheet->getCell('I1')->getValue() != 'Адрес' ||
                $excel_sheet->getCell('K1')->getValue() != 'Рубрики'
            ){
                throw new \Boolive\errors\Error('Неверная струткра файла','structure');
            }
            $percent_update = time();
            for ($row = 2; $row <= $row_cnt; ++$row){
                $title = $excel_sheet->getCell('A'.$row)->getValue();
                $name = F::nameFilter($title);
                $phones = explode(',',$excel_sheet->getCell('C'.$row)->getValue());
                $emails = explode(',',$excel_sheet->getCell('E'.$row)->getValue());
                $site = explode(',',$excel_sheet->getCell('G'.$row)->getValue());
                $site = $site? $site[0]:'';
                $address = $excel_sheet->getCell('I'.$row)->getValue();
                $categories = explode(',',$excel_sheet->getCell('K'.$row)->getValue());

                $company = $parent->{$name};
                if (!$company->isExist()){
                    $company = $proto->birth($parent, false);
                    $company->name($name);
                    $company->save();
                }
                //Название
                $company->title->value($title);
                $company->title->save(false);
                //Сайт
                if (!empty($site)){
                    $company->site->value($site);
                    try{
                        $company->site->save(false);
                    }catch (Error $e){

                    }
                }
                //Адрес
                if (!empty($address)){
                    $company->address->value($address);
                    try{
                        $company->address->save(false);
                    }catch (Error $e){

                    }
                }
                //Телефоны
                $current_phones_tmp = $company->phones->find(array(
                    'where' => array(
                        array('attr','is_property','=',0)
                    ),
                    'key' => 'value',
                    'cache' => 0
                ));
                $current_phones = array();
                foreach ($current_phones_tmp as $pkey => $pvalue){
                    $current_phones[preg_replace('/[^0-9]/', '', $pkey)] = $pvalue;
                }
                foreach ($phones as $phone){
                    $phone_key = preg_replace('/[^0-9]/', '', $phone);
                    if (!empty($phone_key) && !isset($current_phones[$phone_key])){
                        $current_phones[$phone_key] = $phone_proto->birth($company->phones, false);
                        $current_phones[$phone_key]->name('phone');
                        $current_phones[$phone_key]->value($phone);
                        try{
                            $current_phones[$phone_key]->save(false);
                        }catch (Error $e){

                        }
                    }
                }
                //Emails
                $current_emails = $company->emails->find(array(
                    'where' => array(
                        array('attr','value','in',$emails),
                        array('attr','is_property','=',0)
                    ),
                    'key' => 'value',
                    'cache' => 0
                ));
                foreach ($emails as $email){
                    $email = trim($email);
                    if (!empty($email) && !isset($current_emails[$email])){
                        $current_emails[$email] = $email_proto->birth($company->emails, false);
                        $current_emails[$email]->name('email');
                        $current_emails[$email]->value($email);
                        try{
                            $current_emails[$email]->save(false);
                        }catch (Error $e){

                        }
                    }
                }
                //Рубрики
                $current_cats = $company->categories->find(array(
                    'where' => array(
                        array('attr','is_property','=',0),
                        array('attr','is_link','>',0)
                    ),
                    'key' => 'name',
                    'cache' => 0
                ));
                foreach ($categories as $cat_title){
                    $cat_name = F::nameFilter($cat_title);
                    if (!empty($cat_name) && !isset($current_cats[$cat_name])){
                        // Поиск категории в общем списке
                        $cat = $cat_list->{$cat_name};
                        if (!$cat->isExist()){
                            $cat = $cat_proto->birth($cat_list, false);
                            $cat->name($cat_name);
                            $cat->value(0);
                            $cat->title->value($cat_title);
                            $cat->save();
                        }
                        $current_cats[$cat_name] = $cat->birth($company->categories, false);
                        $current_cats[$cat_name]->name($cat_name);
                        $current_cats[$cat_name]->isLink(true);
                        try{
                            $current_cats[$cat_name]->save(false);
                        }catch (Error $e){

                        }
                    }
                }
                if (time()-$percent_update > 3){
                    $this->percent->value($row * (100 / $row_cnt));
                    $this->percent->save(false, false);
                }
            }
        }
    }
}