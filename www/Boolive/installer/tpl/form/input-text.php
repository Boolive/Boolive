<div class="item">
	<label><?php echo $v['label']?></label><?php
	if (!empty($v['required'])) echo '<span class="required">*</span>';
	?><br>
	<?php if (!empty($v['prefix'])) echo $v['prefix'];?>
		<input spellcheck="false" type="text" class="<?php echo $v['style']?><?php echo (isset($v['error'])?' error':'');?>" value="<?php echo $v['value']?>" name="<?php echo $v['name']?>">
	<?php if (!empty($v['postfix'])) echo $v['postfix'];?><br>
	<?php
	if (!empty($v['error'])){
		echo '<label class="error">'.$v['error'].'</label><br>';
	}
	if (!empty($v['descript'])){
		echo '<label class="descript">'.$v['descript'].'</label>';
	}
	?>
</div>