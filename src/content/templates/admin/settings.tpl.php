<?//1573549934
class Templatesettings {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "string":return "0";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div class="block">
	<div class="block-head">Настройки</div>
	<div class="block-body">
		<div>
			<form method="POST" action="/admin/settings/">
				<input type="hidden" name="action" value="changeSiteSettings">
				<table>
					<?$this->c->callBlock($this,"block0","string",$d);?>
				</table>
				<input type="submit" value="Сохранить насройки" class="btn_green"><br>
			</form>
		</div>
	</div>
</div>
<?
}
	public function block0($v=array(),&$d=null) {?>

					<tr>
						<td><? if (isset($v["title"]))echo $v["title"]; else trigger_error("Значение \"title\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></td>
						<td><input type="<? if (isset($v["type"]))echo $v["type"]; else trigger_error("Значение \"type\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>" name="<? if (isset($v["alias"]))echo $v["alias"]; else trigger_error("Значение \"alias\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>" value="<? if (isset($v["value"]))echo $v["value"]; else trigger_error("Значение \"value\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>" <? if (isset($v["atr"]))echo $v["atr"]; else trigger_error("Значение \"atr\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>></td>
					</tr>
					<?
	}
}