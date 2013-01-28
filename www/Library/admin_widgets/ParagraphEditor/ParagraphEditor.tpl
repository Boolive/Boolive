<<?php
    // Тег и атрибуты
    echo $v['attrib']['tag'];
    if ($s = $v['attrib']['style']->string()) echo ' style="'.$s.'"';
?> data-plugin="ParagraphEditor" data-object="<?=$v['object']?>" data-view_uri="<?php echo $v['view_uri'];?>"><?php
    // Значение
    echo $v['attrib']['value']->string();
?></<?php
    // Закрытие тега
    echo $v['attrib']['tag'];
?>>