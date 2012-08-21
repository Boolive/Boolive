<?php

$list = $v['objects_list']->arrays(\Boolive\values\Rule::string());
foreach ($list as $item) {
    echo '<div>' . $item . '</div>';
}

?>