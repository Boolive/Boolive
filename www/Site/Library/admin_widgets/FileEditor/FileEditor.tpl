<div class="FileEditor" data-v="<?=$v['view_uri'];?>" data-o="<?=$v['data-o'];?>" data-p="FileEditor" data-mode="<?=$v['file-ext']?>" data-is_default="<?=$v['is_default_value']->int()?>">
    <div style="display: none;" class="FileEditor__contents">
        <div id="default"><?=$v['content']['default']?></div>
        <div id="self"><?=$v['content']['self']?></div>
    </div>
    <div class="FileEditor__head">
        <div class="FileEditor__head-left">
            <h1 class="FileEditor__title" title=""><span class="FileEditor__title-name">Файл объекта</span> <?=$v['title']?></h1>
            <p class="FileEditor__description">Значение объекта в виде файла</p>
            <?php if (!$v['file']->bool()): ?>
            <p class="FileEditor__description FileEditor__warning">У объекта нет файлового значения!</p>
            <?php endif;?>
        </div>
        <div class="FileEditor__head-right FileEditor__default-menu">
            <div class="btn btn-min left FileEditor__btn_default <?=$v['is_default_value']->bool()?'FileEditor__btn_selected':''?>">По умолчанию</div>
            <div class="btn btn-min left FileEditor__btn_self <?=!$v['is_default_value']->bool()?'FileEditor__btn_selected':''?>">Свой</div>
        </div>
    </div>
    <div class="FileEditor__text"></div>
</div>