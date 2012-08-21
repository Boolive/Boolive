<ul>
<?php
    $list = $v['objects_list']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item){
        echo '<li>'.$item.'</li>';
    }
?>
</ul>