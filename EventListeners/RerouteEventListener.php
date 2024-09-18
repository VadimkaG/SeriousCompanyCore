<?php
namespace SCC\EventListeners;
require_once(ROOT."/".ROOT_CORE."/Route.php");
class RerouteEventListener extends \SCC\EventListener {
	/**
	 * Поиск исполняемого скрипта
	 */
	public function run():void {
		$route = \SCC\Route::create($_SERVER["REQUEST_URI"]);
		if (is_object($route)) {
			$route->require();
		} else {
			$route = \SCC\Route::create("err/404");
			if (is_object($route))
				$route->require();
			else
				header("HTTP/1.0 404 Not Found");
		}
	}
}