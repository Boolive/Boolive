<div class="keywordsCloud">
    <?php
    use \Boolive\values\Rule;
    $keywords = $v['list']->arrays(Rule::arrays(array(
        'value'=>Rule::int(),
        'href'=>Rule::uri()
        )
    ));
    foreach ($keywords as $key=>$value) {
        if($value['value']<5){
            $font_size = 10;
        }else if($value['value']<10){
            $font_size = 12;
        }else if($value['value']<15){
            $font_size = 15;
        }else if($value['value']<20){
            $font_size = 20;
        }else if($value['value']<25){
            $font_size = 25;
        }
        echo '<a href="'.$value['href'].'" style="font-size:'.$font_size.'px; line-height:'.$font_size.'px;">'.$key.'</a> ';
    }

    ?>
</div>