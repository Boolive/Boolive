<div class="item<?=isset($v['error'])?' item_state_error':''?>"">
    <label class="item__label" for="id_<?=$v['id']?>"><?=$v['title']?></label>
    <input class="item__input input_size_big" id="id_<?=$v['id']?>" name="<?=$v['uri']?>" value="<?=$v['value']->escape()?>" type="text">
    <?php if (isset($v['error'])): ?>
    <div class="item__error"><?=$v['error']?></div>
    <?php endif; ?>
</div>