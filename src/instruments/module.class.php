<?php
namespace modules;
abstract class Module {
	const VERSION = 'dev';
	protected $db;
	
	private static $modules = array();
	
	public function __construct(){
		if (isset($GLOBALS["MYSQLI_CONNECTION"]))
			$this->db = &$GLOBALS["MYSQLI_CONNECTION"];
		else
			$this->db = null;
	}
	/**
	 * Загрузить модуль
	 * @param $module - название модуля
	 * @return Наследник \modules\Module
	 */
	public static function load($module) {
		if (!isset(self::$modules[$module])) {
			if (!load('modules/'.$module.'/index.class'))
				throw new ModuleLoadException("Модуль \"". $module ."\" не найден");
			$className = 'modules\\'.$module.'\\index';
			if (class_exists($className)) {
				self::$modules[$module] = new $className();
				self::$modules[$module]->init();
			} else
				throw new ModuleLoadException("Класс \"". $className ."\" не найден");
		}
		return self::$modules[$module];
	}
	/**
	 * Получить загруженные модули
	 * @return array( \modules\Module )
	 */
	public static function loadedModules() {
		return self::$modules;
	}
	/**
	 * Получить список всех модулей
	 */
	public static function allModules() {
		return array_slice(scandir(instruments . "modules"),2);
	}
	/**
	 * Настроить базу данных
	 * Переопределяемый метод
	 */
	public function install(){}
	/**
	 * Деинсталировать модуль
	 */
	public function uninstall(){}
	/**
	 * Зависимости
	 * Если указаны зависемости, то модуль
	 * не будет установлен, пока не будет
	 * установлен предыдущий
	 * Если зависимость необхадима, то нужно required установать в true
	 * Если модуль может обходиться без зависемости, но если она есть должна
	 * инициализироваться сначало она, то required установить в false
	 * @return array( module => required );
	 */
	public function depends(){ return array(); }
	/**
	 * Инициализация модуля
	 * Переопределяемый метод
	 */
	public function init(){}
	/**
	 * Версия модуля
	 * @return const string
	 */
	public function getVersion(){return $this::VERSION;}
}
class ModuleLoadException extends \Exception {}
