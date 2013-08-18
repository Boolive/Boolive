<div class="Delete" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['data-o'];?>" data-p="Delete" data-prev="<?php echo $v['prev'];?>">
    <div class="content">
        <h2><?php echo $v['title'];?></h2>
        <ul class="entity-list">
        <?php
            $list = $v['objects'];//->arrays(\Boolive\values\Rule::any());
            foreach ($list as $item):
        ?>
            <li>
            <span class="txt-primary"><?php echo $item['title'];?></span>
            <?php if ($item['uri']->string()):?>
            <span class="txt-tag"><?php echo $item['uri'];?></span>
            <?php endif;?>
            </li>
        <?php endforeach; ?>
        </ul>
        <p><?php echo $v['question'];?></p>
        <p class="mini"><?php echo $v['message'];?></p>

        <div class="buttons">
                <a class="btn btn-danger submit" href="#">Удалить в корзину</a>
                <a class="btn cancel" href="#">Отмена</a>
        </div>
     </div>
</div>