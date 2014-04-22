<div class="layout">
    <div class="header">
        <div class="wrapper">
            <?php echo $v->Logo->string();?>
            <?php echo $v->TopMenu->string();?>
            <?php echo $v->SearchForm->string();?>
        </div>
    </div>
    <div class="cf main">
        <div class="wrapper">
            <div class="topbar">
                <?php echo $v->Slider->string();?>
            </div>
            <div class="sidebar">
                <?php echo $v->ContentMenu->string();?>
                <?php echo $v->Widget->string();?>
            </div>
            <div class="center">
               <?php echo $v->center->string();?>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <div class="wrapper">
        <?php echo $v->FooterText->string();?>
    </div>
</div>