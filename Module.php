<?php
namespace SCC;
class Module {

	protected \SCC\state $state;
	protected string $name;

	public function __construct(string $module_name) {
		if ($module_name === "") throw new \Exception("Не существувует модуля без имени.");
		switch ($module_name) {
			case "public":
			case "core":
			case "cache":
				throw new \Exception("Имя '".$module_name."' не допустимо для модуля.");
		}
		$this->name = $module_name;
		$this->state = \SCC\state($module_name,"modules");
	}
	/**
	 * Получить имя модуля
	 * 
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}
	/**
	 * Получить информацию о модуле
	 * 
	 * @param $name - Псевданим значения, которое необходимо получить
	 * 
	 * @return Зависит от того, что хранится под псевданимом
	 */
	public function get(string $name) {
		return $this->state->get($name);
	}
	/**
	 * Получить путь к модулю
	 * Если в начале есть / то путь откосительно корня сайта
	 * Если нет, то путь относительно директории модулей
	 * 
	 * @return Путь относительно ROOT
	 */
	public function getPath(): string {
		$path = $this->state->get("path","");
		if (substr($path, 0,1) !== "/") {
			$path = MODULES."/".$path;
		} else {
			$path = substr($config["path"],1);
		}
		return $path;
	}
	/**
	 * Получить абсолютный путь к модулю
	 * 
	 * @return Абсолютный путь к модулю
	 */
	public function getPathAbsolute(): string {
		return ROOT."/".$this->getPath();
	}
	/**
	 * Установлен ли модуль
	 * 
	 * @return bolean
	 */
	public function isInstalled(): bool {
		return $this->state->exists();
	}
	/**
	 * Получить класс, управляющий модулем
	 * 
	 * @return \SCC\ModuleManager( $this )
	 */
	public function manager() {
		require_once(__DIR__."/ModuleManager.php");
		return new \SCC\ModuleManager($this);
	}
}