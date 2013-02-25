<div class="Explorer" data-o="<?=$v['uri']?>" data-v="<?php echo $v['view_uri'];?>" data-p="Explorer">
    <div class="btn-group view-filter-group" data-p="ExplorerFilter">
        <a class="btn-tool  btn-tool-square view-filter" title="Фильтр" href=""></a>
        <ul class="dropdown-menu pull-right">
            <li<?php echo ($v['real']->bool()? ' class="selected"':'');?>><a data-filter="real" href="">Реальные</a></li>
            <li<?php echo $v['virtual']->bool()?' class="selected"':'';?>><a data-filter="virtual" href="">Виртуальные</a></li>
            <li<?php echo $v['hidden']->bool()? ' class="selected"':'';?>><a data-filter="hidden" href="">Скрытые</a></li>
            <li<?php echo $v['deleted']->bool()?' class="selected"':'';?>><a data-filter="deleted" href="">Удаленные</a></li>
        </ul>
    </div>
	<div class="content">
<!--        <h1>--><?php //echo $v['head'];?><!--</h1>-->
        <br>
        <?php
            $list = $v['view']->arrays(\Boolive\values\Rule::string());
            foreach ($list as $item){
                echo $item;
            }
        ?>
    </div>
</div>