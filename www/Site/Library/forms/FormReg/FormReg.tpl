<div class="FormReg">
    <form method="post" action="" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="form" value="<?=$v['view_id']?>">
        <div class="item <?=isset($v['error']['login'])?'error':''?>">
            <label for="login">Логин</label><br/>
            <input placeholder="Логин" type="text" id="login" name="login" value="<?=$v['value']['login']?>">
            <span class="error-text"><?=isset($v['error']['login'])?$v['error']['login']:''?></span>
        </div>
        <div class="item <?=isset($v['error']['email'])?'error':''?>">
            <label for="email">Email</label><br/>
            <input placeholder="Email" type="text" id="email" name="email" value="<?=$v['value']['email']?>">
            <span class="error-text"><?=isset($v['error']['email'])?$v['error']['email']:''?></span>
        </div>
        <div class="item <?=isset($v['error']['passw'])?'error':''?>">
            <label for="passw">Пароль</label><br/>
            <input placeholder="Пароль" type="password" id="passw" name="passw" value="<?=$v['value']['passw']?>">
            <span class="error-text"><?=isset($v['error']['passw'])?$v['error']['passw']:''?></span>
        </div>
        <div class="item <?=isset($v['error']['passw_retry'])?'error':''?>">
            <label for="passw">Пароль повторно</label><br/>
            <input placeholder="Пароль повторно" type="password" id="passw_retry" name="passw_retry" value="<?=$v['value']['passw_retry']?>">
            <span class="error-text"><?=isset($v['error']['passw_retry'])?$v['error']['passw_retry']:''?></span>
        </div>
        <div class="item">
            <button type="submit">Зарегистрироваться</button>
        </div>
    </form>
</div>