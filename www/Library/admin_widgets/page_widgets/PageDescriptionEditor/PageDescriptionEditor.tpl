<div class="PageDescriptionEditor" data-o="<?php echo $v['object']?>" data-v = "<?php echo $v['view_uri']?>" data-p="PageDescriptionEditor">
    <div class="col1"><label><?php echo $v['title'];?></label></div>
    <div class="col2">
        <form method="POST" action="" class="form_description">
            <input type="hidden" name="object" value="<?php echo $v['object']?>">
            <textarea class="inpt description" name="Page[description]" ><?php echo $v['value'];?></textarea>
            <div class="error"></div>
        </form>
    </div>
</div>