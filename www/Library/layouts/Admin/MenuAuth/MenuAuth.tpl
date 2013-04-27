<div class="MenuAuth" data-v="<?=$v['view_uri']?>" data-p="MenuAuth">
    <ul class="buttons">
        <?php if (isset($v['name'])): ?>
        <li class="userlink">
            <a title="Профиль пользователя" data-o="<?=$v['userlink']?>" href="/admin<?php echo $v['userlink'];?>"><?php echo $v['name'];?></a>
        </li><li class="exit">
            <a title="Отмена авторизации" href="<?php echo $v['logout'];?>">Выйти</a>
        </li><?php endif;?><li class="site">
            <a title="Главная страница сайта" href="/">Главная</a>
        </li>
    </ul>
</div>