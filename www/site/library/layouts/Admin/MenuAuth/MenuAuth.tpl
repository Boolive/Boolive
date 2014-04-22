<div class="MenuAuth" data-v="<?=$v['view_uri']?>" data-p="MenuAuth">
    <ul class="buttons">
        <?php if (isset($v['name'])): ?>
        <li class="userlink">
            <a title="Профиль пользователя" data-o="<?=$v['userlink']?>" href="/admin<?php echo $v['userlink'];?>"><img class="MenuAuth__userlink-icon" src="<?=$v['usericon']?>" width="16" height="16" alt=""/><?php echo $v['name'];?></a>
        </li><li class="exit">
            <a title="Отмена авторизации" href="<?php echo $v['logout'];?>"><img class="MenuAuth__icon" src="/site/library/layouts/Admin/MenuAuth/res/style/img/exit.png" width="13" height="13" alt=""/> Выход</a>
        </li><?php endif;?><li class="site">
            <a title="Главная страница сайта" href="/"><img class="MenuAuth__icon" src="/site/library/layouts/Admin/MenuAuth/res/style/img/arrow.png" width="13" height="13" alt=""/> Сайт</a>
        </li>
    </ul>
</div>