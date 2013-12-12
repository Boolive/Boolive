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
<div class="Item BooleanField<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="BooleanField">
    <div class="Item__main">
        <div class="Field__input-wrap BooleanField__input-wrap">
            <input class="Field__input BooleadField_input" type="checkbox" id="<?=$v['id']?>" value="1" <?=($v['value']->bool()?'checked':'')?>>
            <label class="Field__title BooleanField__title" for="<?=$v['id']?>"><?=$v['title']?></label>
            <div class="Item__description"><?=$v['description']?></div>
        </div>
        <div class="Item__select Field__select BooleanField__select"><img width="16" height="16" src="/Site/Library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    </div>

    <div class="Field__error"></div>
</div>