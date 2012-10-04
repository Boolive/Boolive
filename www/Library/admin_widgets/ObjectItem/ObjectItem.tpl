<div class="ObjectItem" data-view_uri="<?php echo $v['view_uri'];?>" data-object="<?php echo $v['uri'];?>">
	<div class="view">
		<span class="colright">
		</span>
		<span class="colmain" title="Выделить">
			<!--<span class="col1"></span>-->
			<span class="col2">
				<!--<a title="Войти" href="<?php echo $v['uri'];?>" class="enter"></a>-->
				<span class="name"><?php echo $v['name'];?></span>
				<?php if (!empty($v['value'])):?><span class="value"><?php echo $v['value'];?></span><?php endif;?>
			</span>
		</span>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('.ObjectItem[widget!="true"]').ObjectItem();
	});
</script>
 