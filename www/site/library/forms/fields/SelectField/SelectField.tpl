<div class="field SelectField<?=isset($v['error'])?' field_state_error':''?>">
    <label class="field__label" for="id_<?=$v['id']?>"><?=$v['title']?></label>
    <select class="field__input input_type_select" id="id_<?=$v['id']?>" name="<?=$v['uri']?>">
        <?php foreach($v['options'] as $o):?>
        <option value="<?=$o['value']?>" <?=$o['selected']->bool()?'selected="selected"':''?>><?=$o['title']?></option>
        <?php endforeach;?>
    </select>
    <?php if (isset($v['error'])): ?>
    <div class="field__error"><?=$v['error']?></div>
    <?php endif; ?>
</div>