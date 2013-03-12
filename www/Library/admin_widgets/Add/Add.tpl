<div class="Add" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['object']['uri'];?>" data-p="Add">
    <div class="content">
        <h2><?php echo $v['title'];?></h2>
        <p>Выделите объект, который хотите добавить в
            <span class="txt-primary"><?php echo $v['object']['title'];?></span>
            <?php if ($v['object']['uri']->string()):?>
            <span class="txt-tag"><?php echo $v['object']['uri'];?></span>
            <?php endif;?> и нажмите кнопку "Добавить"
        </p>
        <p class="mini">Для просмотра всех объектов нажмите "Все..."</p>
        <div class="list">
        <?php
        $list = $v['view']->arrays(\Boolive\values\Rule::string());
            foreach ($list as $item){
                echo $item;
            }
        ?>
            <a class="other" href="">Все...</a>
        </div>

        <div class="buttons">
            <a class="btn btn-success  btn-disable submit" href="#">Добавить</a>
            <a class="btn cancel" href="#">Отмена</a>
        </div>
    </div>
</div>