<?php
namespace modules\admin;
class Pager extends \XMLnode {

	private $alias;
	private $elementsPerPage;

	public function __construct(string $alias, int $perPage = 20) {
		$this->alias = (string)$alias;
		$this->elementsPerPage = (int)$perPage;
		parent::__construct("div");
		$this->addClass("pages");
	}
	/**
	 * Добавить станицу
	 * @param $name - Имя элемента
	 * @return \XMLnode - Добавленный элемент
	 */
	public function addPage(string $name) {
		// TODO: Надо выводить не <a>, а <button> и уже через javascript подцеплять нажатие и менять страницу
		return $this->addChild((new \XMLnode("a",$name))->addClass("btn bc_blue")->setAttr("href","?page_".$this->alias."=".$name));
	}
	/**
	 * Создать определенное количество страниц
	 * @param $count - Количество страниц, которое нужно создать
	 * @return $this
	 */
	public function setPages(int $count) {
		$this->clearChilds();
		for ($i = 1; $i <= $count; $i++) {
			$this->addPage($i);
		}
		return $this;
	}
	/**
	 * Функция, создающая страницы по количеству элементов
	 * @param $count Количество элементов
	 * @return $this
	 */
	public function setPagesByCount(int $count) {
		return $this->setPages(Pager::countPages($count));
	}
	/**
	 * Получить количество страниц, из количества элементов
	 * @param $count_items Количество элементов
	 * @return int
	 */
	public function countPages(int $count_items) {
		return ceil($count_items / $this->perPage() );
	}
	/**
	 * Количество элементов на одной странице
	 * @return int
	 */
	public function perPage() {
		return $this->elementsPerPage;
	}
	/**
	 * Получить текущую страницу
	 * Получает текущую страницу из GET
	 * @return int
	 */
	public function current() {
		if (isset($_GET['page_'.$this->alias])) return (int)$_GET['page_'.$this->alias];
		return 1;
	}
}