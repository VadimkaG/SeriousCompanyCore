<?php
namespace modules\templates;
class PageResources extends \PathExecutor {
	private $filepath;
	public function validate() {
		$path_str = "";
		$c = 0;
		$parent = $this->path;
		do {
			$c++;
			if ($c > 100) die();
			if ($path_str == "")
				$path_str = $parent->getAlias();
			else
				$path_str = $parent->getAlias()."/".$path_str;
			$parent = $parent->getParent();
		} while( $parent != null && ($parent->executor() instanceof PageResources) );
		\modules\Module::load("templates");
		$this->filepath = \modules\templates\index::getTemplatePath().$path_str;
		if (file_exists(root.$this->filepath))
			return true;
		else return false;
	}
	public function response(){
		Redirect($this->filepath);
	}
}