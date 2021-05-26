<?php
class Attributes {
	
	private $attributes;
	
	public function __construct() {
		$this->attributes = array();
	}
	/**
	 * Устаногвить атрибут
	 * @param $alias - Название атрибута
	 * @param $value - Значение атрибута
	 * @return $this
	 */
	public function set($alias,$value) {
		$this->attributes[$alias] = (string)$value;
		return $this;
	}
	/**
	 * Существует ли атрибут
	 * @param $alias - Название атрибута
	 * @return boolean
	 */
	public function has($alias) {
		if (isset($this->attributes[$alias])) return true;
		return false;
	}
	/**
	 * Получить значение
	 * @param $alias - Название атрибута
	 * @return string
	 */
	public function get($alias) {
		if (!isset($this->attributes[$alias])) return "";
		return $this->attributes[$alias];
	}
	/**
	 * Удалить значение
	 * @param $alias - Название атрибута
	 */
	public function del($alias) {
		unset($this->attributes[$alias]);
	}
	/**
	 * Добавить класс
	 * @return $this
	 */
	public function addClass($classes) {
		if ($this->has("class")) {
			$this->set(
				"class",
				$this->get("class") . " " . $classes
			);
		} else {
			$this->set("class",$classes);
		}
		return $this;
	}
	/**
	 * Возвращает строковое представление объекта ReflectionMethod
	 * @return string
	 */
	public function __toString() {
		$str = "";
		foreach ($this->attributes as $alias=>$attribute) {
			if ($str != "") $str .= " ";
			else $str = " ";
			$str .= $alias . "=\"" . $attribute . "\"";
		}
		return $str;
	}
}
