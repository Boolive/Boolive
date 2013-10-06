<?php if ($v['show-item']->bool()):?>
<li class="<?php echo $v['item_class'];?>"><a href="<?php echo $v['item_href']; ?>" title="<?php echo $v['item_title']; ?>"><img src="<?php echo $v['item_icon']->uri();?>" alt=""><span><?php echo $v['item_text']; ?></span></a>
<?php endif; ?>
<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    if ($list){
        echo '<ul>';
        foreach ($list as $item)  echo $item;
        echo '</ul>';
    }
?>
<?php if ($v['show-item']->bool()):?>
</li>
<?php endif; ?>