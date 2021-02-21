<?php
abstract class Event {
	// Сохраняем список эвентов, чтобы в дальнейшем не загружать по 100 раз
	private static $listeners = null;
	/**
	 * Вызвать событие
	 * @param $eventInd - Идентификатор события
	 * @param $params - Параметры события
	 * @return array
	 */
	public static function call(string $eventInd, array $params = array()) {
		if (Event::$listeners == null) {
			Event::$listeners = getConfig("event_listeners");
			if (Event::$listeners == null) return false;
		}
		$return = [];
		if (isset(Event::$listeners[$eventInd]) && is_array(Event::$listeners[$eventInd]))
			foreach(Event::$listeners[$eventInd] as $event=>$path) {
				if (is_string($event) && is_string($path)) {
					if (file_exists(root."/".$path)) {
						require_once(root."/".$path);
						$arr = explode("::",$event);
						if (count($arr) == 2 && class_exists($arr[0]) && method_exists($arr[0], $arr[1])) {
							$class = &$arr[0];
							$method = &$arr[1];
							$return[] = $class::$method($params);
						}
					}
				}
			}
		return $return;
	}
	/**
	 * Зарегистрировать слушателя
	 * @param $event - Идентификатор события
	 * @param $path - Путь к классу слушателя
	 * @param $listener - Класс::Метод
	 * Например: \modules\test\TestClass::execute
	 * @param bool
	 */
	public static function regListener(string $event, string $path, string $listener) {
		if (!file_exists(root."/".$path)) return false;

		if (file_exists(root."/".configs."event_listeners.json")) {
			Event::$listeners = getConfig("event_listeners");
			if (!is_array(Event::$listeners)) return false;
		} else
			Event::$listeners = array();
		if (!isset(Event::$listeners[$event][$listener]) || Event::$listeners[$event][$listener] != $path) {
			Event::$listeners[$event][$listener] = $path;
			file_put_contents(root."/".configs."event_listeners.json", json_encode(Event::$listeners));
		}
		return true;
	}
	/**
	 * Удалить слушателя
	 * @param $event - Идентификатор события
	 * @param $listener - Класс::Метод
	 * Например: \modules\test\TestClass::execute
	 */
	public static function unregListener(string $event, string $listener) {
		if (Event::$listeners == null) {
			Event::$listeners = getConfig("event_listeners");
			if (Event::$listeners == null) return false;
		}
		if (isset(Event::$listeners[$event][$listener])) {
			unset(Event::$listeners[$event][$listener]);
			file_put_contents(root."/".configs."event_listeners.json", json_encode(Event::$listeners));
		}
	}
}