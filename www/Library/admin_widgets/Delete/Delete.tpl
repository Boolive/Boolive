<div class="Delete" data-view_uri="<?php echo $v['view_uri'];?>" data-object="<?php echo $v['object']['uri'];?>">
    <div class="main">
        <div class="content">
            <h1><?php echo $v['title'];?></h1>
            <p>Вы действительно собираетесь удалить этот объект?</p>
            <p class="mini">Объект будет перемещен в корзину, его можно будет восстановить.</p>
            <div class="info">
                <span class="name"><?php echo $v['object']['title'];?></span>
                <span class="uri"><?php echo $v['object']['uri'];?></span>
            </div>


        </div>
    </div>
    <div class="bottom">
        <div class="content">
            <a class="button cancel" href="#">Нет</a>
            <a class="button submit" href="#">Удалить</a>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(function(){
		$('.Delete[widget!="true"]').Delete();
	});
</script>