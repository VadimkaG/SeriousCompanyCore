<?
namespace page\containers\Admin;
class Main extends \page\Container {
	public function init() {
		require_once(containers.'/admin/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc',array(
		    'version'=>$this->template_config['version'],
			'core_version'=>$this->config['core_version']
		));
		$page->setTitle("Главная");
		$page->init();
	}
}
