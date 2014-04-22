<div class="Comment">
    <div class="Comment__head">
        <div class="Comment__user-avatar"><?php if ($v['user']['avatar']->bool()):?><img src="<?=$v['user']['avatar']?>" alt="" width="40"/><?php endif;?></div>
        <div class="Comment__user-name"><?=$v['user']['name']?></div>
        <div class="Comment__date">(<?=date('j F',$v['date']->int())?>)</div>
    </div>
    <div class="Comment__message"><?=$v['views']->cut('message')->string()?></div>
    <div class="Comment__links">
        <a href="" class="Comment__link-answer" data-o="<?=$v['uri']?>">Ответить</a>
    </div>
    <div class="Comment__sub">
    <?php
        $list = $v['views']->arrays(\boolive\values\Rule::string());
        foreach ($list as $item){
            echo $item;
        }
    ?>
    </div>
</div>