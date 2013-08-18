<table border="0" cellspacing="0" cellpadding="0" width="100%" class="comment">
    <tr>
        <td width="20%"><?= $v['author']->entity()->name->value() ?></td>
        <td><?= $v['text']->string() ?></td>
    </tr>
    <?php
    $sub_comments = $v['sub_comments']->arrays(\Boolive\values\Rule::entity());
    if ($v['sub_comments']->string() != null):
    ?>
    <tr>
        <td colspan="2" style="padding-left: 30px;">
        <?= $v['sub_comments']->string() ?>
        </td>
    </tr>
    <?php
    endif;
    ?>
</table>
