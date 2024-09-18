<?php
namespace SCC;
class State {
	private string $fileName;
	private ?array $storage;


	const FILE_SUFFIX = "state";

	public function __construct(string $name, $parent = "") {
		if (is_array($parent)) {
			$parent = implode("/",$parent);
		} elseif (!is_string($parent)) {
			throw new \InvalidArgumentException('$parent must be array or string');
		}
		$this->fileName = (strlen($parent)>0?$parent."/":"").$name;
		$this->storage = null;
	}
	/**
	 * Получить относительный путь к файлу
	 * @return string
	 */
	public function getFileName(): string {
		return $this->fileName;
	}
	/**
	 * Получить путь к файлу без суфикса
	 * @return string
	 */
	public function getDirPath(): string {
		return ROOT."/".CONFIGS."/".$this->getFileName();
	}
	/**
	 * Получить путь к файлу
	 * @return string
	 */
	public function getFilePath(): string {
		return $this->getDirPath().".".static::FILE_SUFFIX;
	}
	/**
	 * Прочитать данные
	 * @return $this
	 */
	public function read(): State {
		$filePath = $this->getFilePath();
		if (file_exists($filePath)) {
			$this->storage = unserialize(file_get_contents($filePath));
			if (!is_array($this->storage))
				$this->storage = [];
		} else {
			$this->storage = [];
		}
		return $this;
	}
	/**
	 * Существуют ли сохраненные данные
	 * @return bool
	 */
	public function exists(): bool {
		$filePath = $this->getFilePath();
		return file_exists($filePath) && !is_dir($filePath);
	}
	/**
	 * Существует ли пакет
	 * 
	 * @return bool
	 */
	public function existsPackage(): bool {
		$path = $this->getDirPath();
		return file_exists($path) && is_dir($path);
	}
	/**
	 * Сохранить данные
	 * @return $this
	 */
	public function save(): State {
		$filePath = $this->getFilePath();
		$dir = dirname($filePath);
		if (!file_exists($dir))
			mkdir(dirname($filePath),0777,true);
		file_put_contents($filePath,serialize($this->storage));
		return $this;
	}
	/**
	 * Удалить данные
	 */
	public function delete(): void {
		if ($this->exists()) {
			unlink($this->getFilePath());
		}
	}
	/**
	 * Получить данные как массив
	 * @return array
	 */
	public function asArray(): array {
		if ($this->storage === null)
			$this->read();
		return $this->storage;
	}
	/**
	 * Получить значение
	 * @param $alias - Псевданим значения
	 * @param $default - Значение по умолчанию
	 * @return ???
	 */
	public function get(string $alias, $default = null) {
		if ($this->storage === null)
			$this->read();
		$aliases = explode(".",$alias);
		$index = &$this->storage;

		$maxDepth = count($aliases);
		$curDepth = 0;

		foreach ($aliases as $i) {

			if (!isset($index[$i])) {
				return $default;
			}

			if ($curDepth === $maxDepth-1) {
				return $index[$i];
			} elseif (is_array($index[$i])) {
				$index = &$index[$i];
			} else {
				return $default;
			}

			$curDepth++;
		}
		return $default;
	}
	/**
	 * Установить значение
	 * @param $alias - Псевданим значения
	 * @param $data - Данные значения
	 * @return $this
	 */
	public function set(string $alias, $data): State {
		if ($this->storage === null)
			$this->read();
		$aliases = explode(".",$alias);
		$index = &$this->storage;

		$maxDepth = count($aliases);
		$curDepth = 0;

		foreach ($aliases as $i) {
			if ($curDepth === ($maxDepth-1)) {
				$index[$i] = $data;
			} else {
				$index = &$index[$i];
			}

			$curDepth++;
		}
		return $this;
	}
	/**
	 * Удалить значение
	 * @param $alias - Псевданим значения
	 * @return $this
	 */
	public function unset(string $alias): State {
		if ($this->storage === null)
			$this->read();
		$aliases = explode(".",$alias);
		$index = &$this->storage;

		$maxDepth = count($aliases);
		$curDepth = 0;

		foreach ($aliases as $i) {
			if ($curDepth === ($maxDepth-1)) {
				unset($index[$i]);
			} else {
				$index = &$index[$i];
			}

			$curDepth++;
		}
		return $this;
	}
	/**
	 * Установить данные
	 * @param $data
	 * @return $this
	 */
	public function setData(array $data): State {
		$this->storage = $data;
		return $this;
	}
	/**
	 * Создать дочерний экземпляр
	 * @param $name - псевданим для дочернего элемента
	 * @return State
	 */
	public function createChild(string $name): State {
		return new State($name,$this->getFileName());
	}
}