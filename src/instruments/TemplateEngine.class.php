<?PHP
namespace page;
class TemplateEngine {
	private $className;
	private $version;
	private $template_path;
	private $html_path;
	private $BLOCK_PREFIX_LEN;
	private $BLOCK_SUFFIX_LEN;
	private $BLOCK_PREFIX = '<!--b_';
	private $BLOCK_SUFFIX = '_-->';
	private $BLOCK_PREFIX_CLOSED = '<!--/b_';
	private $VAL_PREFIX = '<!--v_';
	private $VAL_SUFFIX = '_-->';
	private $locale;
	public function __construct($path) {
		$template_config = &$GLOBALS['config']['template_config'];
		$this->locale = &$GLOBALS['config']['locale'];
		if (is_array($template_config) && count($template_config) > 0) {
			$this->BLOCK_PREFIX = $template_config['block_prefix'];
			$this->BLOCK_SUFFIX = $template_config['block_suffix'];
			$this->BLOCK_PREFIX_CLOSED = $template_config['block_prefix_closed'];
			$this->VAL_PREFIX = $template_config['value_prefix'];
			$this->VAL_SUFFIX = $template_config['value_suffix'];
		}
		if (
				trim($this->BLOCK_PREFIX) == "" ||
				trim($this->BLOCK_SUFFIX) == "" ||
				trim($this->BLOCK_PREFIX_CLOSED) == "" ||
				trim($this->VAL_PREFIX) == "" ||
				trim($this->VAL_SUFFIX) == ""
			) fatalError('Префикс или суффикс блока не могут быть пустыми','Ошибка настроек шаблона');
		$this->BLOCK_PREFIX_LEN = strlen($this->BLOCK_PREFIX);
		$this->BLOCK_SUFFIX_LEN = strlen($this->BLOCK_SUFFIX);
		
		// Обрабатываем путь к фалу
		$path = strtolower(str_replace('.','/',$path));
		
		// Имя класса шаблона
		$this->className = 'Template'.translate($path,true,array('.'=>'_','-'=>'_'));
		
		// Путь к обработанному шаблону шаблону
		$this->template_path = c_templates.'/'.str_replace('/','.',$path).'.tpl.php';
		
		// Путь к html шаблону
		$this->html_path = templates.'/'.$path.'.html';
		
		if ($this->locale != "" && file_exists(locales)) {
			if (file_exists(locales."/".$this->locale."/default.json")) {
				$words = json_decode(file_get_contents(locales."/".$this->locale."/default.json"),true);
				if (is_array($words))
					foreach ($words as $key=>$word) {
						if (!defined("msg_".(string)$key))
							define("msg_".(string)$key,(string)$word);
					}
			}
			if (file_exists(locales."/".$this->locale."/".$path.".json")) {
				$words = json_decode(file_get_contents(locales."/".$this->locale."/".$path.".json"),true);
				if (is_array($words))
					foreach ($words as $key=>$word) {
						if (!defined("msg_".(string)$key))
						define("msg_".(string)$key,(string)$word);
					}
			}
		}
	}
	protected function finder(&$template, &$Blocks) {
		if (!is_string($template)) throw new \Exception('$template must be string');
		$Pos = 0;
		
		$vals = array();
		preg_match_all('/'.$this->VAL_PREFIX.'(.+?)'.$this->VAL_SUFFIX.'/su',$template,$vals);
		
		foreach ($vals[1] as $val) {
			$template = str_replace($this->VAL_PREFIX.$val.$this->VAL_SUFFIX,'<? if (isset($v["'.$val.'"]))echo $v["'.$val.'"]; else trigger_error("Значение \"'.$val.'\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>',$template);
		}
		
		$Pos=0;
		$founded_blocks = array();
		while (($Pos=strpos($template,$this->BLOCK_PREFIX,$Pos))!==false) {
			$EndInd=strpos($template,$this->BLOCK_SUFFIX,$Pos);
			if (($Pos+$this->BLOCK_PREFIX_LEN)!=$EndInd && $EndInd != false) {
				$founded_blocks ++;
				$BlockName=substr($template,$Pos+$this->BLOCK_PREFIX_LEN,$EndInd-($Pos+$this->BLOCK_PREFIX_LEN));
				$BlockEnd = $this->BLOCK_PREFIX_CLOSED.$BlockName.$this->BLOCK_SUFFIX;
				$lenSBlock = ($Pos+$this->BLOCK_PREFIX_LEN+strlen($BlockName)+$this->BLOCK_SUFFIX_LEN);
				$lenEBlock = strlen($BlockEnd);
				$EndIndex=strpos($template,$BlockEnd,$lenSBlock);
				if ($EndIndex == false) fatalError('Для одного из блоков не найдено закрывающее выражение','Ошибка блока шаблона');
				
				$Blocks[] = array(
					$BlockName,
					substr($template,$lenSBlock,$EndIndex-$lenSBlock)
				);
				end($Blocks);
				$curblock = key($Blocks);
				
				//if ($Blocks[$curblock] == false) break;
				
				$founded_blocks[$curblock] = &$Blocks[$curblock];
				
				$template = substr($template,0,$Pos).'<?$this->c->callBlock($this,"block'.$curblock.'","'.$BlockName.'",$d);?>'.substr($template,$lenSBlock+strlen($Blocks[$curblock][1])+$lenEBlock);
			}
		}
		foreach ($founded_blocks as &$block) {
			$this->finder($block[1],$Blocks);
		}
	}
	
