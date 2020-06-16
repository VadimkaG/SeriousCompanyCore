<?php
namespace modules\admin;
class ContentAjax extends \XMLnode {
	private $event;
	public function __construct($eventAlias,$element = "div") {
		parent::__construct($element," ");
		$this->setAttr("id","content_ajax__". $eventAlias);
		$this->setAttr("alias", $eventAlias);
		$this->event = null;
		$this->buttons = array();
	}
	public function addButton($field,$data) {
		if (!is_array($data)) return null;
		$field->addClass("event__content_ajax");
		$field->setAttr("content_ajax__alias", $this->getAttr()->get("alias"));
		$field->setAttr("content_ajax__param",http_build_query($data));
	}
	public function setEvent(&$class,$function) {
		$this->event = array(
			"class"    => &$class,
			"function" => $function
		);
	}
	public function validate() {
		if (isset($_POST["event"]) && $_POST["event"] == $this->getAttr()->get("alias")) return true;
		return false;
	}
	public function event() {
		if ($this->event != null) {
			$func = &$this->event["function"];
			$fields = array();
			parse_str($_POST["param"],$fields);
			return $this->event["class"]->$func($this,$fields);
		}
	}
}