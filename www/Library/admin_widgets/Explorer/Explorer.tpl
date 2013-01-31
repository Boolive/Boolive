<div class="Explorer" data-v="<?php echo $v['view_uri'];?>" data-p="Explorer">
	<div class="content">
<!--        <h1>--><?php //echo $v['head'];?><!--</h1>-->
        <br>
        <?php
            $list = $v['view']->arrays(\Boolive\values\Rule::string());
            foreach ($list as $item){
                echo $item;
            }
        ?>
    </div>
</div>