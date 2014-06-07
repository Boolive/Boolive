<div class="">
<?php
    $list = $v['views']->arrays(\boolive\values\Rule::string());
    foreach ($list as $item){
        echo $item;
    }
?>
</div>