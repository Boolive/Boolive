<div class="text">RichText
<?php
    $list = $v['objects_list']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item) {
        echo $item;
    }
?>
</div>