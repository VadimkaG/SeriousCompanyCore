<?php
namespace SCC\Console\Event;
/**
 * Добавляет новое событие
 */
class AddCommand implements \SCC\Console\ConsoleCommand {
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

		$state = \SCC\state($alias,"events");
		if ($state->exists()) {
			echo "Событие с таким псевданимом уже добавлено";
			return;
		}

		if (isset($params[1])) {
			$classPath = $params[1];
		} else {
			echo "Введите путь к класу события: ";
			$classPath = fgets(STDIN);
			if (substr($classPath, -1) === "\n")
				$classPath = substr($classPath, 0, -1);
		}

		if (strlen($classPath) > 0) {
			$pathArr = \SCC\url_inner_class($classPath);
			$path = ROOT."/".$pathArr["path"];
			$class = &$pathArr["class"];
			if (!file_exists($path)) {
				echo "Файл не существует. Создать новый? (y/N): ";
				$result = fgets(STDIN);
				if (substr($result, -1) === "\n")
					$result = strtolower(substr($result, 0, -1));

				switch ($result) {
					case "y":
					case "yes":
					case "д":
					case "да":
						$indNamespace = strripos($class,"\\",-1);
						$className = substr($class,$indNamespace+1);

						$firstSymbol = 0;
						if (substr($class,0,1) === "\\") {
							$firstSymbol = 1;
						}
						$namespace = substr($class,$firstSymbol,$indNamespace-1);
						unset($firstSymbol);

						$dirPath =  dirname($path);
						if (!file_exists($dirPath)) {
							mkdir($dirPath,0777,true);
						}
						$classContent = "<?php\nnamespace ".$namespace.";\n/**\n * New autogenerated event\n */\n#[\SCC\EventInfo('".$alias."')]\nclass ".$className." extends \SCC\Event {\n	/**\n	 * Your content here\n	 */\n}";
						file_put_contents($path,$classContent);
						break;
					default:
						return;
				}
			}

			// Проверяем существование класса
			require_once($path);
			if (!class_exists($class)) { 
				echo "Класс не существует.\n";
				return;
			}

			$state->set("path",$classPath)
			      ->set("listeners",[])
			      ->save();

		}
	}
}