<?php
namespace modules\admin;
class LayoutTemplate {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \modules\admin\Layout)) throw \InvalidArgumentException('$c must be \modules\admin\Layout');
		$this->c = $c;
	}
	public function main($title,$version,$version_core) {
?><!DOCTYPE html>
<html lang="ru">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="shortcut icon" href="/admin/res/img/favicon.png" type="image/x-icon">
		<link href="/admin/res/css/main.css" rel="stylesheet">
		<link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap/?xml"/>
		<title><?php echo $title;?></title>
		<!-- Автор шаблона: seriouscompany.ru -->
		<!-- Версия модуля admin: <?php echo $version;?> -->
		<!-- Ядро сайта: core.seriouscompany.ru -->
		<!-- Версия ядра сайта: <?php echo $version_core;?> -->
	</head>
	<body>
		<header><div><?php echo $title;?></div></header>
		<div class="HeadMenu">
			<ul><?php $this->c->Block_menu("menu",$this);?> 
			</ul>
		</div><?php $this->c->Block_warning("warning",$this);?> 
		<div class="wrapper">
			<ul class="path"><?php $this->c->Block_path("path",$this);?> 
			</ul>
<?php $this->c->Block_content();?> 
		</div>
		<footer><div>2020 г. <a href="http://core.seriouscompany.ru/" target="_blank" style="color:white;text-decoration:none;">SeriousCompanyCore</a></div></footer>
	</body>
	<script src="/admin/res/js/jquery-3.2.1.min.js"></script>
	<script src="/admin/res/js/events.js"></script>
<?php $this->c->Block_script();?> 
</html>
<?php
}
	public function menu($path="",$name="") {?> 
				<li><a href="<?php echo $path;?>"><?php echo $name;?></a></li><?php
	}
	public function warning($message = "") {?> 
		<div id="warning_popup" class="overlay-popup">
			<div class="popup">
				<p><?php echo $message;?></p>
				<button class="close" title="Закрыть" onclick="$('#warning_popup').remove();"></button>
			</div>
		</div><?php
	}
	public function path($path = "",$name = "") {?> 
				<li><a href="<?php echo $path;?>"><?php echo $name;?></a></li><?php
	}
}