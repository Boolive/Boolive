<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Требования для установки системы Boolive</title>
	<link rel="stylesheet" type="text/css" href="<?php echo DIR_WEB;?>boolive/installer/tpl/style.css" />
	<script src="<?php echo DIR_WEB;?>boolive/installer/tpl/jquery-1.7.1.min.js" type="text/javascript" language="javascript"></script>
	<script src="<?php echo DIR_WEB;?>boolive/installer/tpl/main.js" type="text/javascript" language="javascript"></script>

</head>
<body>
	<div id="req">
		<h1 class="logo"><span class="color1">B</span><span class="color2">o</span><span class="color3">o</span>live</h1>
		<p class="logo-descript">Cистема для создания и управления сайтом</p>
		<h2>Системные требования</h2>
		<ul>
		<?php
		$cnt = sizeof($v['errors']);
		for ($i=0; $i<$cnt; ++$i){
			echo '<li><span>'.$v['errors'][$i].'</span></li>';
		}
		?>
		</ul>
	</div>
</body>
</html>