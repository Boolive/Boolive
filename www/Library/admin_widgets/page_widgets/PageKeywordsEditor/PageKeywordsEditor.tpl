<div class="PageKeywordsEditor" data-o="<?php echo $v['object']?>" data-v="<?php echo $v['view_uri']?>" data-p="PageKeywordsEditor">
    <div class="col1"><label><?php echo $v['title'];?></label></div>
    <div class="col2">
            <div class="keywords inpt">
                <div class="old">
                <?php
                    $list = $v['view']->arrays(\Boolive\values\Rule::string());
                    if(!empty($list)){
                        foreach ($list as $item){
                            echo $item;
                        }
                    }
                    ?>
                    </div>
                <form method="post" action="" class="add">
                    <input type="text" class="value" name="Keyword[value]" value="">
                    <input type="hidden" name="object" value="<?php echo $v['object']?>">
                </form>
            </div>
        </div>
    </div>