<?//1573549934
class Templatedatabase {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "answear":return "0";break;case "request":return "1";break;case "line":return "2";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div class="block">
	<div class="block-head">SQL запрос в базу данных</div>
	<div class="block-body">
		<form method="POST">
			<textarea name="SQL" style="min-width: 99%;max-width:100%;min-height:200px"><? if (isset($v["last"]))echo $v["last"]; else trigger_error("Значение \"last\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></textarea>
			<input type="submit" class="btn bc_gray" value="Выполнить запрос">
		</form>
		<pre><? if (isset($v["error"]))echo $v["error"]; else trigger_error("Значение \"error\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></pre>
	</div>
</div>
<?$this->c->callBlock($this,"block0","answear",$d);?>
<?
}
	public function block0($v=array(),&$d=null) {?>

<div class="block">
	<div class="block-head">Ответ</div>
	<ul class="block-body">
		<?$this->c->callBlock($this,"block1","request",$d);?>
	</ul>
</div>
<?
	}
	public function block1($v=array(),&$d=null) {?>

		<li class="tline">
			<?$this->c->callBlock($this,"block2","line",$d);?>
		</li>
		<?
	}
	public function block2($v=array(),&$d=null) {?>

			<div class="tline-cell"><? if (isset($v["content"]))echo $v["content"]; else trigger_error("Значение \"content\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></div>
			<?
	}
}