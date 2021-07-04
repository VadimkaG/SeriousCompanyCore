<?php
abstract class Event {
	// Сохраняем список эвентов, чтобы в дальнейшем не загружать по 100 раз
	private static $listeners = [];
	/**
	 * Загрузить файл в память
	 * @param $eventInd - Идентификатор события
	 */
	private static function loadFile(string $eventInd) {
		if (isset(Event::$listeners[$eventInd]))
			return;

		$filename = root."/".configs."listeners/".$eventInd.".json";
		if (file_exists($filename) && is_readable($filename))
			Event::$listeners[$eventInd] = getConfig("listeners/".$eventInd);

		// Если загрузить не выходит, то необходимо указать, что данных нет
		else
			Event::$listeners[$eventInd] = false;
	}
	/**
	 * Вызвать событие
	 * @param $eventInd - Идентификатор события
	 * @param $params - Параметры события
	 * @return array
	 */
	public static function call(string $eventInd, &$params = array()) {

		Event::loadFile($eventInd);


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
	 * @param $eventInd - Идентификатор события
	 * @param $path - Путь к классу слушателя
	 * @param $listener - Класс::Метод
	 * Например: \modules\test\TestClass::execute
	 */
	public static function regListener(string $eventInd, string $path, string $listener) {
		if (!file_exists(root."/".$path)) throw new \Exception("Файл '".$path."' не найден");

		Event::loadFile($eventInd);

		Event::$listeners[$eventInd][$listener] = $path;

		$filePath = root."/".configs."listeners/".$eventInd.".json";

		if (!file_exists($filePath) && !is_writable(root."/".configs."listeners"))
			throw new \Exception("Не получается создать файл '".configs."listeners/".$eventInd.".json"."'. Директория не доступна для записи.");
		elseif (file_exists($filePath) && !is_writable($filePath))
			throw new \Exception("Файл '".configs."listeners/".$eventInd.".json"."' не доступен для записи");

		file_put_contents($filePath, json_encode(Event::$listeners[$eventInd]));
	}
	/**
	 * Удалить слушателя
	 * @param $eventInd - Идентификатор события
	 * @param $listener - Класс::Метод
	 * Например: \modules\test\TestClass::execute
	 */
	public static function unregListener(string $eventInd, string $listener) {

		Event::loadFile($eventInd);

		$filePath = root."/".configs."listeners/".$eventInd.".json";
		if (is_array(Event::$listeners[$eventInd]) && count(Event::$listeners[$eventInd]) < 1)
			unlink($filePath);
		elseif (is_array(Event::$listeners[$eventInd])) {
			unset(Event::$listeners[$eventInd][$listener]);
		}

		if (!file_exists($filePath) && !is_writable(root."/".configs."listeners"))
			throw new \Exception("Не получается создать файл '".configs."listeners/".$eventInd.".json"."'. Директория не доступна для записи.");
		elseif (file_exists($filePath) && !is_writable($filePath))
			throw new \Exception("Файл '".configs."listeners/".$eventInd.".json"."' не доступен для записи");

		file_put_contents($filePath, json_encode(Event::$listeners[$eventInd]));
	}
}