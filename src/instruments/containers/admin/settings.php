<?
namespace page\containers\Admin;
class Settings extends \page\Container {
	public function init() {
		loadInstuments("settings");
		require_once(containers.'/admin/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc',array());
		$page->setTitle("Настройки");
		$page->init();
	}
	function Block_string($func,&$c,&$data) {
		foreach (listSettings() as $string) {
			$c->$func(array(
				'type'=>$this->getType($string['option_name']),
				'title'=>$this->locale($string['option_name']),
				'value'=>$string['option_value'],
				'alias'=>$string['option_name'],
				'atr'=>$this->getBooleanAtr($string['option_name'],$string['option_value'])
			));
		}
	}
	private function getBooleanAtr($alias,$value) {
		if ($this->getType($alias) != 'checkbox') return '';
		if ($value == 'True') return "checked";
		else $value = "";
	}
	private function getType($alias) {
		switch ($alias) {
			case "SiteClosed": return "checkbox";
			case "authEnabled": return "checkbox";
			default: return 'text';
		}
	}
	private function locale($text) {
		switch ($text) {
			case "Title": return "Название сайта";
			case "About": return "О сайте";
			case "KeyWords": return "Ключевые слова";
			case "SiteClosed": return "Закрыть сайт";
			case "ClosedReason": return "Причина закрытия сайта";
			case "authEnabled": return "Регистрация разрешена";
			case "template": return "Шаблон сайта";
			default: return $text;
		}
	}
}
