<?
namespace page\containers;
class Layout extends \page\Container {
	private $contents = array();
	private $scripts = array();
	private $title = false;
	private $keywords = false;
	private $description = false;
	public function init() {
		$title = '';
		$pageTitle = '';
		$keywords = '';
		$about = '';
		if ($this->title != false) {
			$title = $this->title.' - '.$this->config['Title'];
			$pageTitle = $this->title;
		} else {
			$title = $this->config['Title'];
			$pageTitle = &$title;
		}
		if ($this->keywords != false) $keywords = $this->keywords.$this->config['KeyWords'];
			else $keywords = $this->config['KeyWords'];
		if ($this->description == false) $this->description = $this->config['About'];
		
		$this->proc(array(
			'pageTitle'=>$this->config['Title'],
			'title'=>$title,
			'about'=>$about,
			'description'=>$this->description,
			'keywords'=>$keywords,
			'about'=>$this->config['About'],
			'date'=>date('Y'),
			'content'=>'',
			'styles_path'=>styles,
			'title_page'=>$pageTitle,
			'scripts'=>scripts,
			'pubres'=>"/".pubres,
			'res'=>"/".res,
			'js'=>"/".scripts,
			'css'=>"/".styles,
			'version'=>$this->template_config['version'],
			'core_version'=>$this->config['core_version']
		));
	}
	public function Block_warning($func,&$c){
		if (isset($this->page['last_event_out']['warning']) && $this->page['last_event_out']['warning'] != '')
			$c->$func(array('warn_message'=>$this->page['last_event_out']['warning']));
	}
	public function Block_content($func,&$c,&$data){
		foreach($this->contents as &$i) {
			$function = &$i['function'];
			$i['class']->$function($i['param'],$i['data']);
		}
	}
	public function Block_script($func,&$c,&$data){
		foreach($this->scripts as &$i) {
			$function = 'block'.$i['class']->getBlockName($i['function']);
			if ($i['param'])
				$i['class']->$function($i['param']);
			else
				$i['class']->$function();
		}
	}
	public function Block_department($func,&$c,&$data) {
	    foreach ($this->category as $cat) {
	    	$c->$func($cat);
	    }
	}
	public function Block_footer_menu($func,&$c,&$data) {
	    $arr = array(
	        '/about/'=>"О нас",
	        '/aboutt/'=>"О нас",
	        '/abouttt/'=>"О нас",
	        '/aboutttt/'=>"О нас"
	    );
	    //$c->$func(array('title'=>"Информация"),$arr);
	    //$c->$func(array('title'=>"Информация"),$arr);
	    //$c->$func(array('title'=>"Информация"),$arr);
	}
	public function Block_footer_menu_items($func,&$c,&$data) {
	    foreach ($data as $key=>$item) {
	        $c->$func(array('href'=>$key,'title'=>$item));
	    }
	}
	/**
	 * Вызовет функцию, когда придет время для контента
	 */
	public function addFunction(&$class,$function,$param=array(),$data = null) {
		$this->contents[] = array(
			'class'=>&$class,
			'function'=>$function,
			'param'=>$param,
			'data'=>$data
		);
	}
	/**
	 * Вызовет функцию, когда придет время для скриптов
	 */
	public function addScripts(&$class,$function,$param=null) {
		$this->scripts[] = array(
			'class'=>&$class,
			'function'=>$function,
			'param'=>$param
		);
	}
	/**
	 * Установить заголовок страницы
	 */
	public function setTitle($t) {
		$this->title = $t;
	}
	/**
	 * Добавить ключевые слова к общим
	 */
	public function setKeywords($t) {
		$this->keywords = $t;
	}
	/**
	 * Установить описание страницы
	 */
	public function setDescription($t) {
		$this->description = $t;
	}
}
