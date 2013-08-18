<td <?php echo $v['style']->string()!=''?'style="'.$v['style']->string().'"':''?>
        <?php echo $v['colspan']->int()>1? 'colspan="'.$v['colspan']->int().'"':''?>
        <?php echo $v['rowspan']->int()>1? 'rowspan="'.$v['rowspan']->int().'"':''?>>
<?php

    $list = $v['view'];
    foreach ($list as $item) {
        echo $item->string();
    }
    echo '</td>';
?>
