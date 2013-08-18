<li><?php
    $item = $v['view']->array(\Boolive\values\Rule::string());
    foreach ($item as $part) {
        echo $part;
    }
?></li>
