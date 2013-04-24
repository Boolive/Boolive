<div class="PageTextEditor" data-o="<?php echo $v['object']?>" data-v = "<?php echo $v['view_uri']?>" data-p="PageTextEditor">
    <div class="col1"><label><?php echo $v['title'];?></label></div>
    <div class="col2">
        <div <?php if ($s = $v['style']->string()) echo 'style="'.$s.'"';?> >
        <?php
            $list = $v['view']->arrays(\Boolive\values\Rule::string());
            foreach ($list as $item) {
                echo $item;
            }
        ?>
        </div>
     <div class="link">
         <a class="textlink" href="#">Редактировать</a>
     </div>
    </div>
</div>