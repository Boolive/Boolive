<div class="ProgressAction" data-v="<?php echo $v['view_uri'];?>" data-o="<?php echo $v['data-o'];?>" data-p="ProgressAction">
    <div class="layout-main">
        <h2><?php echo $v['title'];?></h2>
        <p><?php echo $v['description'][0];?></p>
        <p class="mini"><?php echo $v['description'][1];?></p>
        <div class="confirm">
            <div class="layout-middle">
                <ul class="entity-list">
                <?php
                    $list = $v['objects'];//->arrays(\Boolive\values\Rule::any());
                    foreach ($list as $item):
                ?>
                    <li>
                    <span class="txt-primary"><?php echo $item['title'];?></span>
                    <?php if ($item['uri']->string()):?>
                    <span class="txt-tag"><?php echo $item['uri'];?></span>
                    <?php endif;?>
                    </li>
                <?php endforeach; ?>
                </ul>

                <p class="mini"><?php //echo $v['message'];?></p>
            </div>
            <div class="layout-bottom">
                <a class="btn btn-primary submit" href="#"><?php echo $v['submit_title'];?></a>
                <a class="btn cancel" href="#">Отмена</a>
            </div>
        </div>
        <div class="progress" style="display: none">
            <div class="layout-middle">
                <div class="prgressbar">
                    <div class="bar"><!--<div class="value"></div>--></div>
                </div>
                <div class="message">Сообщение</div>
            </div>
            <div class="layout-bottom">
                <a class="btn cancel" href="#">Отмена</a>
            </div>
        </div>
     </div>
</div>