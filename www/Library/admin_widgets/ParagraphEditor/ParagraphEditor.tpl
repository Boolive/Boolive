<<?php
    // Тег и атрибуты
    echo $v['attrib']['tag'];
    if ($s = $v['attrib']['style']->string()) echo ' style="'.$s.'"';
?> data-p="ParagraphEditor" data-o="<?=$v['object']?>" data-v="<?php echo $v['view_uri'];?>"><?php
    // Значение
//    if (!($text = $v['attrib']['value']->escape())){
//        $text = '&#8203;';
//    }
//    echo $text;
    echo $v['attrib']['value']->escape();
?></<?php
    // Закрытие тега
    echo $v['attrib']['tag'];
?>>