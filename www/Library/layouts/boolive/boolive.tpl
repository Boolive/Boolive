<?php $v->style; ?>
<div id="layout">
    <div id="layout-head">
        <div class="layout-wrapper" data-place="head">
            <?php echo $v->head->string(); ?>
        </div>
    </div>
    <div id="layout-middle">
        <div class="layout-wrapper">
            <div class="top" data-place="top">
                <?php echo $v->top->string(); ?>
            </div>
            <div class="center" data-place="center">
                <?php echo $v->center->string(); ?>
            </div>
            <div class="sidebar" data-place="sidebar">
                <?php echo $v->sidebar->string(); ?>
            </div>
        </div>
    </div>
</div>
<div id="layout-bottom">
    <div class="layout-wrapper" data-place="bottom">
        <?php echo $v->bottom->string(); ?>
    </div>
</div>