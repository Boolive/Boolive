<div class="Admin" data-view_uri="<?php echo $v['view_uri'];?>">
	<div class="left">

    </div>
    <div class="wrap">
        <div class="top">
            <?php //echo $v['BreadcrumbsMenu']->string();?>
        </div>
        <div class="center">
            <?php //echo $v['Programs']->string();?>
        </div>
    </div>
    <div class="right">
        <?php //echo $v['ProgramsMenu']->string();?>
    </div>
</div>
<script type="text/javascript">
	$(function(){
		$('.Admin[widget!="true"]').Admin();
	});
</script>