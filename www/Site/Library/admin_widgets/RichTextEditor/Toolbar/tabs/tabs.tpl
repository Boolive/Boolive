<ul class="tabs">
    <?php foreach ($v['items']->arrays(\Boolive\values\Rule::any()) as $item) { ?>
        <li data-bar="<?php echo $item['bar']?>">
            <a href="#" title="<?php echo $item['title']?>" style="background-image: url(<?php echo $item['icon'];?>);"></a>
        </li>
    <?php } ?>
</ul>