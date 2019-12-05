<?//1573549934
class Templatemain {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div class="block">
	<div class="block-head">Главная</div>
	<div class="block-body ">
		<div>
		    <p>Это панель управления сайтом.</p>
			<p>Чтобы начать редактирование - выберите необходимый пункт меню</p>
			<br>
			<p>Версия шаблона: <? if (isset($v["version"]))echo $v["version"]; else trigger_error("Значение \"version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></p>
			<p>Версия ядра: <? if (isset($v["core_version"]))echo $v["core_version"]; else trigger_error("Значение \"core_version\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></p>
		</div>
	</div>
</div>
<?
}
}