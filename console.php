<?php
// Инициализируем ядро
if (!defined("core")) require_once(__DIR__."/core.php");
require_once(__DIR__."/Console/ConsoleCommand.php");
require_once(__DIR__."/Console/ConsoleCommandFactory.php");

if (!isset($argv) || !is_array($argv)) die("Woops... I cant find arguments");

$factory = new \SCC\Console\ConsoleCommandFactory(array_slice($argv,1));
if (!$factory->exists()) {
	if ($factory->isDir()) {
		$subCommands = $factory->scanSubcommands();
		if (count($subCommands) > 0) {
			echo "Список команд:\n";
			foreach ($subCommands as $command) {
				echo $command."\n";
			}
		} else {
			echo "Список команд пуст\n";
		}
	} else {
		echo "Команда не найдена\n";
	}
} else {
	$factory->callCommand();
}