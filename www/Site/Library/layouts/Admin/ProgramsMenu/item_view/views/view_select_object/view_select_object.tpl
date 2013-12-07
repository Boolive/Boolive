<li class="view_select" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="SelectObjectItem">
    <div class="item" href="">
        <span class="view_all" title="<?=$v['description']?>">
            <span class="title"><?php echo $v['title'];?></span>
            <img class="icon" src="<?php echo $v['icon'];?>"/>
        </span>
        <div class="popup-left">
            <ul class="">
                <?php foreach ($v['shorts'] as $item):?>
                    <li title="<?=$item['description'];?>"><a class="object" href="<?php echo $item['uri']->uri();?>"><?php echo $item['title'];?></a></li>
                <?php endforeach; ?>
<!--                <li class="hsplit"></li>-->
                <li class="view_all link"><a href="" title="Использовать существующий объект">Ссылку...</a></li>
                <li class="view_all"><a href="" title="Новый объект из любого существующего">Другое...</a></li>
            </ul>
        </div>
    </div>
</li>