<div class="BaseExplorer Editor" data-o="<?=$v['object']?>"  data-v="<?php echo $v['view_uri'];?>" data-p="BaseExplorer">
    <div class="BaseExplorer__head">
        <div class="BaseExplorer__head-left">
            <h1 class="BaseExplorer__title"><a class="BaseExplorer__title-a" href="<?=$v['proto']?>"><?=$v['title']?></a></h1>
        </div>
        <div class="BaseExplorer__head-right">
            <?=$v->FilterTool->string()?>
        </div>
    </div>
    <p class="BaseExplorer__description"><?=$v['description']?></p>

    <div class="BaseExplorer__list Editor__list">
    <?php
        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        if(!empty($list)){
            foreach ($list as $item) echo $item;
        }else{
            echo '<div class="BaseExplorer__empty">Пусто
                    <div class="BaseExplorer__empty-explain">У объекта нет подчиненных или они не соответсятвуют фильтру </div></div>';
        }
    ?>
    </div>
</div>