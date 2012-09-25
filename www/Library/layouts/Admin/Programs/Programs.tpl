<div class="Programs" data-view_uri="<?php echo $v['view_uri'];?>">
    <?php echo $v['view']->string();?>
</div>
<script type="text/javascript">
	$(function(){
		$('.Programs[widget!="true"]').Programs();
	});
</script>