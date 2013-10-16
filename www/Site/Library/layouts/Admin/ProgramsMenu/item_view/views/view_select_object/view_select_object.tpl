<li class="view_select" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="SelectObjectItem">
    <div class="item" href="">
        <span class="view_all">
            <span class="title"><?php echo $v['title'];?></span>
            <img class="icon" src="<?php echo $v['icon'];?>"/>
        </span>
        <div class="popup-left">
            <ul class="">
                <?php foreach ($v['shorts'] as $item):?>
                    <li><a class="object" href="<?php echo $item['uri']->uri();?>"><?php echo $item['title'];?></a></li>
                <?php endforeach; ?>
                <li class="hsplit"></li>
                <li class="view_all link"><a href="">Ссылку...</a></li>
                <li class="view_all"><a href="">Показать все...</a></li>
            </ul>
        </div>
    </div>
</li>