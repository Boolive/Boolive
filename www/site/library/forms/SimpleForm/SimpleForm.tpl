<div class="form SimpleForm">
    <h2 class="form__title"><?=$v['title']?></h2>
    <form method="post" action="" enctype="application/x-www-form-urlencoded">
        <!-- Идентификатор формы -->
        <input type="hidden" name="form" value="<?=$v['view_id']?>">
        <!-- Поля -->
        <div class="item <?=isset($v['error']['name'])?'item_state_error':''?>">
            <label class="item__label" for="field_name">Название поля</label>
            <input class="item__input input_size_big" type="text" id="field_name" name="name" value="<?=$v['value']['name']?>">
            <span class="item__error"><?=isset($v['error']['name'])?$v['error']['name']:''?></span>
        </div>
        <div class="item">
            <button class="btn btn_type_primary" type="submit">Отправить</button>
        </div>
    </form>
</div>