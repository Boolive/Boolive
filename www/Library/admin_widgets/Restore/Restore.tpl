<div class="Restore" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['data-o'];?>" data-p="Restore" data-prev="<?php echo $v['prev'];?>">
    <div class="main">
        <div class="content">
            <h2><?php echo $v['title'];?></h2>
            <p>Вы действительно желаете восстановить</p>
            <ul>
            <?php
                $list = $v['objects'];
                foreach ($list as $item):
            ?>
                <li>
                <span class="txt-primary"><?php echo $item['title'];?></span>
                <?php if ($item['uri']->string()):?>
                <span class="txt-tag"><?php echo $item['uri'];?></span>
                <?php endif;?>?
                </li>
            <?php endforeach; ?>
            </ul>
            <p class="mini"><?php echo $v['message'];?></p>
         </div>
    </div>
    <div class="bottom">
        <div class="content">
            <a class="btn btn-success submit" href="#">Да</a>
            <a class="btn cancel" href="#">Отмена</a>
        </div>
    </div>
</div>