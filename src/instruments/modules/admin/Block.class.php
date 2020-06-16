<?php
namespace modules\admin;
class Block {
	
	private $attributes_block;
	private $attributes_head;
	private $attributes_body;
	
	private $content;
	
	private $title;
	
	private $headerButtons;
	
	
	public function __construct($title = null) {
		$this->attributes_block = new \Attributes();
		$this->attributes_block->addClass("block");
		$this->attributes_head = new \Attributes();
		$this->attributes_head->addClass("block-head");
		$this->attributes_body = new \Attributes();
		$this->attributes_body->addClass("block-body");
		
		$this->headerButtons = array();
		
		if (is_string($title))
			$this->title = $title;
		else
			$this->title = "";
		
		$this->content = null;
	}
	/**
	 * Получить заголовок блока
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	/**
	 * Установить заголовок для блока
	 * @param $title - Новый заголовок
	 * @return \XMLnode
	 */
	public function setTitle($title) {
		return $this->title = new \XMLnode("span",$title);
	}
	/**
	 * Получить атрибуты блока
	 * @return \modules\admin\Attributes
	 */
	public function getAttr() {
		return $this->attributes_block;
	}
	/**
	 * Получить заголовка блока
	 * @return \modules\admin\Attributes
	 */
	public function getAttrHead() {
		return $this->attributes_head;
	}
	/**
	 * Получить тела блока
	 * @return \modules\admin\Attributes
	 */
	public function getAttrBody() {
		return $this->attributes_body;
	}
	/**
	 * Установить вызов функции, когда придет время для контента
	 * @param $content - Контент блока
	 * Контент будет выводится как string
	 * @return $content
	 */
	public function setContent($content) {
		return $this->content = $content;
	}
	/**
	 * Добавить кнопку в шапку
	 * @param $name Имя кнопки
	 * @return \XMLnode
	 */
	public function addHeaderButton($name) {
		return ($this->headerButtons[] = (new \XMLnode("span",$name))->addClass("btn bc_white"));
	}
	/**
	 * Получить все кнопки в шапке
	 */
	public function getHeaderButtons() {
		return $this->headerButtons;
	}
	/**
	 * Преобразовать блок в xml строку
	 */
	public function __toString() {
		$str = "<div". $this->attributes_block .">"
			."<div". $this->attributes_head .">"
			.$this->title;
		if (count($this->headerButtons) > 0) {
			$str .= "<div class=\"right\">";
			foreach ($this->headerButtons as $button) {
				$str .= $button;
			}
			$str .= "</div>";
		}
		$str .= "</div>"
			. "<div". $this->attributes_body .">". $this->content ."</div>"
			. "</div>";
		return $str;
	}
	
}
