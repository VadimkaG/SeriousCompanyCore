<?PHP
namespace events;
class Event{
	private $isPost = '';
	public function __construct($isPost = true) {
		$this->isPost = $isPost;
		$this->init();
		$this->config = &$GLOBALS['config'];
		$this->page = &$GLOBALS['config']['page'];
	}
	public function getValue($value) {
		if ($this->isPost) {
			if (isset($_POST[$value])) {
				if (is_string($_POST[$value]))
					return trim($_POST[$value]);
				else
					return $_POST[$value];
			} else
				return '';
		}
		else {
			if (isset($_GET[$value])) {
				if (is_string($_GET[$value]))
					return trim($_GET[$value]);
				else
					return $_GET[$value];
			} else
				return '';
		}
	}
	public function execute() {
		if ($this->validate()) {
			if ($this->action()) $out = $this->success();
			else $out = $this->failed();
		} else $out = $this->failed();
		return $out;
	}
	public function init() {}
	public function validate() {return true;}
	public function action() {return true;}
	public function success() {return array();}
	public function failed() {return array();}
}
