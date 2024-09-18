<?php
// Инициализируем ядро
if (!defined("core")) require_once(__DIR__."/core.php");

// Запускаем работу в фоне
$event = \SCC\event("bgwork");
if ($event->hasListeners())
	$event->call();