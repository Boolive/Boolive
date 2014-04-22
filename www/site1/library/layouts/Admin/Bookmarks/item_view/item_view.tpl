<?php if ($v['show-item']->bool()):?>
<li class="Bookmarks__item <?=$v['item_class'];?>" data-l="<?=$v['item_href']?>" data-o="<?=$v['item_key']?>">
    <a class="Bookmarks__item-link" href="/admin<?=$v['item_href']; ?>" title="<?=$v['item_descript']; ?>">
        <?php if ($v['item_icon']->bool()):?><img class="Bookmarks__item-icon"  src="<?=$v['item_icon']->uri();?>" alt=""><?php endif;?>
        <span class="Bookmarks__item-title"><?=$v['item_text']; ?></span>
        <div class="Bookmarks__item-remove" title="Удалить закладку"><img src="/Site/library/layouts/Admin/Bookmarks/res/style/remove.png" width="16" height="16"/></div>
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