<?php
namespace modules\admin;
class Resources extends \PathExecutor {
	private $file = null;
	private $mime = null;
	public function validate() {
		if ($this->path->getParent() == null) return false;
		if ($this->path->getParent()->getParent() == null) return false;
		if ($this->path->getParent()->getParent()->getAlias() != "res") return false;
		switch ($this->path->getParent()->getAlias()) {
			case "css":
				if (file_exists(__DIR__."/template/styles/".$this->path->getAlias())) {
					$this->file = __DIR__."/template/styles/".$this->path->getAlias();
					$this->mime = "text/css";
					return true;
				}
				var_dump(__DIR__."/template/styles/".$this->path->getAlias());
				return false;
			case "js":
				if (file_exists(__DIR__."/template/scripts/".$this->path->getAlias())) {
					$this->file = __DIR__."/template/scripts/".$this->path->getAlias();
					$this->mime = "text/javascript";
					return true;
				}
				return false;
			case "img":
				if (file_exists(__DIR__."/template/pictures/".$this->path->getAlias())) {
					$this->file = __DIR__."/template/pictures/".$this->path->getAlias();
					$this->mime = mime_content_type($this->file);
					return true;
				}
				return false;
			default: return false;
		}
		return false;
	}
	public function response() {
		if ($this->file != null) {
			header('Content-type: '.$this->mime);
			readfile($this->file);
		}
	}
}
