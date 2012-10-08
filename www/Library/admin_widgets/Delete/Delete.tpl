<div class="Delete" data-view_uri="<?php echo $v['view_uri'];?>" data-object="<?php echo $v['object']['uri'];?>">
    <div class="main">
        <div class="content">
            <h1><?php echo $v['title'];?></h1>
            <p>Вы действительно желаете удалить этот объект?</p>
            <p class="mini">Объект будет перемещён в корзину, его можно будет восстановить.</p>
            <div class="info">
                <span class="name"><?php echo $v['object']['title'];?></span>
                <span class="inline-hint"><?php echo $v['object']['uri'];?></span>
            </div>
        </div>
    </div>
    <div class="bottom">
        <div class="content">
            <a class="btn cancel" href="#">Нет</a>
            <a class="btn btn-danger submit" href="#">Да</a>
        </div>
    </div>
</div>
<script type="text/javascript">
	$(function(){
		$('.Delete[widget!="true"]').Delete();
	});
</script>