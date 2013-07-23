<?php
    $list = $v['views']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item){
        echo $item;
    }
?>