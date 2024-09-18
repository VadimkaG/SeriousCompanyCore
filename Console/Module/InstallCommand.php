<?php
namespace SCC\Console\Module;
class InstallCommand implements \SCC\Console\ConsoleCommand {
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

		if ($module->isInstalled()) {
			echo "Модуль уже установлен\n";
			return;
		}

		$moduleStorage = \SCC\moduleStorage();
		$modules = $moduleStorage->findAll();

		foreach ($modules as $name => $path) {
			if ($name !== $alias) continue;

			$strModLen = strlen(MODULES);
			if (strlen($path) >= $strModLen && substr($path, 0,$strModLen) === MODULES) {
				$path = substr($path, $strModLen+1);
			} else {
				$path = "/".$path;
			}
			$module = $module->manager();
			$module->setPath($path);

			$module->install();

			return;
		}

		echo "Модуль не найден\n";
	}
}