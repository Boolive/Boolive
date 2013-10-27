<div class="ObjectItem list" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-p="ObjectItem">
	<?php
        $class = '';
        if($v['is_hidden']->bool()){
            $class .= ' hidden';
        }
        if($v['is_draft']->bool()){
            $class .= ' draft';
        }
        if($v['is_link']->bool()){
            $class .= ' link';
        }
        switch ($v['diff']->int()){
            case \Boolive\data\Entity::DIFF_CHANGE:
                $class .= ' diff_change';
                break;
            case \Boolive\data\Entity::DIFF_ADD:
                $class .= ' diff_add';
                break;
            case \Boolive\data\Entity::DIFF_DELETE:
                $class .= ' diff_delete';
                break;
        }
    ?>
    <div class="view<?php echo $class;?>" title="<?php echo $v['alt']->escape();?>">
		<span class="info">
            <a class="select" title="Выделить" href="<?php echo $v['uri'];?>"></a>
            <a class="prop" title="Свойства ссылки" href="<?php echo ltrim($v['uri'],'/');?>"></a>
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value_full']->escape();?>"><?php echo $v['value']->escape();?></span>
            <?php if ($v['is_file']->bool()):?>
                <span class="file"></span>
            <?php endif;?>
        </span>
		<span class="main">
            <a class="title" href="<?php echo ltrim($v['link'], '/');?>"><span><?php echo $v['title']->escape();?></span></a>
            <span class="description"><?php echo $v['description'];?></span>
        </span>
	</div>
</div>