<div class="ObjectItem tile" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-l="<?=$v['link']?>" data-p="ObjectItem">
	<?php
        $class = '';
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
    <div class="view<?php echo $class;?>" <?php echo $v['style']->string();?> title="<?php echo $v['alt']->escape();?>">
		<div class="title-group">
            <?php
            $s = $v['title']->escape();
            if ($pos = mb_strpos($s, ' ')){
                $s2 = mb_substr($s, $pos);
                $s = mb_substr($s, 0, $pos);

            }
            $width = max(118, round((mb_strlen($s)*18+18)/50)*50+18);
            echo '<a class="title" href="'.ltrim($v['link'],'/').'" style="min-width:'.$width.'px;"><span>'.$s.'</span></a>';
            if (!empty($s2)) echo '<span class="mini">'.$s2.'</span>';
            ?>
        </div>
        <div class="info">
            <a class="prop" title="Свойства ссылки" href="<?php echo ltrim($v['uri'],'/');?>"></a>
            <?php if ($v['is_file']->bool()):?>
                <span class="file"></span>
            <?php endif;?>
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value_full']->escape();?>"><?php echo $v['value']->escape();?></span>
        </div>
        <a class="select" title="Выделить" href="<?php echo ltrim($v['uri'],'/');?>"></a>
	</div>
</div>