<li class="view-program" data-v="<?php echo $v['view_uri'];?>" data-p="ProgramItem">
    <span class="item" data-program="<?php echo $v['program']?>" title="<?=$v['description']?>">
        <span class="title"><?php echo $v['title'];?></span>
        <?php if ($v['icon']->bool()):?><img class="icon" src="<?php echo $v['icon'];?>"/><?php endif;?>
    </span>
</li>