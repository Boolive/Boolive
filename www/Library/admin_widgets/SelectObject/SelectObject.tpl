<div class="SelectObject" data-view_uri="<?php echo $v['view_uri'];?>" data-plugin="SelectObject">
    <div class="main">
        <div class="content">
            <h1><?php echo $v['title'];?></h1>
            <p><?php echo $v['message'];?></p>
            <div class="view">
                <?php echo $v['programs']->string();?>
            </div>
        </div>
    </div>
    <div class="buttons">
        <div class="content">
            <a class="btn cancel"><?php echo $v['cancel_title'];?></a>
            <a class="btn btn-success submit"><?php echo $v['submit_title'];?></a>
        </div>
    </div>
</div>