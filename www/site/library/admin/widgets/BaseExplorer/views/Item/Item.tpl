<?php
    $class = '';
    if($v['is_hidden']->bool()) $class .= ' Item_hidden';
    if($v['is_draft']->bool()) $class .= ' Item_draft';
    if($v['is_link']->bool()) $class .= ' Item_link';
    if($v['is_mandatory']->bool()) $class .= ' Item_mandatory';
//    switch ($v['diff']->int()){
//        case \boolive\data\Entity::DIFF_CHANGE:
//            $class .= ' Item_diff-change';
//            break;
//        case \boolive\data\Entity::DIFF_ADD:
//            $class .= ' Item_diff-add';
//            break;
//        case \boolive\data\Entity::DIFF_DELETE:
//            $class .= ' Item_diff-delete';
//            break;
//    }
    $url = ltrim($v['uri'],'/');
?>
<div class="Item<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Item" title="<?=$v['uri']?>">
    <div class="Item__main Item__select-area">
        <?php if ($v['icon']->bool()):?>
        <img class="Item__icon" src="<?=$v['icon']?>" alt="" width="16" height="16">
        <?php endif;?>
        <a class="Item__title" href="<?=ltrim($v['link'],'/')?>"><?=$v['title']?></a>
        <div class="Item__description"><?=$v['description']?></div>

        <a class="Item__link" title="Структура ссылки" href="<?=$url?>"><img src="/site/library/admin/widgets/BaseExplorer/views/Item/res/style/img/enter.png" width="16" height="16" alt=""/></a>
<!--        <a class="Item__prop" title="Изменение свойств" href="--><?//=$url.'&select=property';?><!--">Свойства</a>-->
        <a class="Item__value <?=($v['is_file']->bool()?'Item__file':'')?><?=$v['is_default_value']->bool()?' Item__default-value':'';?>" title="<?=$v['value_full']?>" href="<?=$url.'&select=file';?>"><?=$v['value_short']?></a>

    </div>
    <div class="Item__select"><img width="16" height="16" src="/site/library/admin/widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
</div>