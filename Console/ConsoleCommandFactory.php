<?php
namespace SCC\Console;
class ConsoleCommandFactory {
	
	protected $commandName;
	protected $argv;
	protected $argc;
	protected $state;

	public function __construct(array $argv, array $parents = [ "commands" ]) {
		$this->argc = count($argv);

		if ($this->argc < 0) {
			throw new \Exception("unknown command");
		}

		$this->commandName = reset($argv);

		$this->state = \SCC\state($this->commandName,$parents);
		if (!$this->state->exists() && $this->argc > 1 && is_dir($this->state->getDirPath())) {
			$parents[] = $this->commandName;
			$this->__construct(array_slice($argv,1),$parents);
		} else {
			$this->argv = array_slice($argv, 1);
			$this->argc = count($this->argv);
		}
	}
	/**
	 * Получить аргументы команды
	 */
	public function getArgs(): array {
		return $this->argv;
	}
	/**
	 * Получить данные команды
	 */
	public function getState(): \SCC\State {
		return $this->state;
	}
	/**
	 * Установить новый обработчик команды
	 */
	public function setExecutor(string $path, ?string $class = null): void {
		$this->state->set("path",$path);
		if (!is_null($class)) {
			$this->state->set("class",$class);
		}
		$this->state->save();
	}
	/**
	 * Существует ли команда
	 * @return bool
	 */
	public function exists(): bool {
		return $this->state->exists();
	}
	/**
	 * Получить текущий пакет
	 * @return string
	 */
	public function getDirPath(): string {
		return $this->state->getDirPath();
	}
	/**
	 * Получить список команд внутри
	 */
	public function scanSubcommands(): array {
		if (!is_dir($this->getDirPath()))
			throw new \Exception("this is not a package");

		$files = scandir($this->getDirPath());
		$commands = [];
		foreach ($files as $file) {
			if ($file === "." || $file === "..") continue;
			$commands[] = pathinfo($file)["filename"];
		}
		return $commands;
	}
	/**
	 * Является ли команда пакетом
	 * @return bool
	 */
	public function isDir(): bool {
		return !$this->exists() && is_dir($this->getDirPath());
	}
	/**
	 * Создать экземпляр команды
	 */
	public function createCommand(): \SCC\Console\ConsoleCommand {
		$data = $this->state->asArray();
		if (!is_string($data["path"]))
			throw new \Exception("command path not founded");

		if (isset($data["class"]) && is_string($data["class"])) {
			$path = &$data["path"];
			$class = &$data["class"];
		} else {
			$pathArr = \SCC\url_inner_class($data["path"]);
			$path = &$pathArr["path"];
			$class = &$pathArr["class"];
		}

		require_once(ROOT."/".$path);

		if (!class_exists($class))
			throw new \Exception("Class ".$class." not exists");

		$class = new $class();
		if (!($class instanceof \SCC\Console\ConsoleCommand))
			throw new \Exception("Command must be instance of \SCC\Console\ConsoleCommand");

		return $class;
	}
	/**
	 * Выполнить команду
	 */
	public function callCommand(): void {
		$command = $this->createCommand();
		$command->onCommand($this->getArgs());
	}
	/**
	 * Создать пакет команд
	 */
	public static function createPackage(string $name): void {
		$state = \SCC\state($name,"commands");
		$dir = $state->getDirPath();
		if (!file_exists($dir))
			mkdir($dir,0777,true);
	}
}