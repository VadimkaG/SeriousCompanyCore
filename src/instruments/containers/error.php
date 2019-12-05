<?
namespace page\containers;
class Error extends \page\Container {
	public function init() {
		require_once(containers.'/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc',array('res'=>"/".res));
		$page->setTitle("Страница не найдена");	
		$page->setKeywords("error404");
		$page->setDescription("Страница не найдена");
		$page->init();
	}
}
