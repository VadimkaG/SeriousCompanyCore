<?php
namespace modules\admin;
use \XMLnode;
class PopupWindow extends \XMLnode {
	private $windowContent;
	public function __construct($hidden = true) {
		parent::__construct("div");
		$this->addClass("overlay-popup");
		if ($hidden == true)
			$this->addClass("hidden");
		$wrapper = $this->addChild((new XMLnode("div"))->addClass("popup"));
		$wrapper->addChild(
			(new XMLnode("button"," "))
				->addClass("close")
				->setAttr("title","Закрыть")
				->setAttr("onclick","$(this).closest('.overlay-popup').hide();")
		);
		$this->windowContent = $wrapper->addChild(new XMLnode("div"));
	}
	/**
	 * Получить контент окна
	 * @return \XMLnode
	 */
	public function getWindowContent() {
		return $this->windowContent;
	}
}