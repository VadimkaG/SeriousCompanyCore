<?php
namespace SCC;

/**
 * Указывает менеджеру модулей как правильно установить событие
 * Должно содержать список псевданимов, на который регистрируется событие
 */
#[Attribute]
class EventInfo {}

/**
 * Информация об исполняемом событии
 */
class Event {
	protected \SCC\State $state;
	public function __construct(\SCC\State $state) {
		$this->state = $state;
	}
	/**
	 * Получить сохраненные данные события
	 */
	public function getState(): \SCC\State {
		return $this->state;
	}

}