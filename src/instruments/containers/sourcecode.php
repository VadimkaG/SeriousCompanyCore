<?PHP
namespace page\containers;
class Sourcecode extends \page\Container {
	public function init() {
		$this->proc(array(
			"title"=>$this->config['Title'],
			"version"=>$this->template_config['version'],
			"core_version"=>$this->config['core_version'],
			"description"=>$this->config['About'],
			"keywords"=>$this->config['KeyWords']
		));
	}
	public function validate() {
		$this->filePath = root."";
		if (isset($this->page["StartPathKey"])) {
			for ($i = $this->page["StartPathKey"]; $i <= $this->config["path_current_key"]; $i++) {
				$this->filePath .= "/".$this->path[$i];
			}
		}
		if (!file_exists($this->filePath)) return false;
		return true;
	}
	public function Block_list($func,&$c,&$data) {
		$c->$func();
	}
	public function Block_file($func,&$c,&$data) {
		if (is_file($this->filePath)) {
			$c->$func(array("file"=>htmlspecialchars(file_get_contents($this->filePath))));
		}
	}
	public function Block_list_item($func,&$c,&$data) {
		$files = scandir($this->filePath);
		foreach($files as $key=>$file) {
			if ($key < 2) continue;
			$c->$func(array(
				"link"=>$file,
				"name"=>$file
			));
		}
	}
}
