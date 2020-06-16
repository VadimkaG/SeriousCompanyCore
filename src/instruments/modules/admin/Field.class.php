<?php
namespace modules\admin;
class Field extends \XMLnode {
	private $wrapper;
	
	private $label;
	
	private $display;
	private $id;
	
	public function __construct($element,$id = null,$required = false,$content = "") {
		parent::__construct($element,$content);
		$this->wrapper = null;
		$this->label = null;
		$this->display = true;
		if ($id != null)
			$this->id = (string)$id;
		else $this->id = null;
	}
	/**
	 * Установить обязательно ли поле
	 * @param $required - boolean
	 * @return $this
	 */
	public function setRequired($required = true) {
		if ($required == true && !$this->getAttr()->has("required")) 
			$this->setAttr("required","");
		elseif ($required == false && $this->getAttr()->has("required")) 
			$this->getAttr()->del("required");
		return $this;
	}
	/**
	 * Будет ли отображаться в выводе
	 * Если $boolean = true, то не будет выводиться
	 * @param $boolean - boolean
	 * @return $this
	 */
	public function setDisplay($boolean) {
		if ($boolean)
			$this->display = true;
		else
			$this->display = false;
		return $this;
	}
	/**
	 * Установить ID
	 * @param $id - Будет преобразовано в string
	 * @return $this
	 */
	public function setID($id) {
		$this->id = (string)$id;
		if ($this->label != null) $this->label->setAttr("for",$this->id);
		return $this;
	}
	/**
	 * Установить label
	 * @param $name - Контент label
	 * @return Созданная label 
	 */
	public function setLabel($name) {
		if (!is_string($this->id)) throw new AddLabelToFieldException("Нельзя добавить label в field без id");
		$this->label = (new \XMLnode("label",$name))->setAttr("for",$this->id);
		if ($this->wrapper != null) 
			$this->wrapper->addChild($this->label);
		return $this->label;
	}
	/**
	 * Получить элемент label
	 * @return var label
	 */
	public function getLabel() {
		return $this->label;
	}
	/**
	 * Имеет ли label
	 * @return boolean
	 */
	public function hasLabel() {
		if ($this->label == null) return false;
		else return true;
	}
	/**
	 * Получить wrapper
	 * @return $this
	 */
	public function getWrapper() {
		return $this->wrapper;
	}
	/**
	 * Установить wrapper
	 * @param $wrapper - Ожидается string или \XMLnode
	 * @return $this
	 */
	public function setWrapper($wrapper) {
		$this->wrapper = $wrapper;
		$wrapper->addChild($this);
		if ($this->label != null)
			$wrapper->addChild($this->label);
		return $this;
	}
	/**
	 * Установить placeholder
	 * @param $placeholder - string
	 * @return $this
	 */
	public function setPlaceholder($placeholder) {
		$this->setAttr("placeholder",$placeholder);
		return $this;
	}
	/**
	 * Получить value
	 * @return string
	 */
	public function getValue() {
		switch ($this->element) {
		case "select":
			foreach ($this->getChilds() as $child) {
				if ($child->getElement() == "option" && $child->getAttr()->has("selected")) {
					if ($child->getAttr()->has("value"))
						return $child->getAttr()->get("value");
					else return $this->getContent();
				}
			}
			break;
		default:
			if ($this->getAttr()->has("value"))
				return $this->getAttr()->get("value");
			else return null;
		}
	}
	/**
	 * Установить value
	 * @param $value - string
	 * @return $this
	 */
	public function setValue($value) {
		switch ($this->element) {
		case "textarea":
			$this->setContent($value);
			break;
		default:
			$this->setAttr("value",$value);
		}
		return $this;
	}
	/**
	 * Получить только field в xml
	 * @return string
	 */
	public function getField() {
		return parent::__toString();
	}
	/**
	 * Преобразовать в xml
	 * @return string
	 */
	public function __toString() {
		if (!($this->display)) return "";
		if ($this->wrapper != null) return $this->wrapper;
		else {
			if ($this->label != null)
				return $this->label . parent::__toString();
			else
				return parent::__toString();
		}
	}
}
class AddLabelToFieldException extends \Exception {}
