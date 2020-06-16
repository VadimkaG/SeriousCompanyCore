<?php
namespace modules\admin;
class DataTransfer {

	private $eventAlias;
	private $fields;
	private $data;

	public function __construct($eventAlias) {
		$this->fields     = array();
		$this->data       = array();
		$this->eventAlias = (string)$eventAlias;
	}
	/**
	 * Добавить кнопку
	 * @param $button - \XMLnode
	 * @param $data - array( $alias=>$value ) - Данные
	 */
	public function addButton($button,$data) {
		if ( !($button instanceof \XMLnode) ) return null;
		$button->addClass("event__data_transfer");
		$button->setAttr("alias",$this->eventAlias);
		if (is_array($data)) {
			$this->data[] = $data;
			$button->setAttr("data-key",array_key_last($this->data));
		}
	}
	/**
	 * Установить данные
	 */
	public function setData($alias,$value) {
		$this->data[(string)$alias] = (string)$value;
	}
	/**
	 * Устноавить field
	 * @param $field - \modules\admin\Field
	 * @param $dataAlias - string - Идентифкатор значения
	 */
	public function addField($field,$dataAlias) {
		if ( !($field instanceof \modules\admin\Field) ) return null;
		$field->addClass("field__data_transfer__".$this->eventAlias."__".$dataAlias);
		$this->fields[] = $dataAlias;
	}
	/**
	 * Получить данные 
	 * @return string
	 */
	public function __toString() {
		$str = "<div id=\"data_transfer__". $this->eventAlias ."\" style=\"display:none;\">";
			foreach($this->data as $alias=>$values) {
				foreach($values as $key=>$value) {
					$str .= "<div class=\"data_transfer__". $this->eventAlias ."__". $alias ."\" alias=\"". (string)$key ."\">". (string)$value ."</div>";
				}
			}
		$str .= "</div>";
		return $str;
	}


}