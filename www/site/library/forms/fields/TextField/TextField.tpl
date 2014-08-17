<div class="field TextField<?=isset($v['error'])?' field_state_error':''?>">
    <label class="field__label" for="id_<?=$v['id']?>"><?=$v['title']?></label>
    <textarea class="field__input input_type_textarea" id="id_<?=$v['id']?>" name="<?=$v['uri']?>" cols="" rows=""><?=$v['value']->escape()?></textarea>
    <?php if (isset($v['error'])): ?>
    <div class="field__error"><?=$v['error']?></div>
    <?php endif; ?>
</div>