<?php
namespace SCC;
class Route {
	protected ?Route $parent;
	protected string $uri;
	protected string $alias;
	protected array $arguments;
	protected \SCC\state $state;

	protected bool $is_static;
	protected bool $is_fork;
	protected ?string $fork_alias;

	public function __construct(string $alias, ?Route $parent = null) {
		$this->alias = $alias;
		$this->parent = $parent;
		$this->arguments = [];
		$this->fork_alias = null;

		// Корневой элемент
		if ($this->parent === null) {
			if ($this->alias === "")
				$this->state = \SCC\state("routing");
			else
				$this->state = \SCC\state("routing_".$this->alias);
			$this->uri = $this->alias;
			$this->is_static = false;
			$this->is_fork = false;

		// Не корневой элемент
		} else {
			$this->arguments = $this->parent->arguments;

			// Если роут форк, значит он не статика и не может имееть статичной директории
			if (!$this->parent->isFork())
				$this->state = $this->parent->state->createChild($this->alias);
			else
				$this->state = $this->parent->parent->state->createChild($this->alias);

			// Проверка на статику роута
			if ($this->state->get("path",false) !== false) {
				$this->is_static = true;
				$this->is_fork = false;
				$this->uri = $this->parent->uri."/".$this->alias;

			// Роут не статика
			} else {
				$this->is_static = false;

				// Является ли роут форком
				if (is_string($this->parent->fork_alias)) {
					$this->is_fork = true;
					$this->uri = $this->parent->uri."/".$this->parent->fork_alias;

					// var_dump($this->parent->state);
					if (!$this->parent->state->get("fork.infinite",false)) {
						$this->arguments[$this->parent->fork_alias] = $this->alias;
						$this->state = $this->parent->state->createChild($this->parent->fork_alias);
					} else {
						$this->arguments[$this->parent->fork_alias][] = $this->alias;
						$this->state = $this->parent->state;
					}
				} else {
					$this->is_fork = false;
					$this->uri = $this->parent->uri."/".$this->alias;
				}
			}

			// Устанавливаем алиас форка, если он существует
			$this->fork_alias = $this->state->get("fork.alias",null);
		}
	}
	/**
	 * Получить конфигурацию роута
	 */
	public function getState() {
		return $this->state;
	}
	/**
	 * Получить родителя
	 */
	public function getParent(){
		return $this->parent;
	}
	/**
	 * Создать дочерний роут
	 * 
	 * @param $alias - Псевданим дочернего роута
	 * @return \SCC\Route
	 */
	public function createChild(string $alias): Route {
		return new Route($alias,$this);
	}
	/**
	 * Получить URI запроса
	 * 
	 * @return string
	 */
	public function getURI(): string {
		return $this->uri;
	}
	/**
	 * Статичен ли данный роут
	 * 
	 * @return boolean
	 */
	public function isStatic(): bool {
		return $this->is_static;
	}
	/**
	 * Является ли текущий роут форком
	 * 
	 * @return boolean
	 */
	public function isFork(): bool {
		return $this->is_fork;
	}
	/**
	 * Является ли данный роут пакетом
	 * 
	 * @return boolean
	 */
	public function isPackage(): bool {
		return $this->state->existsPackage();
	}
	/**
	 * Получить аргументы
	 * 
	 * @return array
	 */
	public function getArguments(): array {
		return $this->arguments;
	}
	/**
	 * Получить аргумент
	 * 
	 * @param $alias - Псевданим аргумента
	 * @return Зависит от самого аргумента
	 */
	public function getArgument(string $alias) {
		return isset($this->arguments[$alias])?$this->arguments[$alias]:null;
	}
	/**
	 * Валиден ли путь
	 */
	public function validate(bool $strict = true): bool {
		if (
			// Если существует конечный путь к файлу
			$this->isStatic()
			||
			// Если это форк
			(
				!$strict
				&&
				(
					$this->isFork()
					||
					$this->isPackage()
				)
			)
			||
			// Если существуют наследники
			(
				$strict
				&&
				$this->isFork()
				&&
				strlen($this->parent->state->get("fork.path","")) > 0
			)
		)
			return true;
		return false;
	}
	/**
	 * Получить путь к подгружаемому файлу
	 * 
	 * @return путь относительно корня сайта
	 */
	public function getFilePath(): string {
		return $this->isFork()?$this->parent->state->get("fork.path",""):$this->state->get("path","");
	}
	/**
	 * Подгрузить файл пути
	 */
	public function require(): void {
		$_ROUTE = $this;
		require(ROOT."/".\SCC\url_inner($this->getFilePath()));
	}
	/**
	 * Получить роут из uri
	 * 
	 * @param $uri - URI
	 * @return \SCC\Route или null, если путь не найден
	 */
	public static function create(string $uri): ?Route {
		$uri = explode("/",$uri);
		$route = new \SCC\Route(array_shift($uri));
		$keyLast = count($uri)-1;
		if ($uri[$keyLast] === "") {
			unset($uri[$keyLast]);
			$keyLast--;
		}
		if ($keyLast < 0) return null;
		foreach ($uri as $key=>$alias) {
			$route = $route->createChild($alias);
			if (!$route->validate($key===$keyLast))
				return null;
		}
		return $route;
	}
}