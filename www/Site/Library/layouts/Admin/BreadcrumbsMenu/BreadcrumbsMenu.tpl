<div class="BreadcrumbsMenu" data-v="<?php echo $v['view_uri'];?>" data-p="BreadcrumbsMenu">
	<ul class="view">
		<?php for ($i = count($v['items'])-1; $i>=0; $i--):?>
			<li class="<?php echo $v['items'][$i]['class'];?>" style="z-index: <?php echo $i?>;">
				<a class="entity" href="<?php echo $v['items'][$i]['url']?>" data-o="<?php echo $v['items'][$i]['uri']?>"><span><?php echo $v['items'][$i]['title']?></span></a>
			</li>
		<?php endfor; ?>
	</ul>
    <div class="inline" style="display: none;">
        <input type="text" value="<?php echo $v['current'];?>">
    </div>
</div>