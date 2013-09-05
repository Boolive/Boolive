<li class="view_action <?php echo $v['checked']->bool()?'checked':''?>" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="ToggleActionItem">
    <a class="view-action" href="">
        <span><?php echo $v['title'];?></span>
        <img src="<?php echo $v['icon'];?>"/>
    </a>
</li>