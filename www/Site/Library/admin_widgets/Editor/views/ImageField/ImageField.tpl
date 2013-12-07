<?php
    $class = '';
    if($v['is_hidden']->bool()) $class .= ' Item_hidden';
    if($v['is_draft']->bool()) $class .= ' Item_draft';
    if($v['is_link']->bool()) $class .= ' Item_link';
    if($v['is_mandatory']->bool()) $class .= ' Item_mandatory';
?>
<div class="Item Field ImageField<?=$class?>" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-nl="<?=$v['newlink']?>" data-p="ImageField">
    <label class="Item__title Field__title" for="<?=$v['object']?>"><?=$v['title']?></label>
    <div class="inpt">
        <div class="ImageField__wrap">
            <img class="ImageField__image" id="<?=$v['object']?>" src="<?php echo $v['file'];?>" alt=""/>
            <form class="ImageField__form" action="" enctype="multipart/form-data" type="POST">
                <div class="btn-min ImageField__fileupload" title="Выбрать файл с компьютера">Выбрать файл
                    <input type="file" size="1" name="entity[file]"/>
                </div>
                <a class="btn-min ImageField__reset" title="Отменить загрузку файла с компьютера">Отменить</a>
                <span class="ImageField__filename"></span>
            </form>
        </div>
        <div class="Item__select Field__select ImageField__select"><img width="16" height="16" src="/Site/Library/admin_widgets/BaseExplorer/views/Item/res/style/img/touch.png" alt=""/></div>
    </div>
    <div class="Item__description Field__description"><?=$v['description']?></div>
    <div class="Field__error"></div>
</div>