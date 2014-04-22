<div class="SimpleForm">
    <form method="post" action="" enctype="application/x-www-form-urlencoded">
        <!-- Идентификатор формы -->
        <input type="hidden" name="form" value="<?=$v['view_id']?>">
        <!-- Поля -->
        <div class="item">
            <label for="field1">Полее</label><input type="text" id="field1" name="field1" value="Значение">
        </div>
        <div class="item">
            <button type="submit">Отправить</button>
        </div>
    </form>
</div>