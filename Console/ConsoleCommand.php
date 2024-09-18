<?php
namespace SCC\Console;
/**
 * Обработчик команды
 */
interface ConsoleCommand {
	/**
	 * Выполняет команду
	 * @param $params - Агрументы команды
	 */
	function onCommand(array $params): void;
}