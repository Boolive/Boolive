<div class="BreadcrumbsMenu" data-view_uri="<?php echo $v['view_uri'];?>">
	<ul>
		<?php for ($i = sizeof($v['items'])-1; $i>=0; $i--):?>
			<li class="<?php echo $v['items'][$i]['active']->bool()?'active':''?>" style="z-index: <?php echo $i?>;">
				<a class="entity" href="<?php echo $v['items'][$i]['url']?>"><?php echo $v['items'][$i]['title']?></a>
			</li>
		<?php endfor; ?>
	</ul>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('.BreadcrumbsMenu[widget!="true"]').BreadcrumbsMenu();
	});
</script>
