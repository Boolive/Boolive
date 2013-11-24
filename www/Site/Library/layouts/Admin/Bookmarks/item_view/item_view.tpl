<?php if ($v['show-item']->bool()):?>
<li class="<?=$v['item_class'];?>">
    <a class="item" href="/admin<?=$v['item_href']; ?>" title="<?=$v['item_descript']; ?>" data-o="<?=$v['item_href']; ?>">
        <?php if ($v['item_icon']->bool()):?><img class="icon"  src="<?=$v['item_icon']->uri();?>" alt=""><?php endif;?>
        <span class="title"><?=$v['item_text']; ?></span>
    </a>
<?php endif; ?>
<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    if ($list){
        echo '<ul>';
        foreach ($list as $item)  echo $item;
        echo '</ul>';
    }
?>
<?php if ($v['show-item']->bool()):?>
</li>
<?php endif; ?>