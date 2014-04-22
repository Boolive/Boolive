<div class="LogicEditor" data-v="<?=$v['view_uri']?>" data-o="<?=$v['data-o'];?>" data-p="LogicEditor" data-is_default="<?=$v['is_default_class']->int()?>">
    <div style="display: none;" class="LogicEditor__contents">
        <div id="default"><?=$v['content']['default']?></div>
        <div id="self"><?=$v['content']['self']?></div>
    </div>
    <div class="LogicEditor__head">
        <div class="LogicEditor__head-left">
            <h1 class="LogicEditor__title" title=""><span class="LogicEditor__title-name">Логика объекта</span> <?=$v['title']?></h1>
            <p class="LogicEditor__description">PHP класс, наследуемый от класса прототипа</p>
        </div>
        <div class="LogicEditor__head-right LogicEditor__default-menu">
            <div class="btn btn-min left LogicEditor__btn_default <?=$v['is_default_class']->bool()?'LogicEditor__btn_selected':''?>">По умолчанию</div>
            <div class="btn btn-min left LogicEditor__btn_self <?=!$v['is_default_class']->bool()?'LogicEditor__btn_selected':''?>">Своя</div>
        </div>
    </div>
    <div class="LogicEditor__text"></div>
</div>