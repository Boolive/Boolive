<div class="PartPreview">
<?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
    <a href="<?php echo $v['href']?>">Далее</a>
</div>