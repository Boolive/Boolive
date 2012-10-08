<label for="<?php echo $v['id'];?>"><?php echo $v['title'];?></label>
<textarea id="<?php echo $v['id'];?>" name="<?php echo $v['uri'];?>" rows="8" cols="50"><?php echo $v['value']->escape();?></textarea>
<?php if (isset($v['error'])): ?>
<p class="error_message"><?php echo $v['error']; ?></p>
<?php endif; ?>