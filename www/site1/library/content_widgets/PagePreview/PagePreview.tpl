<div class="PagePreview">
<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
    <a class="more" href="<?php echo $v['href']?>">Далее</a>
</div>