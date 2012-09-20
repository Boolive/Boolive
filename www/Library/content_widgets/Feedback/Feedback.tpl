<div class="Feedback">
    <h3>Обратная связь</h3>
    <form action="" method="POST">
        <input type="hidden" name="view" value="<?php echo $v['uri'];?>">
        <input type="hidden" name="object[uri]" value="<?php echo $v['obj'];?>">
        <?php
        $list = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item) {
            echo '<p>' . $item . '</p>';
        }
        ?>
        <p><input type="submit" value="Отправить"></p>
    </form>
</div>