<?php
namespace SCC;

#[Attribute]
class EventListenerInfo {}

abstract class EventListener {
	protected \SCC\state $state;
	protected ?\SCC\Event $event;
	public function __construct(?\SCC\Event $event, \SCC\State $state) {
		$this->event = $event;
		$this->state = $state;
	}
	/**
	 * Получить экземпляр события данного слушателя
	 * @return \SCC\Event or null
	 */
	public function getEvent(): ?\SCC\Event {
		return $this->event;
	}
	/**
	 * Получить сохраненные данные этого события
	 * @return \SCC\State
	 */
	public function getState(): \SCC\State {
		return $this->state;
	}
	/**
	 * Проверить условие выполнения слушателя
	 * @return Продолжить ли выполнение
	 */
	public function validate():bool {
		return true;
	}
	/**
	 * Выполнить слушатель
	 */
	public abstract function run():void;
	/**
	 * Действие при неуспешной проверки validate
	 */
	public function error():void {}
}