<?php
namespace SCC\Console\Module;
use \SCC\Console\ConsoleCommandFactory;
/**
 * Выводит активные модули
 */
class ListCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {
		$moduleStorage = \SCC\moduleStorage();
		$modules = $moduleStorage->list();
		if (count($modules) > 0) {
			echo "Список активных модулей:\n";
		} else {
			echo "Не найдено ни одного активного модуля\n";
		}
		foreach ($modules as $name => $module) {
			echo "- ".$name." [".$module->getPath()."]\n";
		}
	}
}