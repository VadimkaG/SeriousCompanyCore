<?php
namespace SCC\Console\Event;
/**
 * Удаляет событие событие
 */
class DisableCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {

		if (isset($params[0])) {
			$alias = $params[0];
		} else {
			echo "Введите псевданим псевданим события: ";
			$alias = fgets(STDIN);
			if (substr($alias, -1) === "\n")
				$alias = substr($alias, 0, -1);
		}

		$state = \SCC\State($alias,"events");
		if (!$state->exists()) {
			echo "Событие с таким псевданимом не существует";
			return;
		}

		$state->delete();
	}
}
