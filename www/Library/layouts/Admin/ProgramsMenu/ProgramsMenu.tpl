<div class="ProgramsMenu" data-view_uri="<?php echo $v['view_uri'];?>" data-plugin="ProgramsMenu">
	<ul>
		<?php foreach ($v['items']->arrays(\Boolive\values\Rule::any()) as $item):?>
			<li>
				<a class="view-program" href="<?php echo $item['view_name']?>" style="background-image: url(<?php echo $item['icon'];?>);"><?php
					echo $item['title'];
				?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>