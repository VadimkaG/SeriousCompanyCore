<?//1574851923
class Templatesourcecode_main {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "list":return "0";break;case "file":return "1";break;case "list_item":return "2";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><html>
	<head>
		<title><? if (isset($v["title"]))echo $v["title"]; else trigger_error("Значение \"title\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></title>
		<meta charset="UTF-8">
		<!-- Ядро сервера: SeriousCompanyCore -->
		<!-- Версия шаблона: <? if (isset($v["version"]))echo $v["version"]; else trigger_error("Значение \"version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
		<!-- Версия ядра сайта: <? if (isset($v["core_version"]))echo $v["core_version"]; else trigger_error("Значение \"core_version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
		<meta name="description" content="<? if (isset($v["description"]))echo $v["description"]; else trigger_error("Значение \"description\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>">
		<meta name="keywords" content="<? if (isset($v["keywords"]))echo $v["keywords"]; else trigger_error("Значение \"keywords\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>">
		<meta name="author" content="Vadimka">
	</head>
	<body>
		<?$this->c->callBlock($this,"block0","list",$d);?> 
		<?$this->c->callBlock($this,"block1","file",$d);?>
	</body>
</html>
<?
}
	public function block0($v=array(),&$d=null) {?>

		<ul>
			<?$this->c->callBlock($this,"block2","list_item",$d);?> 
		</ul>
		<?
	}
	public function block1($v=array(),&$d=null) {?>
<pre><? if (isset($v["file"]))echo $v["file"]; else trigger_error("Значение \"file\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></pre><?
	}
	public function block2($v=array(),&$d=null) {?>
 
			<li><a href="<? if (isset($v["link"]))echo $v["link"]; else trigger_error("Значение \"link\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></a></li>
			<?
	}
}