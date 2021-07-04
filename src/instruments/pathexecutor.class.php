<?php
abstract class PathExecutor {
	protected $db;
	protected $path;
	protected $pathAliases;
	protected $params;
	public function __construct(&$database,$path = null,$pathAliases = [],$params = []) {
		$this->db = $database;
		$this->path = $path;
		$this->pathAliases = $pathAliases;
		$this->params = $params;
	}
	/**
	 * Проверка страницы
	 * Переопределяемый метод
	 * @param $path - Путь страницы
	 * @return boolean
	 */
	public function validate() { return true; }
	/**
	 * Запустить обработку страницы
	 * Переопределяемый метод
	 */
	public abstract function response();
}
