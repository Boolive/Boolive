<div class="Feedback">
    <h3>Обратная связь</h3>
    <?php  if ($v['ok']->bool()):?>
    <div class="form_ok">
        <?php echo $v['result_message']->string(); ?>
    </div>
    <?php endif; ?>
    <?php  if ($v['error']->bool()):?>
    <div class="form_error">
        <?php echo $v['error_message']; ?>
    </div>
    <?php endif; ?>
    <form action="" method="POST">
        <input type="hidden" name="0" value="">
        <input type="hidden" name="1" value="">
        <?php
        $list = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item){
            echo $item;
        }
        ?>
        <p><input type="submit" name="<?php echo $v['view_uri'];?>[submit]" value="Отправить"></p>
    </form>
</div>