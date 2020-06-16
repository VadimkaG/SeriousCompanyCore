<?
namespace modules\admin;
use \Attributes;
class Form extends \XMLnode {
	
	private $event;
	private $eventField;
	private $id;
	
	public function __construct($id) {
		parent::__construct("form");
		require_once(__DIR__.'/Field.class.php');
		$this->setAttr("method","POST");
		$this->id = (string)$id;
		if ($this->id != null) {
			$this->event = array(
				"event"    => $this->id
			);
			$this->eventField = $this->addInput("event")->setValue($this->id)->setAttr("type","hidden");
		} else {
			$this->event = null;
		}
	}
	/**
	 * Получить метод отправки формы
	 */
	public function getMethod() {
		return $this->getAttr()->get("method");
	}
	/**
	 * Установить метод
	 */
	public function setMethod($method) {
		switch ($method) {
			case "g":
			case "get":
			case "GET":
				$this->setAttr("method","GET");
				break;
			default:
				$this->setAttr("method","POST");
				break;
		}
	}
	/**
	 * Добавить Field
	 */
	public function addField($name) {
		return $this->child[] = new Field($name);
	}
	/**
	 * Добавить кнпоку submit
	 */
	public function addSubmit($label) {
		return $this->child[] = (new Field("button"))->setContent($label)->setAttr("type","submit");
	}
	/**
	 * Добавить input
	 */
	public function addInput($name,$label = null,$type = "text",$value = null) {
		$field = $this->addField("input");
		$field->setAttr("type",$type)
		      ->setAttr("name",$name);
		$field->setID($this->id . "__" . $name);
		if ($label != null)
			$field->setLabel($label);
		if (is_string($value))
			$field->setValue($value);
		return $field;
	}
	/**
	 * Добавить textarea
	 */
	public function addTextArea($name,$label = null,$value = "") {
		$field = $this->addField("textarea");
		$field->setAttr("name",$name);
		$field->setID($this->id . "__" . $name);
		if ($label != null)
			$field->setLabel($label);
		if (is_string($value))
			$field->setValue($value);
		return $field;
	}
	/**
	 * Добавить select
	 */
	public function addSelect($name,$options = null,$label = null) {
		$field = $this->addField("select","");
		$field->setAttr("name",$name);
		$field->setID($this->id . "__" . $name);
		if (is_array($options))
		foreach ($options as $option) {
			if (is_string($option) || ($option instanceof \XMLnode))
				$field->addChild($option);
			else if (is_array($option)) {
				$o = new \XMLnode("option");
				$field->addChild($o);
				if (isset($option["#value"])) {
					$o->setContent($option["#value"]);
					unset($option["#value"]);
				}
				foreach ($option as $key=>$value) {
					$o->setAttr($key,$value);
				}
			}
		}
		if ($label != null)
			$field->setLabel($label);
		return $field;
	}
	/**
	 * Установить обработчик события при отправки формы
	 */
	public function setEvent(&$class,$function,$eventName = null) {
		if ($eventName == null && $this->id != null) $eventName = $this->id;
		elseif ($eventName == null) $eventName = $function;
		$this->event = array(
			"class"    => &$class,
			"function" => $function,
			"event"    => $eventName
		);
		$this->eventField->setValue($eventName);
		return $this->eventField;
	}
	public function validate() {
		if ($this->event == null || !isset($this->event["event"])) return false;
		if (
			(
				$this->getMethod() == "POST"
				&&
				isset($_POST["event"])
				&&
				$_POST["event"] == $this->event["event"]
			)
			||
			(
				$this->getMethod() == "GET"
				&&
				isset($_GET["event"])
				&&
				$_GET["event"] == $this->event["event"]
			)
		) {
			if (isset($this->event["class"]) && isset($this->event["function"])) {
				$fields = array();
				foreach($this->getChilds() as $child) {
					if ($child instanceof \modules\admin\Field) {
						
						if ($child->getAttr()->has("name") && ($name = $child->getAttr()->get("name")) != "event") {
							switch ($this->getMethod()) {
							
							case "POST":
								$fields[$name] = &$_POST[$name];
								break;
							
							case "GET":
								$fields[$name] = &$_GET[$name];
								break;
							}
							$child->setValue($fields[$name]);
						}
					}
				}
				$func = &$this->event["function"];
				return $this->event["class"]->$func($this,$fields);
			}
			return true;
		}
		return false;
		
	}
	/**
	 * Преобразовать в xml, добавив в таблицу
	 */
	public function toTableString() {
		$table = new Table();
		$buttons = array();
		// Обрабатываем все поля формы
		foreach ($this->child as &$field) {
			// Скрытые поля мы добавляем напрямую без всяких манипуляций
			if ( $field->getElement() == "input" && $field->getAttr()->has("type") && $field->getAttr()->get("type") == "hidden" ) {
				$table->addChild($field);
				continue;
			}
			// Кнопки мы будет выводить в самом конце
			if ( $field->getElement() == "button" ) {
				$buttons[] = &$field;
				continue;
			}
			// Добавляем все элементы в таблицу
			$line = $table->addLine();
			if ($field instanceof \modules\admin\Field) {
					
					if ($field->hasLabel())
						$line->addCell($field->getLabel())->addClass("center");
					$line->addCell($field->getField())->addClass("center");
			} else {
				$line->addCell($field)->addClass("center");
			}
		}
		// Добавляем кнопки
		if (count($buttons) > 0) {
			$line = $table->addLine();
			foreach ($buttons as $button) {
				$line->addCell($button)->addClass("center");
			}
			unset($buttons);
		}
		// В вывод кроме таблицы нам нужно добавить элемент формы с атрибутами
		return "<" . $this->element . $this->attributes . ">" . $table . "</" . $this->element . ">";
	}
}
