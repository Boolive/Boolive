<div class="comments_wrapper">
    <?php
    if ($v['show_title']->bool()):
    ?>
    <h3>Комментарии</h3>
    <?php
    endif;
    ?>
    <div class="comments">
        <?php
        $comments = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($comments as $object) {
            echo $object;
        }
        ?>
    </div>
</div>
