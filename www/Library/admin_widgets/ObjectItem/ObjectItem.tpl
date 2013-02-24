<div class="ObjectItem" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-p="ObjectItem">
	<?php
        $class = '';
        if($v['is_virtual']->bool()){
            $class .= ' virtual';
        }
        if($v['is_hidden']->bool()){
            $class .= ' hidden';
        }
        if($v['is_delete']->bool()){
            $class .= ' deleted';
        }
    ?>
    <div class="view<?php echo $class;?>" title="<?php echo $v['uri']->escape();?>">
		<span class="colright">
            <a class="select" title="Выделить" href="<?php echo $v['uri'];?>"></a>
            <!--<span class="name"><?php //echo $v['name']->escape();?></span>-->
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value_full']->escape();?>"><?php echo $v['value']->escape();?></span>
            <?php if ($v['is_file']->bool()):?>
                <span class="file"></span>
            <?php endif;?>
        </span>
		<span class="colmain">
			<!--<span class="col1"></span>-->
			<span class="col2">
                <a class="title" href="/admin<?php echo $v['uri'];?>"><?php echo $v['title']->escape();?></a>
			</span>
		</span>
	</div>
</div>