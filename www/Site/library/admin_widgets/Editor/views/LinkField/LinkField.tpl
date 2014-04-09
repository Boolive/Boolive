<?php
    $class = '';
    if($v['is_hidden']->bool()) $class .= ' Item_hidden';
    if($v['is_draft']->bool()) $class .= ' Item_draft';
//    if($v['is_link']->bool()) $class .= ' Item_link';
    if($v['is_mandatory']->bool()) $class .= ' Item_mandatory';
//    switch ($v['diff']->int()){
//        case \Boolive\data\Entity::DIFF_CHANGE:
//            $class .= ' Item_diff-change';
//            break;
//        case \Boolive\data\Entity::DIFF_ADD:
//            $class .= ' Item_diff-add';
//            break;
//        case \Boolive\data\Entity::DIFF_DELETE:
//            $class .= ' Item_diff-delete';
//            break;
//    }
    $url = ltrim($v['uri'],'/');
?>
<div class="Item LinkField<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Field">
    <div class="Item__main">
        <div class="Field__input-wrap LinkField__input-wrap">
            <?php if ($v['icon']->bool()):?>
            <img class="Item__icon" src="<?=$v['icon']?>" alt="" width="16" height="16">
            <?php endif;?>
            <label class="Item__title" for="<?=$v['id']?>"><?=$v['title']?></label>
            <didv class="LinkField__proto-choice">↝</didv>
            <div class="Item__description"><?=$v['description']?></div>

        </div>
        <div class="Item__select Field__select LinkField__select"><img width="16" height="16" src="/Site/library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    </div>

    <div class="Field__error"></div>
</div>