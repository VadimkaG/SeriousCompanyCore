<?php
namespace SCC;

#[Attribute]
class EventInfo {}

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