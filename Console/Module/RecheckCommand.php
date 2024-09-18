<?php
namespace SCC\Console\Module;
class RecheckCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {

		if (isset($params[0])) {
			$alias = $params[0];
		} else {
			echo "Введите псевданим модуля: ";
			$alias = fgets(STDIN);
			if (substr($alias, -1) === "\n")
				$alias = substr($alias, 0, -1);
		}

		$module = \SCC\module($alias);

		if (!$module->isInstalled()) {
			echo "Модуль не установлен\n";
			return;
		}

		$module->manager()->installModuleServices();
	}
}