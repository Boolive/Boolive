<ul class="MenuSelections" data-p="MenuSelections" data-v="<?=$v['view_uri'];?>">
    <?php
    foreach($v['choices'] as $name => $ch):
        ?><li class="MenuSelections__item <?=$ch['active']->bool()?'MenuSelections__item_active':''?>" data-name="<?=$name?>"><?=$ch['title'];?></li><?php
    endforeach;?>
</ul>