<div class="Add" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['object']['uri'];?>" data-p="Add">
    <div class="main">
        <div class="content">
            <h2><?php echo $v['title'];?></h2>
            <p>Выберите объект, который хотите добавить в
                <span class="txt-primary"><?php echo $v['object']['title'];?></span>
                <?php if ($v['object']['uri']->string()):?>
                <span class="txt-tag"><?php echo $v['object']['uri'];?></span>
                <?php endif;?>
            </p>
            <div class="list">
            <?php
            $list = $v['view']->arrays(\Boolive\values\Rule::string());
                foreach ($list as $item){
                    echo $item;
                }
            ?>
                <a class="btn btn-primary other" href="">Другой...</a>
            </div>
        </div>
    </div>
    <div class="buttons">
        <div class="content">
            <a class="btn btn-success  btn-disable submit" href="#">Добавить</a>
            <a class="btn cancel" href="#">Отмена</a>

        </div>
    </div>
</div>