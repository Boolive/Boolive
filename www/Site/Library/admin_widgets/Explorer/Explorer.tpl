<div class="Explorer" data-o="<?=$v['uri']?>" data-v="<?php echo $v['view_uri'];?>" data-p="Explorer">

    <div class="toolbar">
        <?=$v->AddTool->string();?>
        <div class="btn-group view-filter-group right" data-p="ExplorerFilter">
            <a class="btn-tool  btn-tool-square view-filter" title="Фильтр" href=""></a>
            <ul class="dropdown-menu pull-right">
                <?php foreach ($v['filters'] as $name => $f):?>
                <li<?php echo ($f['value']->bool()? ' class="selected"':'');?>><a data-filter="<?php echo $name;?>" href=""><?php echo $f['title'];?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="btn-group view-kind-group right" data-p="ExplorerViewKind">
            <a class="btn-tool  btn-tool-square view-kind" title="Вид" href=""></a>
            <ul class="dropdown-menu pull-right">
                <?php foreach ($v['view-kinds'] as $name => $k):?>
                <li<?php echo ($k['value']->bool()? ' class="selected"':'');?>><a data-view-kind="<?php echo $name;?>" href=""><?php echo $k['title'];?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
	<div class="layout-main list">
        <?php
            $list = $v['views']->arrays(\Boolive\values\Rule::string());
            if(!empty($list)){
                foreach ($list as $item){
                    echo $item;
                }
            }else{
                echo '<div class="empty">Пусто
                        <div class="explain">У объекта нет подчиненных или они не соответсятвуют фильтру </div></div>';
            }
        ?>
    </div>
</div>