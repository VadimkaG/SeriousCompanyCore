<?//1573551762
class Templatelayout {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "menu":return "0";break;case "warning":return "1";break;case "path":return "2";break;case "content":return "3";break;case "script":return "4";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><!DOCTYPE html>
<html lang="ru">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link rel="shortcut icon" href="/<? if (isset($v["pubres"]))echo $v["pubres"]; else trigger_error("Значение \"pubres\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/pictures/favicon.png" type="image/x-icon">
		<link href="/<? if (isset($v["styles"]))echo $v["styles"]; else trigger_error("Значение \"styles\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/main.css" rel="stylesheet">
		<link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap/?xml"/>
		<title><? if (isset($v["title"]))echo $v["title"]; else trigger_error("Значение \"title\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></title>
		<!-- Автор шаблона: seriouscompany.ru -->
		<!-- Версия шаблона: <? if (isset($v["version"]))echo $v["version"]; else trigger_error("Значение \"version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
		<!-- Версия ядра сайта: <? if (isset($v["core_version"]))echo $v["core_version"]; else trigger_error("Значение \"core_version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
	</head>
	<body>	
		<header><div><? if (isset($v["title_page"]))echo $v["title_page"]; else trigger_error("Значение \"title_page\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></div></header>
		<div class="HeadMenu">
			<ul>
				<?$this->c->callBlock($this,"block0","menu",$d);?>
			</ul>
		</div>
		<?$this->c->callBlock($this,"block1","warning",$d);?>
		<div class="wrapper">
			<!-- Панель пути -->
			<ul class="path">
			<?$this->c->callBlock($this,"block2","path",$d);?>
			</ul>
<?$this->c->callBlock($this,"block3","content",$d);?>
		</div>
		<footer><div><a href="http://core.seriouscompany.ru/" target="_blank" style="color:white;text-decoration:none;">SeriousCompanyCore</a> 2016-<? if (isset($v["date"]))echo $v["date"]; else trigger_error("Значение \"date\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> г.</div></footer>
	</body>
	<script src="/<? if (isset($v["pubres"]))echo $v["pubres"]; else trigger_error("Значение \"pubres\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/js/jquery-3.2.1.min.js"></script>
	<script>$(function(){$('#warning_popup').show();});</script>
<?$this->c->callBlock($this,"block4","script",$d);?>
</html>
<?
}
	public function block0($v=array(),&$d=null) {?>
<li><a href="<? if (isset($v["path"]))echo $v["path"]; else trigger_error("Значение \"path\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></a></li><?
	}
	public function block1($v=array(),&$d=null) {?>

		<div id="warning_popup" class="overlay-popup">
		    <div class="popup">
		    <p><? if (isset($v["warn_message"]))echo $v["warn_message"]; else trigger_error("Значение \"warn_message\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></p>
		    <button class="close" title="Закрыть" onclick="$('#warning_popup').remove();"></button>
		    </div>
	    </div>
		<?
	}
	public function block2($v=array(),&$d=null) {?>
<li><a href="<? if (isset($v["path"]))echo $v["path"]; else trigger_error("Значение \"path\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></a></li><?
	}
	public function block3($v=array(),&$d=null) {?>
<?
	}
	public function block4($v=array(),&$d=null) {?>
<?
	}
}