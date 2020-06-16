<?php
namespace modules\admin;
class AjaxDatalist extends \XMLnode {
	private $event;

	public function __construct($alias) {
		parent::__construct("datalist");
		$this->setAttr("id","data_list__".$alias);
		$this->event = null;
	}
	/**
	 * Установить поле
	 * @param &$field - ссылка на поле
	 */
	public function setField(&$field) {
		if (($field instanceof \XMLnode) && $field->getElement() == "input") {
			$field->setAttr("list", $this->getAttr()->get("id"));
			$field->addClass("event__data_list");
		}
	}
	/**
	 * Установить поле
	 * @param &$class - Ссылка на класс
	 * @param $function - Метод который нужно выполнить в классе &$class
	 */
	public function setEvent(&$class,$function) {
		$this->event = array(
			"class"    => &$class,
			"function" => $function
		);
	}
	/**
	 * Запустить эвент
	 * @return array() или false в случае неудачи
	 */
	public function validate() {
		if ($this->event != null && $this->getAttr()->has("id") && isset($_POST[$this->getAttr()->get("id")])) {
			$value = &$_POST[$this->getAttr()->get("id")];
			$func = &$this->event["function"];
			return $this->event["class"]->$func($value);
		} else return false;
	}
}