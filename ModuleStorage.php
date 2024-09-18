<?php
namespace SCC;
/**
 * Класс, который содержит методы для вывода списка модулей
 */
class ModuleStorage {
	/**
	 * Найти все известные модули
	 * 
	 * @param $path - Путь поиска
	 * @return array( string $moduleName => string $modulePath )
	 */
	public function findAll(?string $path = null):array {
		if ($path === null) {
			$path = MODULES;
		}

		if (!file_exists(ROOT."/".$path)) return [];

		// Если модуль в текущей директории
		$dirName = basename($path);
		$modulePath = $path."/module.json";
		if ( !in_array($dirName, [ "public", "core", "cache" ]) && file_exists(ROOT."/".$modulePath) && !is_dir($modulePath)) {

			// Ищем подмодули
			$subModulesPath = $path."/modules";
			if (file_exists(ROOT."/".$subModulesPath) && is_dir(ROOT."/".$subModulesPath)) {
				$list = $this->findAll($subModulesPath);
			} else {
				$list = [];
			}

			$list[$dirName] = $path;

			return $list;
		}

		$rootLen = mb_strlen(ROOT);
		$pathNoRoot = mb_strlen($path) >= $rootLen && mb_substr($path,0,$rootLen) === ROOT?mb_substr($path,$rootLen):$path;

		// Ищем в поддиректориях
		$list = [];
		$files = scandir(ROOT."/".$path);
		foreach ($files as $file) {
			if (is_dir(ROOT."/".$path."/".$file) && strlen($file) >= 1 && substr($file,0,1) !== ".") {
				$list = array_merge($list,$this->findAll($pathNoRoot."/".$file));
			}
		}

		return $list;
	}
	/**
	 * Список модулей
	 * @return array( \SCC\Module )
	 */
	public function list(): array {
		$path = ROOT."/".CONFIGS."/modules";
		if (!file_exists($path))
			return [];

		$list = [];
		$files = scandir($path);
		foreach ($files as $file) {
			if (strlen($file) <= 5 || mb_substr($file,-5) !== ".data") continue;

			$moduleName = mb_substr($file,0,-5);
			$module = \SCC\module($moduleName);

			if ($module->isInstalled())
				$list[$moduleName] = $module;
		}
		return $list;
	}
	/**
	 * Получить модуль
	 * @param $name - Имя модуля
	 * @return \SCC\ModuleManager
	 */
	public function get(string $name) {
		return \SCC\module($name)->manager();
	}
}