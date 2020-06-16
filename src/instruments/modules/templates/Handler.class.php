<?php
namespace modules\templates;
use \modules\templates\Template;
abstract class Handler extends \PathExecutor {
	const TEMPLATE = "";
	protected $config;
	protected $template_config;
	protected $template;
	public function __construct($database = null,$params = "",$path = null, $template_path = null) {
		if ($database == null) $database = \database::getInstance();
		parent::__construct($database,$params,$path);
		if (is_string($template_path)) $this->template_path = $template_path;
		if ($this::TEMPLATE != "") {
			if (!file_exists(root.index::getTemplatePath().'config.json')) throw new \PathNotValidatedException("Конфиг шаблона по пути '".root.index::getTemplatePath().'config.json'."' не найден");
			$template_config = json_decode(file_get_contents(root.index::getTemplatePath().'config.json'), true);
			if ($template_config == null) throw new \PathNotValidatedException('Ошибка при чтении конфига по пути "'.root.index::getTemplatePath().'config.json'.'"');
			$template = new Template($this::TEMPLATE,$template_config);
			if (!$template->exists()) throw new \PathNotValidatedException('Файл шаблона "'.$template->getHtmlPath().'" не найден');
			if (!($template instanceof Template)) throw new \PathNotValidatedException('$template must be \modules\tempaltes\Template');
			$this->template = $template;
			$this->config = &$GLOBALS['config'];
			$this->template_config = $template_config;
		} else throw new \PathNotValidatedException('const TEMPLATE не установлена');
	}
	/**
	 * Начать вывод шаблона
	 * @param $arg - Свойства, в главном блоке шаблона
	 * @param $data - Дополнительные данные, которые могут потребоваться блокам - наследникам
	 */
	public function proc($arg=array(),$data=null) {
		$this->template = $this->template->load($this);
		$this->template->Template_Main($arg,$data);
	}
	/**
	 * Вызвать обработчик блока
	 * Передает ссылку на метод с html данного блока
	 * @param &$blockClass - Класс блока, в котором будет вызван метод "Block_".$container
	 * @param $blockName - Наименование блока
	 * @param $container - Идентификатор блока, который вызывает обработчик
	 * @param &$data - Дополнительные данные, которые могут потребоваться блокам - наследникам
	 */
	public function callBlock(&$blockClass,$blockName,$container,&$data) {
		$funcName = "Block_".$container;
		if(method_exists($this,$funcName)) $this->$funcName($blockName,$blockClass,$data);
	}
}
