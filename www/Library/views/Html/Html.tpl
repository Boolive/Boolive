<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php echo $v['head']->string(); ?>
    </head>
    <body>
        <?php echo $v['body']->string(); ?>
        <?php
//        trace(\Boolive\develop\Benchmark::stop('all', true), 'Benchmark');
//        \Boolive\develop\Trace::groups('DB')->out();
        ?>
    </body>
</html>