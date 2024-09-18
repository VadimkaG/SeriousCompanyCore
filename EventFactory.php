<?php
namespace SCC;
class EventFactory {
	protected string $alias;
	protected bool $loaded;
	protected $event;
	protected array $listeners;
	protected \SCC\state $state;
	protected bool $hasListeners;
	public function __construct(string $event_alias) {
		$this->alias = $event_alias;
		$this->event = null;
		$this->listeners = [];
		$this->hasListeners = false;
		$this->loaded = false;
		$this->state = \SCC\state($this->alias,"eventStates");
	}
	/**
	 * Загрузить событие
	 */
	protected function load():void {
		if ($this->event !== null) return;
		$state = \SCC\state($this->alias,"events");
		if ($state->exists()) {
			$content = $state->asArray();

			// Загружаем эвент
			if (is_array($content) && isset($content["path"])) {
				if (!isset($content["class"])) {
					$pathArr = \SCC\url_inner_class($content["path"]);
					$path = &$pathArr["path"];
				} else {
					$path = \SCC\url_inner($content["path"]);
				}
				if (strlen($path) > 0) {
					require_once(ROOT."/".$path);
					if (isset($content["class"])) {
						$class = $content["class"];
					} else {
						$class = $pathArr["class"];
					}
					$class = new $class($this->state);
					if (!($class instanceof \SCC\Event)) {
						throw new \Exception('Event must be instance of \SCC\Event');
					}
					$this->event = $class;
				}
			}

			// Загружаем слушателей
			if (isset($content["listeners"]) && is_array($content["listeners"])) {
				foreach ($content["listeners"] as $larr) {
					if (!isset($larr["path"])) continue;
					if (!isset($larr["class"])) {
						$pathArr = \SCC\url_inner_class($larr["path"]);
						$path = &$pathArr["path"];
					} else {
						$path = \SCC\url_inner($larr["path"]);
					}
					$path = &$pathArr["path"];
					if (strlen($path) > 0) {
							require_once(ROOT."/".$path);
						if (isset($larr["class"])) {
							$class = $larr["class"];
						} else {
							$class = $pathArr["class"];
						}
						$class = new $class($this->event,$this->state->createChild($class));
						if (!($class instanceof \SCC\EventListener)) {
							throw new \Exception('EventListener must be instance of \SCC\EventListener');
						}
						$this->listeners[] = $class;
					}
				}
			}
			if (count($this->listeners) > 0) $this->hasListeners = true;
			else $this->hasListeners = false;
			$this->loaded = true;
		}
	}
	/**
	 * Вызвать всех слушателей события
	 */
	public function call():void {
		if (!$this->loaded) $this->load();
		foreach ($this->listeners as $listener) {
			if ($listener->validate()) {
				$listener->run();
			} else {
				$listener->error();
			}
		}
	}
	/**
	 * Запустить первый слушатель, который пройдет валидацию
	 */
	public function callFirst():void {
		if (!$this->loaded) $this->load();
		foreach ($this->listeners as $listener) {
			if ($listener->validate()) {
				$listener->run();
				break;
			} else {
				$listener->error();
			}
		}
	}
	/**
	 * Получить событие
	 * @return \SCC\Event
	 */
	public function getEvent(): ?\SCC\Event {
		if (!$this->loaded) $this->load();
		return $this->event;
	}
	/**
	 * Получить слушателей события
	 * @return array( \SCC\EventListener )
	 */
	public function getListeners():array {
		if (!$this->loaded) $this->load();
		return $this->listeners;
	}
	/**
	 * Если ли хотябы один слушатель у события
	 */
	public function hasListeners():bool {
		if (!$this->loaded) $this->load();
		return $this->hasListeners;
	}
}