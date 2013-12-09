<li class="view_select" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="SelectObjectItem">
    <div class="item" href="">
        <span class="view_all" title="<?=$v['description']?>" data-open="<?=$v['open_all']?>">
            <span class="title"><?php echo $v['title'];?></span>
            <img class="icon" src="<?php echo $v['icon'];?>"/>
        </span>
        <div class="popup-left">
            <ul class="">
                <?php foreach ($v['shorts'] as $item):?>
                    <li title="<?=$item['description'];?>"><a class="object" href="<?php echo $item['uri']->uri();?>"><?php echo $item['title'];?></a></li>
                <?php endforeach; ?>
                <?php if ($v['shorts']->bool()):?>
                <li class="hsplit"></li>
                <?php endif;?>
                <li class="view_all link" data-open="<?=$v['open_link']?>"><a href="" title="Использовать существующий объект">Ссылку</a></li>
                <?php if ($v['open_proto']->bool()):?>
                <li class="view_all addons" data-open="<?=$v['open_proto']?>"><a href="" title="Свойства прототипа">Дополнения</a></li>
                <?php endif;?>
                <li class="view_all" data-open="<?=$v['open_all']?>"><a href="" title="Новый объект из любого существующего">Библиотека</a></li>
            </ul>
        </div>
    </div>
</li>