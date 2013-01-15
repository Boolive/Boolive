<div class="Delete" data-view_uri="<?php echo $v['view_uri'];?>" data-object="<?php echo $v['object']['uri'];?>" data-plugin="Delete">
    <div class="main">
        <div class="content">
            <h2><?php echo $v['title'];?></h2>
            <p>Вы действительно желаете удалить
                <span class="txt-primary"><?php echo $v['object']['title'];?></span>
                <?php if ($v['object']['uri']->string()):?>
                <span class="txt-tag"><?php echo $v['object']['uri'];?></span>
                <?php endif;?>?
            </p>
            <p class="mini">Объект будет перемещён в корзину, его можно будет восстановить.</p>
         </div>
    </div>
    <div class="bottom">
        <div class="content">
            <a class="btn btn-danger submit" href="#">Да</a>
            <a class="btn cancel" href="#">Отмена</a>
        </div>
    </div>
</div>