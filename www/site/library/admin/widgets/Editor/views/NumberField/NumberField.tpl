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
?>
<div class="Item Field NumberField<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Field">
    <label class="Field__title" for="<?=$v['id']?>"><?=$v['title']?></label>
    <div class="inpt">
        <div class="Field__input-wrap">
            <input class="Field__input NumberField__input" type="text" id="<?=$v['id']?>" value="<?php echo $v['value'];?>">
        </div>
        <div class="NumberField__unit"><?=$v['unit']?></div>
        <div class="Item__select Field__select"><img width="16" height="16" src="/site/library/admin/widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    </div>
    <div class="Item__description Field__description"><?=$v['description']?></div>
    <div class="Field__error"></div>
</div>