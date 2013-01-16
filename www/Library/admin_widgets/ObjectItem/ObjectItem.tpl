<div class="ObjectItem" data-view_uri="<?=$v['view_uri']?>" data-object="<?=$v['uri']?>" data-plugin="ObjectItem">
	<div class="view <?php echo $v['is_virtual']->bool()?'virtual':'';?>">
		<span class="colright">
            <a title="Выделить" href="<?php echo $v['uri'];?>" class="enter"></a>
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value']->escape();?>"><?php echo $v['value']->escape();?></span>
            <?php if ($v['is_file']->bool()):?>
            <span class="file"></span>
            <?php endif;?>
		</span>
		<span class="colmain" title="<?php echo $v['name']->escape();?>">
			<!--<span class="col1"></span>-->
			<span class="col2">
                <span class="title"><?php echo $v['title']->escape();?></span>
				<span class="name"><?php echo $v['name']->escape();?></span>
			</span>
		</span>
	</div>
</div>