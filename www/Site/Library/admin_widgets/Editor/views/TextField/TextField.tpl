<?php
    $class = '';
    if($v['is_hidden']->bool()) $class .= ' Item_hidden';
    if($v['is_draft']->bool()) $class .= ' Item_draft';
    if($v['is_link']->bool()) $class .= ' Item_link';
    if($v['is_mandatory']->bool()) $class .= ' Item_mandatory';
?>
<div class="Item Field TextField<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Field">
    <label class="Item__title Field__title" for="<?=$v['object']?>"><?=$v['title']?></label>
    <div class="inpt">
        <div class="Field__input-wrap TextField__input-wrap">
            <textarea class="Field__input TextField__input" id="<?=$v['object']?>"><?=$v['value']?></textarea>
        </div>
        <div class="Item__select Field__select"><img width="16" height="16" src="/Site/Library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    </div>
    <div class="Item__description Field__description"><?=$v['description']?></div>
    <div class="Field__error"></div>
</div>