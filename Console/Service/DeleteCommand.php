<?php
namespace SCC\Console\Service;
use \SCC\Console\ConsoleCommandFactory;
/**
 * Удаляет сервис
 */
class DeleteCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {
		if (isset($params[0])) {
			$alias = $params[0];
		} else {
			echo "Введите псевданим сервиса: ";
			$alias = fgets(STDIN);
			if (substr($alias, -1) === "\n")
				$alias = substr($alias, 0, -1);
		}

		$state = \SCC\state($alias,"services");
		if (!$state->exists()) {
			echo "Сервис не существует";
			return;
		}

		echo "Вы уверены, что хотите удалить сервис \"".$alias."\"?";
		$result = fgets(STDIN);
		if (substr($result, -1) === "\n")
			$result = strtolower(substr($result, 0, -1));

		switch ($result) {
			case "n":
			case "no":
			case "н":
			case "нет":
			return;
		}
		$state->delete();
	}
}