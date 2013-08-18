<div class="bars">
<?php
    $list = $v['bars']->arrays(\Boolive\values\Rule::string());
    foreach ($list as $item){
        echo $item;
    }
?>
</div>