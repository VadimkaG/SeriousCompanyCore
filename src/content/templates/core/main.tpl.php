<?//1574847517
class Templatemain {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "top_block":return "0";break;case "menu":return "1";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?>		<div class="topheader">
			<div class="uptitle"><? if (isset($v["pageTitle"]))echo $v["pageTitle"]; else trigger_error("Значение \"pageTitle\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></div>
			<div class="row middlemessage">
				<?$this->c->callBlock($this,"block0","top_block",$d);?>
			</div>
			<ul class="menu">
				<?$this->c->callBlock($this,"block1","menu",$d);?>
			</ul>
		</div>
<?
}
	public function block0($v=array(),&$d=null) {?>

				<div class="col d-flex justify-content-center">
						<div class="col-10 item">
							<a href="<? if (isset($v["link"]))echo $v["link"]; else trigger_error("Значение \"link\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>">
							<div class="d-flex justify-content-center icon">
								<img src="<? if (isset($v["image"]))echo $v["image"]; else trigger_error("Значение \"image\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"></img>
							</div>
							<div class="title">
								<span><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></span>
							</div>
							</a>
						</div>
				</div>
				<?
	}
	public function block1($v=array(),&$d=null) {?>

				<li><a href="<? if (isset($v["link"]))echo $v["link"]; else trigger_error("Значение \"link\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></a></li>
				<?
	}
}