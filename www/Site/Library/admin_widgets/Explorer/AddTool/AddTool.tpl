<div class="AddTool left" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['program_uri'];?>" data-p="AddTool">
    <img class="icon" src="<?=$v['icon']?>">
    <span class="label">Добавить: </span>
    <?php foreach ($v['shorts'] as $item):?>
        <a class="item object" href="<?=$item['uri']->uri();?>" title="<?=$item['description'];?>"><?=$item['title'];?></a>
    <?php endforeach; ?>
    <a class="item more link" href="" title="Использовать существующий объект">Cсылку</a>
    <a class="item more" href="" title="Новый объект из любого существующего">Другое...</a>

</div>