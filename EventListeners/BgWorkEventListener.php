<?php
namespace SCC\EventListeners;
class BgWorkEventListener extends \SCC\EventListener {

	/**
	 * Слушаем событие вызова
	 */
	public function run():void {
		if ($this->check())
			$this->start();
	}
	/**
	 * Проверка на слишком частые запуски
	 * @return true = можно запускать
	 */
	public function check(): bool {
		$nextRun = $this->getState()->get("nextRun",0);
		if ($nextRun < 0) return false;
		elseif ($nextRun === 0) return true;
		return time() > $nextRun;
	}
	/**
	 * Запустить работника
	 */
	public function start(): void {

		// Записываем время следующего запуска
		$this->getState()->set("nextRun",time() + $this->getState()->get("cooldown",36180) )->save();

		$event = \SCC\event("bgwork");
		if ($event->hasListeners())
			$event->call();
	}
}