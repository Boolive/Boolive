<div class="text" <?php if ($s = $v['style']->string()) echo 'style="'.$s.'"';?>>
<?php
    $list = $v['view']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
</div>