<?php
namespace SCC;
class AllowedEvent extends Event {
	protected bool $is_allowed = false;
	protected string|array $action = "";

	/**
	 * Установить привилегию
	 */
	public function setAction(string|array $action, bool $default = false): void {
		$this->action = $action;
		$this->is_allowed = $default;
	}
	/**
	 * Получить привилегию
	 */
	public function getAction() {
		return $this->action;
	}
	/**
	 * Установить запрет
	 */
	public function setAllowed(bool $allowed): void {
		$this->is_allowed = $allowed;
	}
	/**
	 * Разрешено ли действие
	 */
	public function isAllowed(): bool {
		return $this->is_allowed;
	}
}