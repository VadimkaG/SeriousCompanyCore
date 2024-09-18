<?php
namespace SCC\Console\Module;
use \SCC\Console\ConsoleCommandFactory;
require_once(ROOT_CORE."/ModuleStorage.php");
/**
 * Ищет и выводит всевозможные модули по указанному пути
 */
class ListAllCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {
		$moduleStorage = new \SCC\ModuleStorage();
		$modules = $moduleStorage->findAll();
		if (count($modules) > 0) {
			echo "Список найденных модулей:\n";
		} else {
			echo "Не найдено ни одного модуля\n";
		}
		foreach ($modules as $name => $path) {
			echo "- ".$name." [".$path."]\n";
		}
	}
}