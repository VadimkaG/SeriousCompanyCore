<?php
namespace SCC;
if (!defined("CORE")) {
	require_once(__DIR__."/Event.php");
	require_once(__DIR__."/EventListener.php");
	require_once(__DIR__."/EventFactory.php");
	require_once(__DIR__."/AllowedEvent.php");
	require_once(__DIR__."/Module.php");
	require_once(__DIR__."/State.php");
	/**
	 * Идентификатор ядра
	 */
	define("CORE","SeriousCompanyCore");
	/**
	 * Версия ядра
	 */
	define("CORE_VERSION","5.0.0");
	/**
	 * Путь корневой директории сайта
	 */ 
	if (!defined("ROOT")) define("ROOT",realpath(__DIR__."/.."));
	/**
	 * Путь к файлам ядра
	 * Относительно ROOT
	 */
	if (!defined("ROOT_CORE")) define("ROOT_CORE","SeriousCompanyCore");
	/**
	 * Путь к директории модулей
	 * Относительно ROOT
	 */
	if (!defined("MODULES")) define("MODULES","modules");
	/**
	 * Путь к директории настроек
	 * Относительно ROOT
	 */
	if (!defined("CONFIGS")) define("CONFIGS","configs");
	/**
	 * Путь к директории публичного контента
	 * Относительно ROOT
	 */
	if (!defined("PUBLIC_DIR")) define("PUBLIC_DIR","public");
	/**
	 * Кэш сервисов
	 */
	$GLOBALS["serviseCACHE"] = [];
	$GLOBALS["eventCACHE"] = [];
	$GLOBALS["moduleCACHE"] = [];
	/**
	 * Распознать внутренний url и преобразовать в полный путь
	 * @param $url - внутренний url
	 * @return полный путь относителньо ROOT
	 */
	function url_inner(string $url): string {
		$comp = parse_url($url);
		if (!is_array($comp) && !isset($comp["path"]) || strlen($comp["path"]) < 2) return "";

		if (substr($comp["path"], 0,1) !== "/")
			$comp["path"] = "/".$comp["path"];

		if (!isset($comp["scheme"]))
			return $comp["path"];

		if (is_string($comp["scheme"]))
			$comp["scheme"] = str_replace("-","_",$comp["scheme"]);

		switch ($comp["scheme"]) {
			case "core":
				return ROOT_CORE.$comp["path"];
			case "cache":
				return (defined("CACHE")?CACHE:"cache").$comp["path"];
			case "public":
				return PUBLIC_DIR.$comp["path"];
			default:
				$module = \SCC\module($comp["scheme"]);
				return $module->getPath().$comp["path"];
		}
	}
	/**
	 * Распознать внутренний url и сгенерировать путь и имя класса
	 * @param $url - внутренний url
	 * @return array( path, class )
	 */
	function url_inner_class(string $url): array {
		$comp = parse_url($url);
		if (!is_array($comp) && !isset($comp["path"]) || strlen($comp["path"]) < 2) return [ "path" => "", "class" => "" ];

		if (substr($comp["path"], 0,1) !== "/")
			$comp["path"] = "/".$comp["path"];

		if (!isset($comp["scheme"]))
			return [
				"path" => substr($comp["path"],1),
				"class" => "\\SCC".str_replace("/", "\\", $comp["path"])
			];

		if (is_string($comp["scheme"]))
			$comp["scheme"] = str_replace("-","_",$comp["scheme"]);

		$pathInfo = pathinfo($comp["path"]);
		$class = ($pathInfo["dirname"]==="/"?"\\":str_replace("/", "\\", $pathInfo["dirname"])."\\").$pathInfo["filename"];

		switch ($comp["scheme"]) {
			case "core":
				return [
					"path"  => ROOT_CORE.$comp["path"],
					"class" => "\\SCC".$class
				];
			case "cache":
				return [
					"path"  => CACHE.$comp["path"],
					"class" => "\\SCC".$class
				];
			case "public":
				return [
					"path"  => PUBLIC_DIR.$comp["path"],
					"class" => "\\SCC".$class
				];
			default:
				$module = \SCC\module($comp["scheme"]);
				return [
					"path"  => $module->getPath().$comp["path"],
					"class" => "\\".$comp["scheme"].$class
				];
		}
	}
	/**
	 * Получить экземпляр хранилища данных
	 * @return State
	 */
	function state(string $name, $parent = ""): \SCC\State {
		return new State($name,$parent);
	}
	/**
	 * Указывает менеджеру модулей как правильно установить сервис.
	 * Должно содержать список псевданимов, на который регистрируется сервис
	 */
	#[Attribute]
	class ServiceInfo {}
	/**
	 * Вызов сервиса
	 * @param $name - Название сервиса
	 * @return Класс сервиса
	 */
	function service(string $name): ?object {
		if (!isset($GLOBALS["serviseCACHE"][$name])) {

			$state = state($name,"services");
			if (!$state->exists()) return null;
			$serviceConfig = $state->asArray();

			if (!isset($serviceConfig['class'])) {
				$pathArr = url_inner_class($serviceConfig['path']);
				$path = &$pathArr["path"];
			} else
				$path = url_inner($serviceConfig['path']);

			require_once(ROOT."/".$path);

			if (isset($serviceConfig['class'])) {
				$className = $serviceConfig['class'];
				$GLOBALS["serviseCACHE"][$name] = new $className();
			} elseif (class_exists($pathArr["class"])) {
				$className = $pathArr["class"];
				$GLOBALS["serviseCACHE"][$name] = new $className();
			} else {
				$GLOBALS["serviseCACHE"][$name] = true;
			}
		}

		return $GLOBALS["serviseCACHE"][$name];
	}
	/**
	 * Удалить сервис из кэша
	 * @param $name - Название сервиса
	 */
	function serviceClearFromCACHE(?string $name): void {
		if ($name === null) {
			$GLOBALS["serviseCACHE"] = [];
		} else
			unset($GLOBALS["serviseCACHE"][$name]);
	}
	/**
	 * Вызвать событие
	 * @param $alias - Псевданим события. Если не указать, то очистить весь кэш
	 * @return \SCC\EventFactory
	 */
	function event(string $alias): \SCC\EventFactory {
		if (!isset($GLOBALS["eventCACHE"][$alias])) 
			$GLOBALS["eventCACHE"][$alias] = new \SCC\EventFactory($alias);
		return $GLOBALS["eventCACHE"][$alias];
	}
	/**
	 * Очистить событие из кэша
	 * @param $alias - Псевданим события. Если не указать - очистить весь кэш
	 */
	function eventClearFromCACHE(?string $alias): void {
		if ($alias === null) {
			$GLOBALS["eventCACHE"] = [];
		} else
			unset($GLOBALS["eventCACHE"][$alias]);
	}
	/**
	 * Получить конфигурацию модуля
	 * @param $module_name - Имя модуля
	 * @return \SCC\Module
	 * @throws ModuleNotFoundException - Когда настройки модуля не найдены
	 */
	function module(string $module_name): \SCC\Module {
		if (!isset($GLOBALS["moduleCACHE"][$module_name])) {
			$GLOBALS["moduleCACHE"][$module_name] = new \SCC\Module($module_name);
		}
		return $GLOBALS["moduleCACHE"][$module_name];
	}
	/**
	 * Очистить модуль из кэша
	 * @param $module_name - Псевданим модуля. Если не указать - очистить весь кэш
	 */
	function moduleClearFromCACHE(?string $module_name): void {
		if ($module_name === null) {
			$GLOBALS["moduleCACHE"] = [];
		} else
			unset($GLOBALS["moduleCACHE"][$module_name]);
	}
	/**
	 * Получить хранилище модулей
	 * @return \SCC\ModuleStorage
	 */
	function moduleStorage(): \SCC\ModuleStorage {
		require_once(__DIR__."/ModuleStorage.php");
		return new \SCC\ModuleStorage();
	}
	/**
	 * Разрешено ли действие
	 * 
	 * @param $action - Имя действия
	 * @return bool
	 */
	function isAllowed(string|array $action, bool $default = false): bool {
		$eventFactory = event("is_allowed");
		$event = $eventFactory->getEvent();
		if (is_object($event) && $event instanceof \SCC\AllowedEvent) {
			$event->setAction($action,$default);
			$eventFactory->call();
			return $event->isAllowed();
		} else {
			if (session_status() === PHP_SESSION_NONE)
				session_start();
			return isset($_SESSION['SUPER_RIGHTS']) && $_SESSION['SUPER_RIGHTS'] === true;
		}
	}
	// Автозагрузка
	$autoloadState = state("autoload");
	if ($autoloadState->exists()) {
		$config = $autoloadState->asArray();
		if (is_array($config)) {
			foreach($config as $item) {
				if (!is_string($item)) continue;
				require_once(ROOT."/".url_inner($item));
			}
		}
	}
	unset($autoloadState);
	unset($config);

	register_shutdown_function(function () {
		$event = \SCC\event("shutdown");
		if ($event->hasListeners())
			$event->call();
	});

}