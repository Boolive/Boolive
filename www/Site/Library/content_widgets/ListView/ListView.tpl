<ul>
    <?php
        $list = $v['view']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item) {
            echo $item;
        }
        
    ?>
</ul>
