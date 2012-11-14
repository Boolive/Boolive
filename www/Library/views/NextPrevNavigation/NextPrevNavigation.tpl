<table border="0" cellpadding="0" cellspacing="0" width="100%" class="NextPrevPage">
    <tr>
        <td width="50%">
        <?php
            if ($v['prev']->bool()) {
                echo '← <a href="' . $v['prev']['href']->string() . '" class="NextPrevLinks">' . $v['prev']['title']->string() . '</a>';
            }
        ?>
        </td>
        <td style="text-align: right;">
        <?php
            if ($v['next']->bool()) {
                echo '<a href="' . $v['next']['href']->string() . '" class="NextPrevLinks">' . $v['next']['title']->string() . '</a> →';
            }
        ?>
        </td>
    </tr>
</table>