<?
namespace page\containers;
class Main extends \page\Container {
    
	public function init() {
		
		$mailer = getModule("phpmailer");
		
		/*if ($mailer != null && isset($_GET['s'])) {
			$mailer->send("Тестовое сообщение","Тест","vadimka.golubev@gmail.com");
			echo 'Отправлено';
		}*/
		
		require_once(containers.'/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc',array(
			'res'=>"/".res,
			'pageTitle'=>$this->config['Title']
		));
		$page->setTitle("Главная");
		//$page->addScripts($this->template,'script_news',array('path'=>scripts));
		$page->init();
	}
	public function Block_menu($func,&$c,&$data) {
	    $c->$func(array('link'=>"/",'name'=>"Главная"));
	    $c->$func(array('link'=>"/sourcecode/",'name'=>"Исходный код"));
	    $c->$func(array('link'=>"/admin/login/",'name'=>"Авторизация"));
	    $c->$func(array('link'=>"/admin/",'name'=>"Админ-панель"));
	}
	public function Block_top_block($func,&$c,&$data) {
	    $c->$func(array(
	    		'link'=>'',
	    		'image'=>"/".res."/images/open-opportunity.png",
	    		'name'=>"Как зарадилось ядро?"
	    	));
	    $c->$func(array(
	    		'link'=>'',
	    		'image'=>"/".res."/images/illustration-training.png",
	    		'name'=>"Возможности ядра"
	    	));
	    $c->$func(array(
	    		'link'=>'',
	    		'image'=>"/".res."/images/roundplus.png",
	    		'name'=>"Создать сайт"
	    	));
	    $c->$func(array(
	    		'link'=>'',
	    		'image'=>"/".res."/images/pazl.png",
	    		'name'=>"Написание модуля"
	    	));
	}
	
	
}
