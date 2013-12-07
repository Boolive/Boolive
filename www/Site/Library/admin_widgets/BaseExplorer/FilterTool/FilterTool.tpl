<div class="btn-group FilterTool" data-p="FilterTool" data-v="<?php echo $v['view_uri'];?>">
    <a class="btn-tool  btn-tool-square" title="Фильтр" href=""><img src="<?=$v['icon']?>" width="16" height="16" alt=""></a>
    <ul class="dropdown-menu pull-right">
        <?php foreach ($v['filters'] as $name => $f):?>
        <li<?php echo ($f['value']->bool()? ' class="selected"':'');?>><a data-filter="<?php echo $name;?>" href=""><?php echo $f['title'];?></a></li>
        <?php endforeach; ?>
    </ul>
</div>