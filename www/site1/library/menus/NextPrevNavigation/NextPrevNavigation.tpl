<div class="NextPrevNavigation cf">
<?php
    if ($v['prev']->bool()) {
        echo '<div class="prev">',
             '← <a href="', $v['prev']['href']->url(), '">', $v['prev']['title'], '</a>',
             '</div>';
    }
    if ($v['next']->bool()) {
        echo '<div class="next">',
             '<a href="', $v['next']['href']->url(), '">', $v['next']['title'], '</a> →',
             '</div>';
    }
?>
</div>