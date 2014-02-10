<label for="<?php echo $v['id'];?>"><?php echo $v['title'];?></label>
<input id="<?php echo $v['id'];?>" name="<?php echo $v['uri'];?>" <?php echo $v['value']->bool()?'checked':'';?> type="checkbox" value="1">
<?php if (isset($v['error'])): ?>
<p class="error_message"><?php echo $v['error']; ?></p>
<?php endif; ?>