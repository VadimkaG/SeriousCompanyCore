<?php
namespace SCC\Console;
use \SCC\Console\ConsoleCommandFactory;
/**
 * Добавляет новую команду
 */
class StateCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $args): void {
		if (!isset($args[0])) {
			echo "Must have arguments:\n";
			echo "1.  State name\n";
			echo "2*. Value alias\n";
			echo "3*. Value\n";
			echo "4*. Value type\n";
			return;
		}
		$state = \SCC\state($args[0]);
		if (!$state->exists()) {
			echo "Файл '".$args[0]."' не существует. Создать новый? (Y/n): ";
			$result = fgets(STDIN);
			if (substr($result, -1) === "\n")
				$result = strtolower(substr($result, 0, -1));

			switch ($result) {
				case "n":
				case "no":
				case "н";
				case "нет":
					return;
			}
		}
		if (!isset($args[1])) {
			var_dump($state->asArray());
			return;
		}
		if (isset($args[2])) {
			if (isset($args[3]))
			switch (strtolower($args[3])) {
				case "i":
				case "int":
				case "integer":
					$args[2] = (int)$args[2];
					break;
				case "b":
				case "bool":
				case "boolean":
					$args[2] = (bool)$args[2];
					break;
				case "f":
				case "float":
					$args[2] = (float)$args[2];
					break;
				case "d":
				case "double":
					$args[2] = (double)$args[2];
					break;
			}
			if (isset($args[3])) {
				switch (strtolower($args[3])) {
					case "j":
					case "json":
						$state->set($args[1],json_decode($args[2],true));
						break;
					default:
						$state->set($args[1],$args[2]);
				}
			} else
				switch (strtolower($args[2])) {
				case "null":
					$state->set($args[1],null);
					break;
				case "unset":
					$state->unset($args[1]);
					break;
				case "true":
					$state->set($args[1],true);
					break;
				case "false":
					$state->set($args[1],false);
					break;
				default:
					$state->set($args[1],$args[2]);
				}
			$state->save();
		} else {
			var_dump($state->get($args[1]));
			return;
		}
	}

}