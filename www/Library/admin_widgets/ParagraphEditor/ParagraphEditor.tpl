<<?php
    // Тег и атрибуты
    echo $v['attrib']['tag'];
    if ($s = $v['attrib']['style']->string()) echo ' style="'.$s.'"';
?> data-p="ParagraphEditor" data-o="<?=$v['object']?>" data-v="<?php echo $v['view_uri'];?>"><?php
    // Значение
    echo $v['attrib']['value']->string();
?></<?php
    // Закрытие тега
    echo $v['attrib']['tag'];
?>>