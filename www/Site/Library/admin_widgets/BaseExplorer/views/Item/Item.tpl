<?php
    $class = '';
    if($v['is_hidden']->bool()) $class .= ' Item_hidden';
    if($v['is_draft']->bool()) $class .= ' Item_draft';
    if($v['is_link']->bool()) $class .= ' Item_link';
    if($v['is_mandatory']->bool()) $class .= ' Item_mandatory';
    switch ($v['diff']->int()){
        case \Boolive\data\Entity::DIFF_CHANGE:
            $class .= ' Item_diff-change';
            break;
        case \Boolive\data\Entity::DIFF_ADD:
            $class .= ' Item_diff-add';
            break;
        case \Boolive\data\Entity::DIFF_DELETE:
            $class .= ' Item_diff-delete';
            break;
    }
?>
<div class="Item<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Item">
    <div class="Item__main">
        <a class="Item__title" href="<?=ltrim($v['link'],'/')?>"><?=$v['title']?></a>
        <div class="Item__description"><?=$v['description']?></div>
        <a class="Item__prop" title="Свойства ссылки" href="<?php echo ltrim($v['uri'],'/');?>"><img src="/Site/Library/admin_widgets/BaseExplorer/views/Item/res/style/img/enter.png" width="16" height="16" alt=""/></a>
        <div class="Item__value <?=($v['is_file']->bool()?'Item__file':'')?><?php echo $v['is_default_value']->bool()?' Item__default-value':'';?>" title="<?=$v['value_full']?>"><?=$v['value_short']?></div>
    </div>
    <div class="Item__select"><img width="16" height="16" src="/Site/Library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
</div>