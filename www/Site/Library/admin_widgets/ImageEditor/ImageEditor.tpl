<?php
$class = '';
if($v['is_hidden']->bool()){
    $class .= ' hidden';
}
if($v['is_delete']->bool()){
    $class .= ' deleted';
}
?><img class="<?php echo $class;?>" src="<?php echo $v['file']?>" alt="" <?php if ($s = $v['style']->string()) echo 'style="'.$s.'"';?> data-p="ImageEditor" data-o="<?=$v['object']?>" data-v="<?php echo $v['view_uri'];?>"/>