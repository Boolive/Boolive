<div class="Admin common_background" data-v="<?php echo $v['view_uri'];?>" data-p="Admin" data-base="<?php echo $v['basepath'];?>">
    <div class="center">
        <div class="window"><?php echo $v->Programs->string();?></div>
    </div>
    <div class="top">
        <div class="top-right"><?php
            echo $v->SaveTool->string();
            echo $v->MenuAuth->string();
        ?></div>
        <?php echo $v->BreadcrumbsMenu->string();?>
    </div>
    <div class="sidebar left">
        <!--<div class="shadow"></div>-->
        <?php echo $v->Bookmarks->string();?>
    </div>
    <div class="sidebar right">
        <!--<div class="shadow"></div>-->
        <?php echo $v->ProgramsMenu->string();?>
    </div>
    <div class="bottom">
        <?php echo $v->MenuSelections->string();?>
    </div>
</div>