	protected function proc($template) {
		ini_set("max_execution_time", "500");
		ini_set("memory_limit", "128M");
		$template = file_get_contents($template);
		$blocks = array();
		$this->finder($template,$blocks);
		
		$content = "<?//".$this->version."\nclass ".$this->className." {\n\tprivate \$c;\n";
		$content .= "\tpublic function __construct(\$c) {\n\t\t"
		         ."if (!(\$c instanceof \page\Container)) throw \InvalidArgumentException('\$c must be \page\Container');\n\t\t\$"
		         ."this->c = \$c;\n\t"
		         ."}\n";
		$content .= "\tpublic function getBlockName(\$name){switch(\$name){";
		foreach ($blocks as $key=>&$block) {
			$content .= 'case "'.$block[0].'":return "'.$key.'";break;';
		}
		$content .= "default:return '';}}\n";
		$content .= "\tpublic function Template_Main(\$v=array(),&\$d=null) {\n?>".$template."<?\n}\n";
		foreach ($blocks as $key=>&$block) {
			$content .= "\tpublic function block".$key."(\$v=array(),&\$d=null) {?>\n".$block[1]."<?\n\t}\n";
		}
		$content .= '}';
		
		return $content;
	}
	
	public function getClassname() {
		return $this->className;
	}
	
	public function getTemplatePath() {
		return $this->template_path;
	}
	
	public function getHtmlPath() {
		return $this->html_path;
	}
	
	public function exists() {
		if (file_exists($this->html_path)) return true;
		else false;
	}
	
	public function load(&$container) {
		
		if (!file_exists($this->html_path)) fatalError('Файл шаблона "'.$this->html_path.'" не найден');
		$needCompile = true;
		$this->version = filemtime($this->html_path);
		
		if (file_exists($this->template_path)) {
			$f = fopen($this->template_path, "r");
			$version = fgets($f);
			fclose($f);
			if ('<?//'.$this->version."\n" == $version)
				$needCompile = false;
		}
		
		if ($needCompile) {
			if (
				file_put_contents($this->template_path,$this->proc($this->html_path)) == false
			) fatalError('Не удалось записать файл в '.$this->template_path.'','Ошибка компилирования шаблона');
		}
            
		if (!($container instanceof \page\Container)) throw new \Exception('$container must be \page\Container');
		if (!file_exists($this->getTemplatePath())) fatalError('Файл шаблона "'.$this->getTemplatePath().'" не найден');
		require_once($this->getTemplatePath());
		$className = $this->getClassname();
		if (!class_exists($className)) fatalError('Класс "'.$this->getClassname().'" не найден');
		return new $className($container);
	}
}
