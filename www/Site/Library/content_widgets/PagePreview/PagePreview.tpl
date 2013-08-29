<div class="PagePreview">
<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
    <a href="<?php echo $v['href']?>">Далее</a>
</div>