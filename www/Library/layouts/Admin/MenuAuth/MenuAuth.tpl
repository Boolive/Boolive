<div class="MenuAuth" data-v="<?=$v['view_uri']?>" data-p="MenuAuth">
    <ul class="buttons">
        <?php if (isset($v['name'])): ?>
        <li>
            <a class="userlink" data-o="<?=$v['userlink']?>" href="/admin<?php echo $v['userlink'];?>"><?php echo $v['name'];?></a>
        </li>
        <li>
            <a class="exit" href="<?php echo $v['logout'];?>">Выйти</a>
        </li>
        <?php endif;?>
        <li>
            <a class="site" href="/">На сайт</a>
        </li>

    </ul>
</div>