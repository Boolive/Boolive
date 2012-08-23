<?php $v->style; ?>
<div class="Feedback" x="fff/ggg">
    <h3>Обратная связь</h3>
    <form action="" method="POST">
        <input type="hidden" name="controller" value="<?php echo $v['uri'];?>">
        <?php
        $list = $v['objects_list']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item) {
            echo '<p>' . $item . '</p>';
        }
        ?>
        <p><input type="submit" value="Отправить"></p>
    </form>
</div>