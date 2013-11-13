<?php
/**
 * CKEditor File Uploader
 * Загрузчик файлов для WYSIWYG редактора CKEditor 4
 * @version 1.0
 */
namespace Library\views\CKEditorUploader;

use Boolive\data\Data,
    Boolive\errors\Error,
    Library\views\View\View,
    Boolive\values\Rule;

class CKEditorUploader extends View
{
    /**
     * Правило на входящие данные - условие работы restful
     */
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'CKEditor' => Rule::string()->required(), // uri текста в который добавить объект-файл
                'CKEditorFuncNum' => Rule::int()->required(),
                'proto' => Rule::ospatterns('Image', 'Flash')->required()
            )),
            'FILES' => Rule::arrays(array(
                'upload' => Rule::arrays(Rule::string())->required() // файл, загружаемый в объект
            )),
            'previous' => Rule::not(true)
        ));
    }

    function work()
    {
        $callback = $this->_input['REQUEST']['CKEditorFuncNum'];
        $file_url = '';
        $error = '';
        $text = Data::read($this->_input['REQUEST']['CKEditor']);
        $proto = $this->{$this->_input['REQUEST']['proto']}->linked();
        try{
            if ($text->isExist() && $proto->isExist()){
                $file = $proto->birth($text, false);
                $file->file($this->_input['FILES']['upload']);
                $file->save();
                $file_url = $file->file(null, false);
            }else{
                $error = 'Пожалуйста, сохраните текст перед загрузкой в него картинки';
            }
        }catch (Error $e){
            $error = $e->getUserMessage();
        }catch(\Exception $e){
            $error = $e->getMessage();
        }
        return "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction(".$callback.",  \"".$file_url."\", \"".$error."\" );</script>";
    }
}