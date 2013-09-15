<div class="PageEditor" data-p="PageEditor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>">
    <div class="main">
    <?php

        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item){
                echo '<div class="item">'.$item.'</div>';
            }
        }
    ?>
    </div>
</div>