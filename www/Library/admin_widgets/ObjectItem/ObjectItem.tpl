<div class="ObjectItem" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-p="ObjectItem">
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
        if($v['is_link']->bool()){
            $class .= ' link';
        }
    ?>
    <div class="view<?php echo $class;?>" title="<?php echo $v['alt']->escape();?>">
		<span class="info">
            <a class="select" title="Выделить" href="<?php echo $v['uri'];?>"></a>
            <a class="prop" title="Свойства ссылки" href="/admin<?php echo $v['uri'];?>"></a>
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value_full']->escape();?>"><?php echo $v['value']->escape();?></span>
            <?php if ($v['is_file']->bool()):?>
                <span class="file"></span>
            <?php endif;?>
        </span>
		<span class="main">
            <a class="title" href="/admin<?php echo $v['link'];?>"><span><?php echo $v['title']->escape();?></span></a>
        </span>
	</div>
</div>