<?php
abstract class PathExecutor {
	protected $db;
	protected $params;
	protected $path;
	public function __construct($database,$params = "",$path = null) {
		$this->db = $database;
		$this->params = $params;
		$this->path = $path;
	}
	/**
	 * Проверка страницы
	 * Переопределяемый метод
	 * @param $path - Путь страницы
	 * @return boolean
	 */
	public function validate() { return true; }
	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() { return ""; }
	/**
	 * Запустить обработку страницы
	 * Переопределяемый метод
	 */
	public abstract function response();
}
