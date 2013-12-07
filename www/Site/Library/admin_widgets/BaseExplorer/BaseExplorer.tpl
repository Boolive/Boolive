<div class="BaseExplorer" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>" data-p="BaseExplorer">
    <div class="BaseExplorer__head">
        <div class="BaseExplorer__head-left">
            <h1 class="title"><?=$v['title']?></h1>
        </div>
        <div class="BaseExplorer__head-right">
            <?=$v->FilterTool->string()?>
        </div>
    </div>
    <p class="BaseExplorer__description"><?=$v['description']?></p>

    <div class="BaseExplorer__list">
    <?php
        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item) echo $item;
        }else{
            echo '<div class="BaseExplorer__empty">'.$v['empty'].'<div class="BaseExplorer__empty-explain">'.$v['empty_description'].'</div></div>';
        }
    ?>
    </div>
</div>