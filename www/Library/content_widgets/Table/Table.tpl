<table border="1">
<?php
 if($v['rowheadercount']->int()>0){?>
    <thead>
    <?php
            for($i=0;$i<$v['rowheadercount']->int();$i++){
                echo '<tr>';
                foreach ($v['rows'][$i]['header'] as $j) {
                    echo $j->string();
                }
                echo '</tr>';
            }
            ?>
    </thead>
    <?php }
     if($v['rowfootercount']->int()>0){?>
    <tfoot>
    <?php
        for($i=0;$i<$v['rowfootercount']->int();$i++){
            echo '<tr>';
            foreach ($v['rows'][$i]['footer'] as $j) {
                echo $j->string();
            }
            echo '</tr>';
        }
    ?>
    </tfoot>
    <?php } ?>
    <tbody>
    <?php

    for ($i = 0; $i < $v['rowcount']->int(); $i++) {
        echo '<tr>';
        foreach ($v['cells'] as $j) {
            echo $j->string();
        }
        echo '</tr>';
    }
        ?>
    </tbody>
</table>

