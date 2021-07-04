<?php
namespace stored_object;
class Connection {
	protected $id;
	protected $objectType;
	protected $object;
	public function __construct(int $id, string $objectType, StoredObject $object = null) {
		$this->id = $id;
		$this->objectType = $objectType;
		$this->object = $object;
	}
	/**
	 * Получить идентификатор объекта
	 * @return int
	 */
	public function id() {
		return $this->id;
	}
	/**
	 * Получить тип объекта
	 * @return string
	 */
	public function objectType() {
		return $this->objectType;
	}
	/**
	 * Получить объект
	 * Если объект не загружен предварительно - он будет сначала загружен
	 */
	public function object() {
		if ($this->object == null && $this->id > 0)
			$this->object = $this->objectType::load($this->id);
		return $this->object;
	}
	/**
	 * Установить объект
	 * @param \Object $object
	 */
	public function set(StoredObject &$object) {
		$this->object = $object;
		if ($object !== null)
			$this->id = $object->id();
		else
			$this->id = null;
		return $this;
	}
	/**
	 * Установить идентификатор
	 * @param int $id - Новый идентификатор
	 */
	public function setId(int $id) {
		if ($id != $this->id) {
			$this->id = $id;
			$this->object = null;
		}
	}
}