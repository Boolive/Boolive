<div class="ObjectItem tile" data-v="<?=$v['view_uri']?>" data-o="<?=$v['uri']?>" data-p="ObjectItem">
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
		<a class="title" href="/admin<?php echo $v['uri'];?>">
            <?php
            $s = $v['title']->escape();
            if ($pos = mb_strpos($s, ' ')){
                $s2 = mb_substr($s, $pos);
                $s = mb_substr($s, 0, $pos);

            }
            $width = round((mb_strlen($s)*18)/50)*50+13;
            echo '<span style="min-width:'.$width.'px;">'.$s.'</span>';
            if (!empty($s2)) echo '<span class="mini">'.$s2.'</span>';
            ?>
        </a>
        <div class="info">
            <?php if ($v['is_file']->bool()):?>
                <span class="file"></span>
            <?php endif;?>
            <span class="value <?php echo $v['is_default_value']->bool()?'default':'';?>" title="<?php echo $v['value_full']->escape();?>"><?php echo $v['value']->escape();?></span>
        </div>
        <a class="select" title="Выделить" href="<?php echo $v['uri'];?>"></a>
	</div>
</div>