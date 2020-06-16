<?php
namespace modules\admin;
class Pager extends \XMLnode {

	private $alias;
	private $elementsPerPage;

	public function __construct($alias,$perPage = 20) {
		$this->alias = (string)$alias;
		$this->elementsPerPage = (int)$perPage;
		parent::__construct("div");
		$this->addClass("pages");
	}
	/**
	 * Добавить станицу
	 * @param $name - Имя элемента
	 */
	public function addPage($name) {
		// TODO: Надо выводить не <a>, а <button> и уже через javascript подцеплять нажатие и менять страницу
		return $this->addChild((new \XMLnode("a",$name))->addClass("btn bc_blue")->setAttr("href","?page_".$this->alias."=".$name));
	}
	/**
	 * Создать определенное количество страниц
	 * @param $count - Количество страниц, которое нужно создать
	 */
	public function setPages($count) {
		$this->clearChilds();
		for ($i = 1; $i <= $count; $i++) {
			$this->addPage($i);
		}
	}
	/**
	 * Функция, создающая страницы по количеству элементов
	 * @param $count Количество элементов
	 * @param $perPages Количество элементов на одной странице
	 */
	public function setPagesByCount($count) {
		$this->setPages(ceil($count / $this->perPage() ));
	}
	/**
	 * Количество элементов на одной странице
	 */
	public function perPage() {
		return $this->elementsPerPage;
	}
	/**
	 * Получить текущую страницу
	 */
	public function current() {
		if (isset($_GET['page_'.$this->alias])) return (int)$_GET['page_'.$this->alias];
		return 1;
	}
	/**
	 * Перевести навигатор в режим подгрузки постраничного ContentAjax
	 * @param $object - \modules\admin\ContentAjax
	 */
	public function setContentAjax(&$object,$data) {
		if (!($object instanceof \modules\admin\ContentAjax)) return null;
	}
	/**
	 * Скопирует с поля $field эвент ContentAjax
	 * и приукрасит его постраничным навигатором
	 * @param $field - Поле, с которого будет копироваться ContentAjax
	 */
	public function copyContentAjax($field) {
		// TODO
	}
}