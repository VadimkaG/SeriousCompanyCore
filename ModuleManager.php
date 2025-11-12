<?php
namespace SCC;
/**
 * Класс необходимый, для управления модулем
 */
class ModuleManager extends Module {

	private $info;

	public function __construct(\SCC\Module $module) {
		$this->state = $module->state;
		$this->name = $module->name;
	}
	/**
	 * Получить состояние модуля
	 */
	public function getState(): \SCC\State {
		return $this->state;
	}
	/**
	 * Установить путь к модулю
	 * 
	 * @param $path - Путь относительно ROOT
	 */
	public function setPath(string $path) {
		$this->state->set("path",$path);
		$this->state->save();
	}
	/**
	 * Получить информацию о модуле
	 * @return array
	 */
	public function getInfo():array {
		if (isset($this->info)) return $this->info;
		$this->info = json_decode(file_get_contents($this->getPathAbsolute()."/module.json"),true);
		return $this->info;
	}
	/**
	 * Найти все файлы с классами внутри указанного пути
	 * 
	 * @param $path - Путь, по которому производится поиск
	 * @param $parentDir - Имя директории, в которой проверяются классы
	 * @param $parentClass - Имя родителя, от которого должен наследоваться класс
	 * 
	 * @return array( string $className => string $classUri )
	 */
	protected function findClasses(string $path, string $parentDir, ?string $parentClass = null): array {

		$modulePath = $this->getPath();
		$modulePathLen = strlen($modulePath);

		$parentDirLen = strlen($parentDir)+2;

		$files = scandir(ROOT."/".$path);
		$services = [];
		foreach ($files as $file) {

			if ($file === "." || $file === "..")
				continue;

			$pathFile = $path."/".$file;
			$pathFileAbs = ROOT."/".$path."/".$file;
			$baseName = basename($pathFile);


			if (is_dir($pathFileAbs)) {
				$services = array_merge($services,$this->findClasses($pathFile,$parentDir));
				continue;
			}

			if (strlen($pathFileAbs) <= 4 || substr($pathFileAbs, -4) !== ".php")
				continue;

			require_once($pathFileAbs);

			if (strlen($pathFile) < $modulePathLen || substr($pathFile, 0, $modulePathLen) !== $modulePath) 
				continue;

			$classUri = substr($pathFile, $modulePathLen,-4);
			$className = str_replace("/","\\",$this->getName().$classUri);
			$classUri = str_replace("_","-",$this->getName()).":".substr($classUri,1).".php";
			if (class_exists($className)) {

				if ($parentClass !== null && !is_a($className, $parentClass,true))
					continue;

				$services[] = [
					"class" => $className,
					"path" => $classUri
				];
			}
		}
		return $services;
	}
	/**
	 * Установить все классы, которые находятся в модуле
	 */
	public function installModuleServices(): void {

		if (strlen($this->getPath()) <= 0)
			throw new \Exception("module is virtual");

		// Устанавливаем сервисы
		$pathServices = $this->getPath()."/Services";
		if (file_exists(ROOT."/".$pathServices) && is_dir(ROOT."/".$pathServices)) {
			$services = $this->findClasses($pathServices,"Services");

			foreach ($services as $service) {
				if (!class_exists($service["class"]))
					throw new \Exception('Class "'.$service["class"].'" not exists');
				$reflection = new \ReflectionClass($service["class"]);
				$attribute = $reflection->getAttributes(\SCC\ServiceInfo::class);
				if (count($attribute) > 0) {
					$attribute = reset($attribute);
					$sericeAliases = $attribute->getArguments();
					foreach ($sericeAliases as $alias) {
						$state = \SCC\state($alias,"services");
						$state->set("path",$service["path"]);
						$state->set("class",$service["class"]);
						$state->save();
					}
				}
			}
		}

		// Устанавливаем события
		$pathServices = $this->getPath()."/Events";
		if (file_exists(ROOT."/".$pathServices) && is_dir(ROOT."/".$pathServices)) {
			$services = $this->findClasses($pathServices,"Events","\\SCC\\Event");

			foreach ($services as $service) {
				$reflection = new \ReflectionClass($service["class"]);
				$attribute = $reflection->getAttributes(\SCC\EventInfo::class);
				if (count($attribute) > 0) {
					$attribute = reset($attribute);
					$sericeAliases = $attribute->getArguments();
					foreach ($sericeAliases as $alias) {
						$state = \SCC\state($alias,"events");
						$state->set("path",$service["path"]);
						$state->set("class",$service["class"]);
						$state->save();
					}
				}
			}
		}

		// Устанавливаем слушатели событий
		$pathServices = $this->getPath()."/EventListeners";
		if (file_exists(ROOT."/".$pathServices) && is_dir(ROOT."/".$pathServices)) {
			$services = $this->findClasses($pathServices,"EventListeners","\\SCC\\EventListener");
			foreach ($services as $service) {
				$reflection = new \ReflectionClass($service["class"]);
				$attribute = $reflection->getAttributes(\SCC\EventListenerInfo::class);
				if (count($attribute) > 0) {
					$attribute = reset($attribute);
					$sericeAliases = $attribute->getArguments();
					foreach ($sericeAliases as $alias) {
						$state = \SCC\state($alias,"events");
						$listeners = $state->get("listeners",[]);
						$listeners[] = [
							"path" => $service["path"],
							"class" => $service["class"]
						];
						$state->set("listeners",$listeners);
						$state->save();
					}
				}
			}
		}

		// Устанавливаем автозагрузочный файл
		$pathServices = $this->getPath()."/autoload.php";
		if (file_exists(ROOT."/".$pathServices)) {
			$state = \SCC\state("autoload");
			$path = $this->getName().":/autoload.php";
			$config = $state->asArray();
			$finded = false;
			foreach ($config as $item) {
				if ($item === $path) {
					$finded = true;
					break;
				}
			}
			if (!$finded)
				$config[] = $path;
			$state->setData($config);
			$state->save();
		}
	}
	/**
	 * Проверка соответствия требованиям версии ядра
	 */
	public function checkCoreVerion(): bool {
		$info = $this->getInfo();
		return $this->validateVersion($info["core"],CORE_VERSION);
	}
	/**
	 * Проверить зависимости модуля
	 * 
	 * @return массив названий модулей, которые не удовлетворены зависимостям
	 */
	public function checkDepends(): array {
		$satisfied = [];

		$info = $this->getInfo();
		if (isset($info["depends"]) && is_array($info["depends"])) {
			foreach ($info["depends"] as $depend) {
				$depend = explode(":",$depend,2);
				$module = \SCC\module($depend[0]);
				if (
					!$module->isInstalled()
					||
					(
						isset($depend[1])
						&&
						!$module->manager()->checkVersion($depend[1])
					)
				)
					$satisfied[] = $depend[0];
			}
		}

		return $satisfied;
	}
	/**
	 * Установить модуль
	 */
	public function install(): void {

		if (strlen($this->getPath()) <= 0)
			throw new \Exception("module is virtual");

		$pathFile = $this->getPathAbsolute()."/module.json";
		if (!file_exists($pathFile))
			throw new \Exception("failed to find file: ".$pathFile);

		$config = file_get_contents($pathFile);
		if ($config === false)
			throw new \Exception("failed to read file: ".$pathFile);

		$config = json_decode($config,true);
		if ($config === null)
			throw new \Exception("failed to decone json in file: ".$pathFile);

		$info = $this->getInfo();

		if (!$this->checkCoreVerion()) {
			throw new \Exception("Module required '".$info["core"]."' core versions. Current version: ".CORE_VERSION);
		}

		$satisfied = $this->checkDepends();
		if (!empty($satisfied))
			throw new \Exception("Module dependencies not satisfied. Required modules: ".implode(", ", $satisfied));

		$this->installModuleServices();

		$pathFile = $this->getPathAbsolute()."/install.php";
		if (file_exists($pathFile))
			require($pathFile);
	}
	/**
	 * Удалить модуль
	 * 
	 * TODO: Не реализовано
	 */
	public function uninstall(): void {
		// TODO По идее нужно пробежаться по тому, что устанавливали и просто убрать регистрацию
	}
	/**
	 * Проверить версию модуля
	 * 
	 * @param $versionRequired - Условия проверки версии
	 * 
	 * @return true or false
	 */
	public function checkVersion(string $versionRequired): bool {
		$info = $this->getInfo();
		return $this->validateVersion($versionRequired,$info["version"]);
	}
	/**
	 * Проверить версию
	 * 
	 * Условия разделяются пробелами
	 * 
	 * Каждая версия состоит из цифр, разделенных точкой. Количество цифр не ограничено
	 * 
	 * Каждая может быть разлелена на ветки. Напрмер stable, dev и д.р.
	 * Ветки указываются после тире и рекомендуемая версия должна четко соответствовать.
	 * Если не указано - идет проверка с веткой stable
	 * 
	 * Первым символом можно указать специальные знаки, которые зададут условие сравнивания
	 * 
	 * В числах может быть использован символ *, который будет означать любую цифру.
	 * Если в рекомендуемой версии меньше цифр, чем в реальной - лишние цифры проверены не будут,
	 * а версии считаются равными
	 * 
	 * ~
	 * Все цифры, кроме последней должны четко соответствовать.
	 * Последняя цифра проверяется по условию "Больше либо равно"
	 * 
	 * ^
	 * Первая цифра должна строго соответствовать.
	 * Остальные цифры сравниваются по условию "Больше либо равно"
	 * 
	 * >
	 * Все цифры сравниваются, по условию больше либо равно
	 * 
	 * <
	 * Все цифры сравниваются по условию меньше, либо равно
	 * 
	 * @param $versionRequired - Правила проверки версии
	 * @param $versionCurrent - Версия, которая будет проверена
	 * 
	 * @return true or false
	 */
	public static function validateVersion(string $versionRequired, string $versionCurrent): bool {
		if (strlen($versionCurrent) < 1 || strlen($versionRequired) < 1) return true;
	
		$verCurrent = explode("-",$versionCurrent,2);
		if (isset($verCurrent[1]))
			$verCurrentS = $verCurrent[1];
		else
			$verCurrentS = null;
		$verCurrent = explode(".",$verCurrent[0]);
		$verCurrentL = count($verCurrent)-1;


		$versions = explode(" ",$versionRequired);
		foreach ($versions as $ver) {
			$ver = explode("-",$ver,2);
			if (strlen($ver[0]) < 1) continue;

			// Сравниваем ветку
			if (
				isset($ver[1])
				&&
				(
					(
						$verCurrentS !== null
						&&
						$verCurrentS !== $ver[1]
					)
					||
					(
						$verCurrentS === null
						&&
						$ver[1] !== "stable"
					)
				)
			)
				return false;

			$ver = $ver[0];

			// Управляющий символ
			$comSym = substr($ver, 0,1);
			if (in_array($comSym, [ "~", "^", ">", "<" ]))
					$ver = substr($ver, 1);

			$ver = explode(".",$ver);
			$verL = count($ver)-1;

			$op = 0;
			switch ($comSym) {

				// Сравнивает последнее число
				case "~":
					if ($verL > 0) {
						if ($verL > $verCurrentL || ((int)$ver[$verL]) > ((int)$verCurrent[$verL]))
							return false;
						unset($ver[$verL]);
						$verL--;
					}
					break;

				// Больше текущей версии, но первая цифра всегда строгая
				case "^":
					if (((int)$ver[0]) !== (int)$verCurrent[0])
						return false;
					unset($ver[0]);
					$op = 1;
					break;

				// Переключает проверку на "Больше"
				case ">":
					$op = 1;
					break;

				// Переключает проверку на "Меньше"
				case "<":
					$op = 2;
			}

			// Сравнение цифр
			foreach ($ver as $key=>$i) {
				if ($key > $verCurrentL)
					return true;
				if ($i === "*")
					continue;

				switch ($op) {

				// Больше либо равно
				case 1:
					if ((int)$i > (int)$verCurrent[$key])
						return false;
					break;

				// Меньше либо равно
				case 2:
					if ((int)$i < (int)$verCurrent[$key])
						return false;
					break;

				// Равно
				default:
					if ((int)$i != (int)$verCurrent[$key])
						return false;
				}
			}
		}
		return true;
	}
}