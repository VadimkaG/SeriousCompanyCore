<?php
namespace modules\admin;
class Table extends \XMLnode {
	
	public function __construct() {
		parent::__construct("ul");
		$this->addClass("body-table");
	}
	/**
	 * Добавить строку
	 */
	public function addLine($key = null) {
		return $this->addChild(new TableLine(),$key);
	}
	/**
	 * Получить ячейку
	 */
	public function getCell($line,$cell) {
		if ( !( $this->hasChild($line) ) ) $this->addLine($line);
		else if (!$this->hasChild($cell)) return ( $this->getChild($line)->addCell("",$cell));
		else return $line->getChild($cell);
	}
}
class TableLine extends \XMLnode {
	
	public function __construct() {
		parent::__construct("li","");
		$this->addClass("line");
	}
	/**
	 * Добавить ячейку
	 */
	public function addCell($content = "",$key = null) {
		return $this->addChild(new \XMLnode("div",$content), $key)->addClass("cell");
	}
}
