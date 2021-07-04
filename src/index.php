<?php
// Вывод ошибок, если потребуется
/*if (isset($_GET['display_errors'])) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}*/
// Корневая директория сайта
define('root',$_SERVER['DOCUMENT_ROOT']);
/**
 * Путь к инструментам ядра
 * Должен быть относитель root
 */
define('instruments','instruments/');
/**
 * Путь к настройкам ядра
 * Должен быть относитель root
 */
define('configs','configs/');
/**
 * Путь к файлам кэша
 */
define('cache','cache/');
/**
 * Путь к контенту ядра
 * У ядра должны быть права на запись в эту директорию
 * Должен быть относитель root
 */
define('content','content/');
// Импортируем ядро
include_once(instruments.'SeriousCompanyCore.php');

// Ищем страницу, соответствующую текущему URL
try {
	$url = explode("?",$_SERVER["REQUEST_URI"],2);

	// Запустить событие подготовки URL, перед загрузкой страницы
	$eventParams = [ "URL" => &$url[0] ];
	\Event::call( "prepareURL", $eventParams );

	// Найти страницу по URL и получить ее обработчик
	$executor = Path::findExecutorFromURL($url[0]);
} catch (\Exception $e) {
	fatalError($e->getMessage(),"Ошибка загрузки страницы");
}

// Если страница не найдера - выводим ошибку 404
if ($executor === null)
	fatalError("","Страница не найдена","404 Not Found");

// Вызываем событие подготовки страницы
$eventParams = [ "page" => &$executor ];
\Event::call( "preparePage", $eventParams );

// Запускаем исполнитель страницы
$executor->response();