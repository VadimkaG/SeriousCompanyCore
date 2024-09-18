<?php
if (!defined("CORE")) require_once(__DIR__."/core.php");
try {
	$event = \SCC\event("reroute");
	if ($event->hasListeners())
		$event->callFirst();
	else
		header("HTTP/1.1 404 Not Found");
} catch (\Exception $e) {
	header("HTTP/1.1 404 Not Found");
}