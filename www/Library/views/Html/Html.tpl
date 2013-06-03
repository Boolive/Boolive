<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <?php echo $v['head']->string(); ?>
    </head>
    <body>
        <?php echo $v['body']->string(); ?>
        <?php if (GLOBAL_TRACE){
            \Boolive\develop\Trace::groups()->group('Benchmark')->set(\Boolive\develop\Benchmark::stop('all', true));
            \Boolive\develop\Trace::groups()->out();
        }
        ?>
    </body>
</html>