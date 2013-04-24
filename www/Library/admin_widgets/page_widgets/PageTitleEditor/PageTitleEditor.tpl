<div class="PageTitleEditor" data-o="<?php echo $v['object']?>" data-v = "<?php echo $v['view_uri']?>" data-p="PageTitleEditor">
    <div class="col1"><label><?php echo $v['title'];?></label> </div>
    <div class="col2">
        <form method="POST" action="" class="form_title">
            <input type="hidden" name="inpt object" value="">
            <input type="text" class="inpt title" name="Page[title]" value="<?php echo $v['value'];?>">
            <div class="error"></div>
        </form>
    </div>
</div>