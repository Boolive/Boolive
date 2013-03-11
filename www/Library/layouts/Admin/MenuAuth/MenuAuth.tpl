<div class="MenuAuth" data-v="<?=$v['view_uri']?>" data-p="MenuAuth">
    <ul class="buttons">
        <?php if (isset($v['name'])): ?>
        <li>
            <a title="Профиль пользователя" class="userlink" data-o="<?=$v['userlink']?>" href="/admin<?php echo $v['userlink'];?>"><?php echo $v['name'];?></a>
        </li>
        <li>
            <a title="Отмена авторизации" class="exit" href="<?php echo $v['logout'];?>">Выйти</a>
        </li>
        <?php endif;?>
        <li>
            <a title="Главная страница сайта" class="site" href="/">На сайт</a>
        </li>

    </ul>
</div>