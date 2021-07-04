<?php
namespace stored_object;
class Condition {
	private $condition;
	private $items;
	private $nodes;
	private $inBrackets;
	public function __construct(string $condition,bool $inBrackets = false) {
		switch (strtolower($condition)) {
			case "o":
			case "or":
				$this->condition = "OR";
				break;
			default:
				$this->condition = "AND";
		}
		$this->items = array();
		$this->nodes = array();
		$this->inBrackets = $inBrackets;
	}
	/**
	 * Добавить условие
	 * @param $item - Имя поля
	 * @param $operator - Оператор
	 * @param $value - Значение (тип определяется автоматически: string, integer, array)
	 * @param $raw - Установить в true, еесли необходимо $value передать в чистом виде
	 */
	public function add(string $item,$value,string $operator="=",bool $raw=false) {
		if ($operator == "!=") $operator = "<>";
		if (!$raw) $value = Condition::getField($value);
		$this->items[] = array(
			'operator'=>$operator,
			'item'=>$item,
			'value'=>$value
		);
	}
	/**
	 * Добавить еще одну ноду.
	 * @param $condition - AND или OR
	 * @param $inBrackets - Использывать ли скобки
	 * @return \databaseWhereNode
	 */
	public function addNode(string $condition = "AND", bool $inBrackets=true){
		return $this->nodes[] = new Condition($condition,$inBrackets);
	}
	/**
	 * Получить всех наследников
	 * @return array( \databaseWhereNode )
	 */
	public function getNodes() {
		return $this->nodes;
	}
	/**
	 * Получить получившийся SQL
	 * @return string
	 */
	public function getSQL() {
		if (count($this->items) < 1 && count($this->nodes) < 1) return '';
		$str = '';
		if ($this->inBrackets)
			$str = '(';
		$isFirst = true;
		foreach ($this->items as $item) {
			if ($isFirst)
				$isFirst = false;
			else
				$str .= ' '.$this->condition.' ';
			$str .= $item['item'].' '.$item['operator'].' '.$item['value'];
		}
		foreach ($this->nodes as $node) {
			if ($str != '(' && $str != '')
				$str .= ' '.$this->condition.' ';
			$str .= $node->getSQL();
		}
		if ($this->inBrackets)
			$str .= ')';
		return $str;
	}
	/**
	 * Функция автоопределения переменной
	 * @param $field - 
	 * @return \databaseWhereNode
	 */
	public static function getField($field) {
		if (is_string($field))
			$field = "'".str_replace([ "'", "\\" ],[ "''", "\\\\" ],$field)."'";
		elseif (is_bool($field))
			$field = $field?1:0;
		else if (is_array($field)) {
			$str = '(';
			foreach ($field as $inner) {
				if ($str != '(') $str .= ',';
				$str .= Condition::getField($inner);
			}
			$field = $str.')';
			unset($str);
		} elseif (is_null($field))
			$field = "null";
		elseif ($field instanceof Connection)
			$field = $field->id();
		else
			$field = (string)$field;
		return $field;
	}
	public function __toString() {
		return $this->getSQL();
	}
}