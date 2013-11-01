<?php
/**
 * Шаблонизация с помощью PHP
 * @link http://boolive.ru/createcms/making-page
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\template\php;

class PHPTemplate
{
    /**
     * Создание текста из шаблона
     * В шаблон вставляются переданные значения
     * При обработки шаблона могут довыбираться значения из $entity и создаваться команды в $commands
     * @param \Boolive\data\Entity $entity
     * @param array $v
     * @throws \Exception
     * @return string
     */
    public function render($entity, $v)
    {
        // Массив $v достпуен в php-файле шаблона, подключамом ниже
        $v = new PHPTemplateValues($v, null, $entity);
        try{
            ob_start();
            if ($entity->isFile()){
                include($entity->file(null, true));
            }else{
                echo $entity->value();
            }
            $result = ob_get_contents();
            ob_end_clean();
        }catch (\Exception $e){
            ob_end_clean();
//          if ($e->getCode() == 2){
//              echo "Template file '{$entity->file()}' not found";
//          }else{
                throw $e;
//          }
        }
        return $result;
    }
}