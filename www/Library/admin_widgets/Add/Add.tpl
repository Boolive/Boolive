<div class="Add" data-view_uri="<?php echo $v['view_uri'];?>" data-object="<?php echo $v['object']['uri'];?>" data-plugin="Add">
    <div class="main">
        <div class="content">
            <h1><?php echo $v['title'];?></h1>
            <p>Выберите объект, который хотите добавить в
                <span class="name">"<?php echo $v['object']['title'];?>"</span>
                <span class="inline-hint"><?php echo $v['object']['uri'];?></span>
            </p>
            <div class="list">
            <?php
            $list = $v['view']->arrays(\Boolive\values\Rule::string());
                foreach ($list as $item){
                    echo $item;
                }
            ?>
            </div>
        </div>
    </div>
    <div class="bottom">
        <div class="content">
            <a class="btn btn-primary other" href="">Другой...</a>
            <a class="btn cancel" href="#">Отмена</a>
            <a class="btn btn-success  btn-disable submit" href="#">Добавить</a>
        </div>
    </div>
</div>