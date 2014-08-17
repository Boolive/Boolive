<div class="Sidebar" data-p="Sidebar">
    <div class="Sidebar__views">
    <?php foreach($v['tabs'] as $name => $t):?>
        <div class="Sidebar__view<?=$t['active']->bool()?' Sidebar__view_active':''?>" data-name="<?=$name?>"><?=$t['html']->string()?></div>
    <?php endforeach;?>
    </div>
    <div class="Sidebar__tabs">
        <?php foreach($v['tabs'] as $name => $t):
        ?><div class="Sidebar__tab<?=$t['active']->bool()?' Sidebar__tab_active':''?>" data-name="<?=$name?>"><?=$t['title']->string()?></div><?php
        endforeach;?>
    </div>
</div>