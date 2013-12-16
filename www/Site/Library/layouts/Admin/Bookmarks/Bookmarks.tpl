<div class="Bookmarks" data-p="Bookmarks" data-v="<?php echo $v['view_uri'];?>" data-o="<?=$v['config']?>">
    <div class="Bookmarks__title-wrap">
        <a href="<?=ltrim($v['config'],'/')?>" data-o="<?=$v['config']?>" class="Bookmarks__config"><img src="/Site/Library/layouts/Admin/Bookmarks/res/style/attr.png" width="16" height="16" alt=""/></a>
        <span class="Bookmarks__title common_background"><?=$v['title']?></span>
    </div>
    <?=$v['item_view']->string();?>
    <div class="Bookmarks__toolitem" title="Добавть в закладки"><img class="Bookmarks__add" src="/Site/Library/layouts/Admin/Bookmarks/res/style/plus.png" width="16" height="16" alt=""/></div>
</div>