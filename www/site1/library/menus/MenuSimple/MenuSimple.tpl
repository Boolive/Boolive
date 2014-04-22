<ul class="MenuSimple">
    <?php
    $print_items = function($items)use(&$print_items){
        foreach($items as $item){
            $active = $item['active']->int();
            $class = $active==0? '' : ($active==1 ? 'active' : 'sub-active');
            if ($class) $class = ' class="'.$class.'"';
            echo '<li'.$class.'><a href="'.$item['url'].'">';
            if ($item['icon']->bool()){
                echo '<img src="'.$item['icon'].'" alt="">';
            }
            echo '<span>'.$item['title'].'</span></a>';
            if (isset($item['children'])){
                echo '<ul>';
                $print_items($item['children']);
                echo '</ul>';
            }
            echo '</li>';
        }
    };
    $print_items($v['items']);
?></ul>