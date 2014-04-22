<?php
    use \boolive\input\Input;
    $current = $v['current']->int();
    $count = $v['count']->int();
    $uri = $v['uri']->string();
    $show = $v['show']->int();
    // С какой страницы начинать
    $page_first = max(1, $current - $show);
	if ($page_first <= $show) $page_first = 1;
	// На какой странице закончить
	$page_end = min($count, $current + $show);
	if ($page_end > ($count - $show)) $page_end = $count;
?>
<div class="PageNavigation">
    <ul class="cf">
        <li class="pageinfo">Страницы с 1 до <?php echo $count?></li>
        <?php
        if ($current > 1) echo '<li><a href="'.Input::url($uri,0,array('page'=>$current-1), true).'">←</a></li>';

        if ($page_first > $show){
			echo '<li><a href="'.Input::url($uri,0,array('page'=>1), true).'">1</a></li>';
			echo '<li>...</li>';
		}
		for ($i = $page_first; $i <= $page_end; $i++){
			echo '<li'.($i==$current?' class="active"':'').'><a href="'.Input::url($uri,0,array('page'=>$i), true).'">'.$i.'</a></li>';
		}
		if ($page_end <= ($count - $show)){
			echo '<li>...</li>';
			echo '<li><a href="'.Input::url($uri,0,array('page'=>$count), true).'">'.$count.'</a></li>';
		}

        if ($current < $count) echo '<li><a href="'.Input::url($uri,0,array('page'=>$current+1), true).'">→</a></li>';
        ?>
    </ul>
</div>