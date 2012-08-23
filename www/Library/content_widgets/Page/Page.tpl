<?php
$v->style;
$list = $v['objects_list']->arrays(\Boolive\values\Rule::string());
?>
<div class="post">
    <?php echo $list['title']; ?>
    <?php echo $list['text']; ?>
</div>
<?php
unset($list['title'], $list['text']);
foreach ($list as $item) {
    echo '<div>' . $item . '</div>';
}
?>