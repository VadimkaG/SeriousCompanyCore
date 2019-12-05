<?//1574159233
class Templatelayout {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "warning":return "0";break;case "content":return "1";break;case "script":return "2";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><!DOCTYPE html>
<html>
	<head>
		<title><? if (isset($v["title"]))echo $v["title"]; else trigger_error("Значение \"title\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></title>
		<meta charset="UTF-8">
		<!-- Ядро сервера: SeriousCompanyCore -->
		<!-- Версия шаблона: <? if (isset($v["version"]))echo $v["version"]; else trigger_error("Значение \"version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
		<!-- Версия ядра сайта: <? if (isset($v["core_version"]))echo $v["core_version"]; else trigger_error("Значение \"core_version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?> -->
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
		<meta name="description" content="<? if (isset($v["description"]))echo $v["description"]; else trigger_error("Значение \"description\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>">
		<meta name="keywords" content="<? if (isset($v["keywords"]))echo $v["keywords"]; else trigger_error("Значение \"keywords\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>">
		<meta name="author" content="Vadimka">
		<link rel="stylesheet" href="<? if (isset($v["css"]))echo $v["css"]; else trigger_error("Значение \"css\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/bootstrap.min.css">
		<link rel="stylesheet" href="<? if (isset($v["css"]))echo $v["css"]; else trigger_error("Значение \"css\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/main.css">
	</head>
	<body>
		<?$this->c->callBlock($this,"block0","warning",$d);?>
		<?$this->c->callBlock($this,"block1","content",$d);?> 
		<script src="<? if (isset($v["pubres"]))echo $v["pubres"]; else trigger_error("Значение \"pubres\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/js/jquery-3.2.1.min.js"></script>
		<script src="<? if (isset($v["js"]))echo $v["js"]; else trigger_error("Значение \"js\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/bootstrap.min.js"></script>
		<script>$(function(){$('#warning').modal();});</script>
		<?$this->c->callBlock($this,"block2","script",$d);?> 
	</body>
</html>
<?
}
	public function block0($v=array(),&$d=null) {?>

		<div class="modal" tabindex="-1" role="dialog" id="warning" >
		  <div class="modal-dialog" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
				</button>
			  </div>
			  <div class="modal-body">
				<p><? if (isset($v["warn_message"]))echo $v["warn_message"]; else trigger_error("Значение \"warn_message\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></p>
			  </div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			  </div>
			</div>
		  </div>
		</div>
		<?
	}
	public function block1($v=array(),&$d=null) {?>
<?
	}
	public function block2($v=array(),&$d=null) {?>
<?
	}
}