<div class="PageKeywordsEditor" data-o="<?php echo $v['object']?>" data-v="<?php echo $v['view_uri']?>" data-p="PageKeywordsEditor">
    <label><?php echo $v['title'];?></label>
    <div class="keywords inpt">
        <div class="old">
        <?php
            $list = $v['views']->arrays(\Boolive\values\Rule::string());
            if(!empty($list)){
                foreach ($list as $item){
                    echo $item;
                }
            }
            ?>
        </div>
        <input type="text" class="value" value="">
    </div>
</div>