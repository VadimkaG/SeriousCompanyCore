<?php
namespace SCC;
class RouteConfigurator {
	protected string $route_uri;

	function __construct(string $route_uri) {
		$this->route_uri = $route_uri;
	}
	/**
	 * Получить объект роута
	 */
	function getRoute() {
		require_once(ROOT."/".ROOT_CORE."/Route.php");
		return \SCC\Route::create($this->route_uri);
	}
	/**
	 * Существует ли роут
	 * 
	 * @return boolean
	 */
	function isExists() {
		return $this->createRoute() !== null;
	}
	/**
	 * Создать роут
	 * @param $controller - Файл на который будет ссылаться путь
	 * @param $aliases - Динамеческие параметры
	 */
	function create(string $controller, array $aliases = []) {
		$uri = explode("/",$this->route_uri);
		$fistAlias = array_shift($uri);
		$keyLast  = count($uri)-1;
		if ($keyLast < 1) return;

		if ($fistAlias === "") {
			$state = \SCC\state("routing");
		} else {
			$state = \SCC\state("routing_".$fistAlias);
		}

		if ($uri[$keyLast] === "") {
			unset($uri[$keyLast]);
			$keyLast--;
		}
		$is_fork2 = null;
		$is_fork = false;
		foreach ($uri as $key => $value) {
			$is_fork_child = in_array($value, $aliases);

			$child = $state->createChild($value);
			$needStateSave = false;
			
			if ($is_fork_child) {
				if ($is_fork && $is_fork2)
					$state = $state->createChild($uri[$key-1]);
				$state->set("fork.alias",$value);
				$needStateSave = true;
			}
			if ($key === $keyLast) {
				if ($is_fork_child) {
					$state->set("fork.path",$controller);
					$needStateSave = true;
				} else {
					$child->set("path",$controller);
					$child->save();
				}
			}
			if ($needStateSave)
				$state->save();

			if (!$is_fork_child || ($is_fork_child && !$is_fork)) {
				$state = $child;
			}

			if ($key > 1)
				$is_fork2 = $is_fork;
			$is_fork = $is_fork_child;

		}
	}
}