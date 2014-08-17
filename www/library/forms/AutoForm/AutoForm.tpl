<div class="form AutoForm">
    <h2 class="form__title"><?=$v['title']?></h2>
    <?php
    $result = $v['result']->int();
    $result_class = $result==1 ? 'form__result_error' : ($result==2 ? 'form__result_ok' : '');
    if ($result_class):
    ?>
    <div class="form__result <?=$result_class?>">
        <?php echo $v['message']->string(); ?>
    </div>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <!-- Идентификатор формы -->
        <input type="hidden" name="form" value="<?=$v['view_id']?>">
        <input type="hidden" name="object" value="<?=$v['object']?>">
        <!-- Поля -->
        <?php
        $list = $v['views']->arrays(\boolive\values\Rule::string());
        foreach ($list as $item){
            echo $item;
        }
        ?>
        <div class="form__buttons">
            <input class="btn btn_type_primary" type="submit" name="<?php echo $v['view_uri'];?>[submit]" value="Сохранить">
        </div>
    </form>
</div>