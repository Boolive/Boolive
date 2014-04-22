<li class="view_select" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="">
    <div class="item" href="">
        <span class="">
            <span class="title"><?php echo $v['title'];?></span>
            <img class="icon" src="<?php echo $v['icon'];?>"/>
        </span>
        <div class="popup-left">
            <ul class="">
                <?php foreach ($v['list'] as $key => $item):?>
                    <li><a href="<?php echo $key;?>"><?php echo $item['title'];?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</li>