<?php
    $v->style;

    $current = $v['current']->int();
    $count = $v['count']->int();
    $uri = $v['uri']->string();
    $show = 3;
    // С какой страницы начинать
    $page_first = max(1, $current - $show);
	if ($page_first <= $show) $page_first = 1;
	// На какой странице закончить
	$page_end = min($count, $current + $show);
	if ($page_end > ($count - $show)) $page_end = $count;
?>
<div class="PageNavigation">
    <ul>
        <li class="pageinfo">Страницы 1 до <?php echo $count?></li>
        <?php
        if ($current > 1) echo '<li><a href="'.$uri.'/page-'.($current-1).'">←</a></li>';

        if ($page_first > $show){
			echo '<li><a href="'.$uri.'/page-1">1</a></li>';
			echo '<li>...</li>';
		}
		for ($i = $page_first; $i <= $page_end; $i++){
			echo '<li'.($i==$current?' class="active"':'').'><a href="'.$uri.'/page-'.$i.'">'.$i.'</a></li>';
		}
		if ($page_end <= ($count - $show)){
			echo '<li>...</li>';
			echo '<li><a href="'.$uri.'/page-'.$count.'">'.$count.'</a></li>';
		}

        if ($current < $count) echo '<li><a href="'.$uri.'/page-'.($current+1).'">→</a></li>';
        ?>
    </ul>
</div>