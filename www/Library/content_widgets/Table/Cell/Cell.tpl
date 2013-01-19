<?php
    if($v['style']->string()!=''){
        echo '<td  style="'.$v['style']->string().'">';
    }else{
        echo '<td>';
    }
    $list = $v['view'];
    foreach ($list as $item) {
        echo $item->string();
    }
    echo '</td>';
?>
