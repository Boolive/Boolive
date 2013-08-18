<table style="<?php echo $v['style'];?>">
<?php
 if($v['header']){?>
    <thead>
    <?php
            for($i=0;$i<count($v['header']['rows']);$i++){
                echo '<tr style="'.$v['header_style'].'">';
                foreach ($v['header']['rows'][$i]['cells'] as $j) {
                    echo $j->string();
                }
                echo '</tr>';
            }
            ?>
    </thead>
    <?php }
    if($v['footer']){?>
    <tfoot>
    <?php
        for($i=0;$i<count($v['footer']['rows']);$i++){
            echo '<tr style="'.$v['footer_style'].'">';
            foreach ($v['footer']['rows'][$i]['cells'] as $j) {
                echo $j->string();
            }
            echo '</tr>';
        }
    ?>
    </tfoot>
    <?php } ?>
    <tbody>
    <?php

    for ($i = 0; $i <count($v['body']['rows']); $i++) {
        echo '<tr style="'.$v['body_style'].'">';
        foreach ($v['body']['rows'][$i]['cells'] as $j) {
            echo $j->string();
        }
        echo '</tr>';
    }
        ?>
    </tbody>
</table>

