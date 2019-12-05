<?PHP
namespace page;
class Container {
	protected $config;
	protected $page;
	protected $path;
	protected $template_config;
	protected $template;
	public function __construct($template) {
		if (is_string($template)) {
			$template = new \page\TemplateEngine($template);
			if (!$template->exists()) throw new \Exception('file "'.$template->getHtmlPath().'" not found');
		}
		if (!($template instanceof TemplateEngine)) throw new \Exception('$template must be \page\TemplateEngine');
		$this->template = $template;
		$this->config = &$GLOBALS['config'];
		$this->page = &$GLOBALS['config']['page'];
		$this->path = &$GLOBALS['config']['path'];
		$this->template_config = &$GLOBALS['config']['template_config'];
	}
	public function proc($arg=array(),$data=null) {
		$this->template = $this->template->load($this);
		$this->template->Template_Main($arg,$data);
	}
	public function callBlock(&$blockClass,$blockName,$container,&$data) {
		$funcName = "Block_".$container;
		if(method_exists($this,$funcName)) $this->$funcName($blockName,$blockClass,$data);
	}
	public function validate() {return true;}
	public function init() {return true;}
}
