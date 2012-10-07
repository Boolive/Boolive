<?php

$prev = $v['prev']->entity();
$next = $v['next']->entity();

if ($prev == null) {
    $next = '<a href="' . $v['next_href']->string() . '" class="NextPrevLinks">' . $next->title['value'] . '</a> →';
} else if ($next == null) {
    $prev = '← <a href="' . $v['prev_href']->string() . '" class="NextPrevLinks">' . $prev->title['value'];
} else {
    $next = '<a href="' . $v['next_href']->string() . '" class="NextPrevLinks">' . $next->title['value'] . '</a> →';
    $prev = '← <a href="' . $v['prev_href']->string() . '" class="NextPrevLinks">' . $prev->title['value'];
}
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="NextPrevPage">
    <tr>
        <td width="50%"><?= $prev ?></td>
        <td style="text-align: right;"><?= $next ?></td>
    </tr>
</table>
