<?php
namespace SCC\Console\Command;
/**
 * Удаление команды
 */
class DeleteCommand implements \SCC\Console\ConsoleCommand {
	/**
	 * {@inheritdoc}
	 */
	function onCommand(array $params): void {

		if (isset($params[0])) {
			$cmdAlias = $params[0];
		} else {
			echo "Введите псевданим команды: ";
			$cmdAlias = fgets(STDIN);
			if (substr($cmdAlias, -1) === "\n")
				$cmdAlias = substr($cmdAlias, 0, -1);
		}

		$factory = new \SCC\Console\ConsoleCommandFactory([ $cmdAlias ]);

		if (!$factory->exists()) {
			echo "Команда не найдена\n";
			return;
		}

		echo "Вы уверены, что хотите удалить комманду (y/N)?";
		$result = fgets(STDIN);
		if (substr($result, -1) === "\n")
			$result = strtolower(substr($result, 0, -1));

		switch ($result) {
			case "y":
			case "yes":
			case "д":
			case "да":
				break;

			default:
			die();
		}

		$factory->getState()->read();
		$factory->getState()->delete();

		echo "Удалить также класс команды (y/N)?";
		$result = fgets(STDIN);
		if (substr($result, -1) === "\n")
			$result = strtolower(substr($result, 0, -1));

		switch ($result) {
			case "y":
			case "yes":
			case "д":
			case "да":

				$data = $factory->getState()->asArray();

				if (!isset($data["path"])) die("Ошибка: Не верная конфигурация команды.");

				$path = url_inner($data["path"]);
				if (file_exists($path))
					unlink($path);

				break;
		}

	}
}
