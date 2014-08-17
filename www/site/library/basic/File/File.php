<?php
/**
 * Файл
 * Объект ассоциируемый с файлом
 * @version 1.0
 */
namespace site\library\basic\File;

use boolive\data\Entity;
use boolive\values\Check;
use boolive\values\Rule;

class File extends Entity
{
    protected function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns($this->validExtentions())->required();
        return $rule;
    }

    /**
     * Шаблоны допустимых имен файлов (расширений)
     * @return array
     */
    function validExtentions()
    {
        return explode(' ', $this->extentions->inner()->value());
    }

    /**
     * Проверка допустимости имени (его расширения)
     * @param $file_name
     * @return bool
     */
    function isValidExtention($file_name)
    {
        Check::ospatterns($file_name, $error, Rule::ospatterns($this->validExtentions()));
        return !isset($error);
    }

    function ext()
    {
        return \boolive\file\File::fileExtention($this->_attribs['value']);
    }

    function mime()
    {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon', //'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        $ext = $this->ext();
        if (array_key_exists($ext, $mime_types)){
            return $mime_types[$ext];
        }else
        if (function_exists('finfo_open')){
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $this->value());
            finfo_close($finfo);
            return $mimetype;
        }else{
            return 'application/octet-stream';
        }
    }
}