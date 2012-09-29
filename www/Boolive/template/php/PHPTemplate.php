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
        ob_start();
            try{
                include($entity->getFile(true));
            }catch (\Exception $e){
//                if ($e->getCode() == 2){
//                    echo "Template file '{$entity->getFile()}' not found";
//                }else{
                    throw $e;
//                }
            }
            $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}