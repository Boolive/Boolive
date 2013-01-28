<div class="keywordsCloud">
    <h2><?php echo $v['title']; ?></h2>
    <?php
    use \Boolive\values\Rule;
    $keywords = $v['list']->arrays(Rule::arrays(array(
        'title'=>Rule::string(),
        'value'=>Rule::int(),
        'href'=>Rule::uri()
        )
    ));
    $max_font_size =  $v['max_font_size']->int();
    $min_font_size =  $v['min_font_size']->int();
    $font_dif = $max_font_size - $min_font_size;
    //ищем мин и макс использования ключевых слов
    $min_value = $max_value = $keywords[0];
    foreach ($keywords as $key=>$value) {
        if($value['value']<$min_value['value']){
            $min_value = $value;
        }
        if($value['value']>$max_value['value']){
            $max_value = $value;
        }
    }
    $fs_dif = $max_value['value'] - $min_value['value'];
    if ( $fs_dif==0 ) $tg_dif = 1;
    //шаг размера шрифта
    $step = ($fs_dif / $font_dif);
    foreach ($keywords as $key=>$value) {
       $size = round($min_font_size + (($value['value'] - $min_value['value'] ) * $step));
       echo '<a style="font-size:'.$size.'px" href="?tag='.$value['href'].'" title="'.$value['title'].'">'.$value['title'].'</a>'."\n";
    }

    ?>
</div>