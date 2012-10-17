<div class="ObjectItem" data-view_uri="<?=$v['view_uri']?>" data-object="<?=$v['uri']?>" data-plugin="ObjectItem">
	<div class="view">
		<span class="colright">
		</span>
		<span class="colmain" title="Выделить">
			<!--<span class="col1"></span>-->
			<span class="col2">
				<!--<a title="Войти" href="<?php echo $v['uri'];?>" class="enter"></a>-->
				<span class="name"><?php echo $v['name']->escape();?></span>
				<?php if (!empty($v['value'])):?><span class="value"><?php echo $v['value'];?></span><?php endif;?>
			</span>
		</span>
	</div>
</div>