<?
namespace page\containers\Admin;
class Layout extends \page\Container {
	private $contents = array();
	private $scripts = array();
	private $title = false;
	public function init() {
		$this->proc(array(
			'title'=>$this->config['Title'],
			'about'=>$this->config['About'],
			'date'=>date('Y'),
			'content'=>'',
			'styles'=>styles,
			'title_page'=>'Админ-панель - '.$this->config['Title'],
			'scripts'=>scripts,
			'pubres'=>pubres,
			'res'=>res,
			'version'=>$this->template_config['version'],
			'core_version'=>$this->config['core_version']
		));
	}
	public function Block_warning($func,&$c){
		if (isset($this->page['last_event_out']['warning']) && $this->page['last_event_out']['warning'] != '')
			$c->$func(array('warn_message'=>$this->page['last_event_out']['warning']));
	}
	public function Block_path($func,&$c) {
		$path = '/';
		foreach ($this->config['path'] as $page) {
			if ($page == '') continue;
			$path .= $page.'/';
			$c->$func(array(
				'path'=>$path,
				'name'=>$page
			));
		}
	}
	public function Block_menu($func,&$c) {
		$menu = array('/'=>'Главная');
		if (checkPerm('admin.database'))
			$menu['/admin/database/'] = 'База данных';
		if (checkPerm('admin.users'))
			$menu['/admin/users/'] = 'Пользователи';
		if (checkPerm('admin.groups'))
			$menu['/admin/groups/'] = 'Группы';
		if (checkPerm('admin.settings'))
			$menu['/admin/settings/'] = 'Настройки';
		foreach ($menu as $path=>$name) {
			$c->$func(array(
				'path'=>$path,
				'name'=>$name
			));
		}
	}
	public function Block_content($func,&$c){
		foreach($this->contents as &$i) {
			$function = &$i['function'];
			if ($i['param'])
				$i['class']->$function($i['param']);
			else
				$i['class']->$function();
		}
	}
	public function Block_script($func,&$c){
		foreach($this->scripts as &$i) {
			$function = 'block'.$i['class']->getBlockName($i['function']);
			if ($i['param'])
				$i['class']->$function($i['param']);
			else
				$i['class']->$function();
		}
	}
	/**
	 * Вызовет функцию, когда придет время для контента
	 */
	public function addFunction(&$class,$function,$param=false) {
		$this->contents[] = array(
			'class'=>&$class,
			'function'=>$function,
			'param'=>$param
		);
	}
	/**
	 * Вызовет функцию, когда придет время для скриптов
	 */
	public function addScripts(&$class,$function,$param=false) {
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
}
