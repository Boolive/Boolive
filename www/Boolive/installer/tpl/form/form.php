<h2><?php echo $v['title']?></h2>
<p class="descript"><?php echo $v['descript']?></p>
<form id="userform" class="form-list" action="" method="post" accept-charset="UTF-8">
	<?php if (!empty($v['error'])):?>
	<p class="error"><?php echo $v['error']?></p>
	<?php endif;?>
	<?php
		if (isset($v['fields'])){
			$cnt = count($v['fields']);
			for ($i=0; $i<$cnt; $i++){
				echo $v['fields'][$i];
			}
		}
	?>
	<div class="item-buttons">
		<input class="button"  type="submit" name="next" value="Далее">
	</div>
	<p class="descript"><span class="required">*</span> - поля, обязательные для заполнения</p>
</form>
