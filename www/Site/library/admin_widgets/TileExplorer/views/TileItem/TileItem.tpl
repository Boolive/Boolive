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
    $url = ltrim($v['uri'],'/');
    // Разделение длинных заголовков на два и округление ширены плитки
    $title_big = $v['title']->escape();
    $l = mb_strlen($title_big);
    $pos = mb_strpos($title_big, ' ', min(8, $l));
    if ($l>15 && ($pos > 15 || !$pos)){
        $pos = mb_strpos($title_big, ' ');
    }
    if ($pos){
        $title_min = mb_substr($title_big, $pos);
        $title_big = mb_substr($title_big, 0, $pos);
    }else{
        $title_min = '';
    }
    $icon_width = $v['icon']->bool()?40:0;
    $width_big = max(200, round((mb_strlen($title_big)*19+$icon_width)/25)*25);
    $width_min = max(200, round((mb_strlen($title_min)*11)/25)*25);
    $width = max($width_big, $width_min);
?><div class="Item TileItem <?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="Item">
    <div class="Item__main TileItem__main Item__select-area" style="width:<?=$width?>px; <?=$v['icon-style']?>">

        <div class="TileItem__title-wrap">
            <?php if ($v['icon']->bool()):?>
        <img class="Item__icon TileItem__icon" src="<?=$v['icon']?>" alt="" width="32" height="32">
        <?php endif;?>
            <a class="Item__title TileItem__title" href="<?=ltrim($v['link'],'/')?>"><?=$title_big?></a></div>
        <?php if (!empty($title_min)) :?><div class="TileItem__title-min"><?=$title_min?></div><?php endif; ?>
        <div class="Item__description TileItem__description"><?=$v['description']?></div>
        <div class="TileItem__value_wrap">
            <a class="Item__value <?=($v['is_file']->bool()?'Item__file':'')?><?php echo $v['is_default_value']->bool()?' Item__default-value':'';?> TileItem__value" title="<?=$v['value_full']?>" href="<?=$url.'&select=file';?>"><?=$v['value_short']?></a>
        </div>
    </div>
    <div class="Item__select TileItem__select"><img width="16" height="16" src="/Site/library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    <a class="Item__link TileItem__link" title="Структура ссылки" href="<?=$url?>"><img src="/Site/library/admin_widgets/BaseExplorer/views/Item/res/style/img/enter.png" width="16" height="16" alt=""/></a>
    <a class="Item__prop TileItem__prop" title="Изменение свойств" href="<?=$url.'&select=property';?>">Свойства</a>
</div>