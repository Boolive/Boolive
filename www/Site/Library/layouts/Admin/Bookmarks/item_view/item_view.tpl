<?php if ($v['show-item']->bool()):?>
<li class="Bookmarks__item <?=$v['item_class'];?>">
    <a class="Bookmarks__item-link" href="/admin<?=$v['item_href']; ?>" title="<?=$v['item_descript']; ?>" data-o="<?=$v['item_href']; ?>">
        <?php if ($v['item_icon']->bool()):?><img class="Bookmarks__item-icon"  src="<?=$v['item_icon']->uri();?>" alt=""><?php endif;?>
        <span class="Bookmarks__item-title"><?=$v['item_text']; ?></span>
        <span class="Bookmarks__item-remove" title="Удалить закладку" data-o="<?=$v['item_key']?>">✖</span>
    </a>
<?php endif; ?>
<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    if ($list){
        echo '<ul class="Bookmarks__items-list">';
        foreach ($list as $item)  echo $item;
        echo '</ul>';
    }
?>
<?php if ($v['show-item']->bool()):?>
</li>
<?php endif; ?>