<div class="PagePreview">
<?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
    <a href="<?php echo $v['object_uri']?>">Далее</a>
</div>