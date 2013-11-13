<div class="FormAuth2">
    <form method="post" action="" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="form" value="<?=$v['view_id']?>">
        <div class="item <?=isset($v['error']['login'])?'error':''?>">
            <label for="login">Логин</label><br/>
            <input placeholder="Логин" type="text" id="login" name="login" value="<?=$v['value']['login']?>">
            <span class="error-text"><?=isset($v['error']['login'])?$v['error']['login']:''?></span>
        </div>
        <div class="item <?=isset($v['error']['passw'])?'error':''?>">
            <label for="passw">Пароль</label><br/>
            <input placeholder="Пароль" type="password" id="passw" name="passw" value="<?=$v['value']['passw']?>">
            <span class="error-text"><?=isset($v['error']['passw'])?$v['error']['passw']:''?></span>
        </div>
        <div class="item <?=isset($v['error']['passw_retry'])?'error':''?>">
            <label for="remember">Запомнить меня</label>
            <input id="remember" name="remember" <?php echo $v['value']['remember']->bool()?'checked':'';?> type="checkbox" value="1">
            </div>
        <div class="item">
            <button type="submit">Войти</button>
        </div>
    </form>
</div>