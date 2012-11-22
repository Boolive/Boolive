<div class="FormAuth">
    <h1>Вход</h1>
    <?php  if ($v['ok']->bool()):?>
    <div class="form_ok">
        <?php echo $v['ok']->string(); ?>
    </div>
    <?php endif; ?>
    <?php  if ($v['error']->bool()):?>
    <div class="form_error">
        <?php echo $v['error']; ?>
    </div>
    <?php endif; ?>
    <form action="" method="POST">
        <?php
        $list = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item){
            echo $item;
        }
        ?>
        <p><input type="submit" name="<?php echo $v['view_uri'];?>[submit]" value="Войти"></p>
    </form>
</div>