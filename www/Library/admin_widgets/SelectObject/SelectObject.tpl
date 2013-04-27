<div class="SelectObject" data-v="<?php echo $v['view_uri'];?>" data-p="SelectObject">
    <div class="main">
        <div class="content">
            <h2><?php echo $v['title'];?></h2>
            <p><?php echo $v['message'];?></p>
            <p class="mini"><?php echo $v['message2'];?></p>
            <div class="view">
                <?php echo $v->programs->string();?>
            </div>
        </div>
    </div>
    <div class="buttons">
        <div class="content">
            <a class="btn btn-success submit"><?php echo $v['submit_title'];?></a>
            <a class="btn cancel"><?php echo $v['cancel_title'];?></a>
        </div>
    </div>
</div>