<div class="keywordsCloud">
    <h2><?php echo $v['title']; ?></h2>
    <?php
    use \Boolive\values\Rule;
    $keywords = $v['list']->arrays(Rule::arrays(array(
        'title' => Rule::string(),
        'value' => Rule::int(),
        'href' => Rule::uri()
        )
    ));
    $font_dsize = $v['font_dsize']->int();
    $font_start = $v['font_start']->int();
    $value_start = $v['value_start']->int();
    foreach ($keywords as $key=>$value) {
       $size = $font_start + $font_dsize * ($value['value'] + $value_start);
       echo '<a style="font-size:'.$size.'pt; line-height:'.$size.'pt;" href="'.$value['href'].'" title="'.$value['title'].'">'.$value['title'].'</a>'."\n";
    }
    ?>
</div>