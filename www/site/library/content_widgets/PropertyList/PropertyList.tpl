<div class="PropertyList">
    <h3 class="PropertyList__title"><?=$v['title']?></h3>
    <ul>
        <?php foreach ($v['list'] as $item):?>
        <li><?=$item->escape()?></li>
        <?php endforeach;?>
    </ul>
</div>