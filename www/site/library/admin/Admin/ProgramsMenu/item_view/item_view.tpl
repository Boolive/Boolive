<?php if ($v['show-item']->bool()):?>
<li class="group">
    <div class="group-title"><span class="title common_background"><?php echo $v['title'];?></span></div>
<?php endif; ?>
<?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    if ($list){
        echo '<ul>';
        foreach ($list as $item)  echo $item;
        echo '</ul>';
    }
?>
<?php if ($v['show-item']->bool()):?>
</li>
<?php endif; ?>