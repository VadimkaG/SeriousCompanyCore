<?php
if (!defined("CORE")) require_once(__DIR__."/core.php");
require_once(__DIR__."/Console/ConsoleCommand.php");
require_once(__DIR__."/Console/ConsoleCommandFactory.php");

use \SCC\Console\ConsoleCommandFactory;

ConsoleCommandFactory::createPackage("command");
$factory = new ConsoleCommandFactory([ "command", "add" ]);
$factory->setExecutor("core:Console/Command/AddCommand.php");