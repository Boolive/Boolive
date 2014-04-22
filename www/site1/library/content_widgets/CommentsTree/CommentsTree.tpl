<div class="CommentsTree" data-p="CommentsTree" data-v="<?=$v['view_id']?>" data-o="<?=$v['object']?>">
    <h3 class="CommentsTree__title">Комментарии</h3>
    <div class="Comment__sub">
    <?php
        $list = $v['views']->arrays(\Boolive\values\Rule::string());
        foreach ($list as $item){
            echo $item;
        }
    ?>
    </div>
    <div class="CommentsTree__links">
        <a href="" class="Comment__link-answer Comment__link-answer_hide" data-o="<?=$v['object']?>">Написать комментарий</a>
        <div class="CommentsTree__new">
            <div class="CommentsTree__item">
                <textarea class="CommentsTree__item-message" rows="" cols="" placeholder="Текст комментария"></textarea>
            </div>
            <div class="CommentsTree__new-links">
                <a class="CommentsTree__submit" href="">Написать</a>
            </div>
        </div>
    </div>
</div>