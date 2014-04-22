<?php
    $current = $v['current']->int();
    $count = $v['count']->int();
    $uri = ltrim($v['uri']->string(),'/');
    $show = $v['show']->int();
    // С какой страницы начинать
    $page_first = max(1, $current - $show);
	if ($page_first <= $show) $page_first = 1;
	// На какой странице закончить
	$page_end = min($count, $current + $show);
	if ($page_end > ($count - $show)) $page_end = $count;
?>
<div class="PageNavigation" data-p="PageNavigation">
    <ul class="cf">
        <li class="pageinfo">Страницы с 1 до <?php echo $count?></li>
        <?php
        if ($current > 1) echo '<li><a href="'.$uri.'&page='.($current-1).'" data-page="'.($current-1).'">←</a></li>';

        if ($page_first > $show){
			echo '<li><a class="PageNavigation__page" href="'.$uri.'&page=1" data-page="1">1</a></li>';
			echo '<li>...</li>';
		}
		for ($i = $page_first; $i <= $page_end; $i++){
			echo '<li'.($i==$current?' class="active"':'').'><a class="PageNavigation__page" href="'.$uri.'&page='.$i.'" data-page="'.$i.'">'.$i.'</a></li>';
		}
		if ($page_end <= ($count - $show)){
			echo '<li>...</li>';
			echo '<li><a class="PageNavigation__page" href="'.$uri.'&page='.$count.'" data-page="'.$count.'">'.$count.'</a></li>';
		}

        if ($current < $count) echo '<li><a class="PageNavigation__page" href="'.$uri.'&page='.($current+1).'" data-page="'.($current+1).'">→</a></li>';
        ?>
    </ul>
</div>