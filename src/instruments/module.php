<?PHP
namespace modules;
class Module {
	const VERSION = '0.1';
	protected $config;
	public function __construct(){
		$this->config = &$GLOBALS['config'];
	}
	public function install(&$db,&$tn){return false;}
	public function init(){return false;}
	public function getVersion(){return $this::VERSION;}
}?>
