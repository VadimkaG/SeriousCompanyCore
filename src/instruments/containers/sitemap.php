<?
namespace page\containers;
class Sitemap extends \page\Container {
	public function init() {
		$this->pages = array(
			'/'=>'Главная',
			'/donate/'=>'Донат',
			'/start_game/'=>'Начать игру',
			'/rules/'=>'Правила'
		);
		if (isset($_GET['xml'])) {
			$this->proc();
		} else if (isset($_GET['block'])) {
			$this->proc();
		} else {
			require_once(containers.'/layout.php');
			$page = new Layout('layout');
			$page->addFunction($this,'proc');
			$page->init();
		}
	}
	public function Block_if_xml($func,&$c) {
		if (isset($_GET['xml'])) {
			header( 'Content-Type: text/xml' );
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			$c->$func();
		}
	}
	public function Block_for_block($func,&$c) {
		if (isset($_GET['block'])) {
			$c->$func();
		}
	}
	public function Block_for_item($func,&$c) {
		foreach ($this->pages as $path=>$name) {
			$c->$func(array(
				'path'=>$path,
				'page'=>$name
			));
		}
	}
	public function Block_if($func,&$c) {
		if (!isset($_GET['xml']) && !isset($_GET['block']))
			$c->$func();
	}
	public function Block_xml_item($func,&$c) {
		foreach ($this->pages as $path=>$name) {
			$c->$func(array(
				'path'=>$path,
				'page'=>$name
			));
		}
	}
	public function Block_item($func,&$c) {
		foreach ($this->pages as $path=>$name) {
			$c->$func(array(
				'path'=>$path,
				'page'=>$name
			));
		}
	}
}
