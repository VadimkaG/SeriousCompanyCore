<?
namespace modules\admin;
\modules\Module::load("admin");
class Layout extends \PathExecutor {
	
	private $template;
	// Страница
	private $contents = array();
	// Скрипты страницы
	private $scripts = array();
	// Описание сайта
	private $SITE_ABOUT = "";
	// Заголовок страницы
	private $TITLE = "";
	// Путь страницы
	private $PATH;
	// Всплывающее сообщение
	private $WARNING = null;

	public function __construct($path) {
		require_once(__dir__."/template/layout.tpl.php");
		$this->template = new LayoutTemplate($this);
		$this->PATH = $path;
	}
	/**
	 * Получить заголовок страницы
	 * @return string - Заголовок страницы
	 */
	public function getTitle() {
		return $this->TITLE;
	}
	/**
	 * Установить заголовок страницы
	 * @param $title - string - заголовок страницы
	 */
	public function setTitle($title) {
		$this->TITLE = $title;
	}
	/**
	 * Обработка страницы
	 */
	public function response() {
		$this->template->main(
			$this->getTitle(),
			\modules\Module::load("admin")->getVersion(),
			core_version
		);
	}
	public function Block_warning($func,&$c){
		if ($this->WARNING != null)
			$c->$func($this->WARNING);
	}
	/**
	 * Установить сплывающее сообщение
	 */
	public function setWarning($message) {
		$this->WARNING = $message;
	}
	/**
	 * Блок страницы навигации
	 * Здесь выводятся родители данной страницы
	 * @param $func - Название функции блока в шаблоне
	 * @param $c - Класс шаблона
	 */
	public function Block_path($func,&$c) {
		foreach ($this->PATH->toArray() as $page) {
			$c->$func(
				LOCALE_PREFIX.$page['link'],
				$page['title']
			);
		}
	}
	/**
	 * Блок меню
	 * Здесь выводятся список страниц из меню
	 * @param $func - Название функции блока в шаблоне
	 * @param $c - Класс шаблона
	 */
	public function Block_menu($func,&$c) {
		$module = \modules\Module::load("admin");
		$menu = $module->listMenu();
		foreach ($menu as $item) {
			$c->$func(
				LOCALE_PREFIX.$item['link'],
				$item['name']
			);
		}
	}
	/**
	 * Блок контента
	 * Здесь выводятся сама страница
	 * @param $func - Название функции блока в шаблоне
	 * @param $c - Класс шаблона
	 */
	public function Block_content(){
		foreach($this->contents as &$i) {
			$function = &$i['function'];
			if ($i['param'])
				$i['class']->$function($i['param']);
			else
				$i['class']->$function();
		}
	}
	/**
	 * Блок скриптов
	 * Здесь выводятся все скрипты страницы
	 * @param $func - Название функции блока в шаблоне
	 * @param $c - Класс шаблона
	 */
	public function Block_script(){
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
	 * @param $function - Название функции блока в шаблоне
	 * @param $class - Класс шаблона
	 * @param $param - Параметры функции шаблона
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
	 * @param $function - Название функции блока в шаблоне
	 * @param $class - Класс шаблона
	 * @param $param - Параметры функции шаблона
	 */
	public function addScripts(&$class,$function,$param=false) {
		$this->scripts[] = array(
			'class'=>&$class,
			'function'=>$function,
			'param'=>$param
		);
	}
}
