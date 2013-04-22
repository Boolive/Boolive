<div class="PageEditor" data-p="PageEditor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>">
    <div class="buttons">
        <a class="btn btn-success btn-disable save" href="#">Сохранить</a>
        <a class="btn cancel" href="#">Отмена</a>
    </div>
    <?php

        $list = $v['view']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item){
                echo '<div class="item">'.$item.'</div>';
            }
        }
    ?>
</div>