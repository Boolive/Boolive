<?php if ($v['show-item']->bool()):?>
<li class="<?php echo $v['item_class'];?>"><a href="<?php echo $v['item_href']; ?>" title="<?php echo $v['item_title']; ?>"><?php
        if ($v['item_icon']->bool()):?><img class="Menu__item_icon" src="<?php echo $v['item_icon']->uri();?>" alt="" width="16" height="16"><?php endif;
        ?><span><?php echo $v['item_text']; ?></span></a>
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