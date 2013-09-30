<div class="layout">
    <div class="header">
        <div class="wrapper">
            <?php echo $v->Logo->string();?>
            <?php echo $v->TopMenu->string();?>
        </div>
    </div>
    <div class="cf main">
        <div class="wrapper">
            <div class="topbar">
                <?php echo $v->Slider->string();?>
            </div>
            <div class="sidebar">
                <?php echo $v->ContentMenu->string();?>
            </div>
            <div class="center">
               <?php echo $v->ContentView->string();?>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <div class="wrapper">
        <?php echo $v->FooterText->string();?>
    </div>
</div>