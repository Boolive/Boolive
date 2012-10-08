<li class="<?php echo $v['item_class'];?>"><a href="<?php echo $v['item_href']; ?>" title="<?php echo $v['item_title']; ?>"><?php echo $v['item_text']; ?></a>
<?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    if ($list){
        echo '<ul>';
        foreach ($list as $item)  echo $item;
        echo '</ul>';
    }
?>
</li>