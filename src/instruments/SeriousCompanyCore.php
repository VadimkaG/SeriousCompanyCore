<?php
// Идентификатор ядра
define("core","SeriousCompanyCore");
// Версия ядра
define("core_version","4.0");
/**
 * Шаблон вывода ошибки
 * @param string $text - Текст ошибки
 * @param string $title - Заголовок ошибки
 * @param string $error = "500 Internal Server Error" - Ошибка
 */
function fatalError(string $text, string $title = 'Ошибка',string $error = "500 Internal Server Error") {
	header('HTTP/1.0 '.$error, true, 500);
	echo '<!DOCTYPE html>'
		. '<html><head>'
		. '<meta charset="utf-8" />'
		. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
		. '<title>'.$title.'</title>'
		. '</head><body>'
		. '<center>'
		. '<h1>'.$title.'</h1>'
		. $text
		. '</center>'
		. '</body></html>';
	die();
}
/**
 * Загрузить инструменты
 * @param array or string $instruments - список инструментов
 * @return boolean
 */
function load($instruments = array()) {
	global $config;
	if (is_array($instruments)) {
		foreach ($instruments as $ins) {
			if (file_exists(root."/".instruments.$ins.'.php')) {
				require_once(root."/".instruments.$ins.'.php');
				return true;
			}
		}
	} else if (file_exists(root."/".instruments.$instruments.'.php')) {
		require_once(root."/".instruments.$instruments.'.php');
		return true;
	}
	return false;
}
/**
 * Получить данные из конфига
 * @param string $config_name - Имя конфига
 * @return array
 */
function getConfig(string $config_name) {
	$file_path = '/'.configs.$config_name.'.json';
	if (!file_exists(root.$file_path)) return false;
	$dec = json_decode(file_get_contents(root.$file_path),true);
	if (!$dec) fatalError('Не удалось разобрать JSON в файле "'.$file_path.'".','Ошибка загрузки конфигурации');
	return $dec;
}
/**
 * Переадресация
 * Если $path пустой, страница перезагружается
 * @param $path - Ссылка, куда нужно перенаправить
 */
function Redirect(string $path = '') {
	if ($path == '') $path = $_SERVER['REQUEST_URI'];
	die(header('Location: '.$path));
}
/**
 * Закрыть базу данныз
 */
function closeCoreDatabaseConnection() {
	if (isset($GLOBALS["MYSQLI_CONNECTION"]) && $GLOBALS["MYSQLI_CONNECTION"] != null)
		$GLOBALS["MYSQLI_CONNECTION"]->close();
}

$coreErrorMessage = "Ошибка инициализации ядра";

// Проверяем константы
if (!defined("instruments")) fatalError('Константа "instruments" не инициализирована.',$coreErrorMessage);
else if (!defined('configs')) fatalError('Константа "configs" не инициализирована.',$coreErrorMessage);
else if (!defined('instruments')) fatalError('Константа "instruments" не инициализирована.',$coreErrorMessage);
else if (!defined('content')) fatalError('Константа "content" не инициализирована.',$coreErrorMessage);
else if (!defined('root')) fatalError('Константа "root" не инициализирована.',$coreErrorMessage);

// Загружаем необходимые инструменты
$res = load("module.class");
if (!$res) fatalError('Инструмент "module.class" не удалось загрузить.',$coreErrorMessage);
$res = load("StoredObjectCondition.class");
if (!$res) fatalError('Инструмент "StoredObjectCondition.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("StoredObject.class");
if (!$res) fatalError('Инструмент "StoredObject.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("StoredObjectConnection.class");
if (!$res) fatalError('Инструмент "StoredObjectConnection.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("path.class");
if (!$res) fatalError('Инструмент "path.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("pathexecutor.class");
if (!$res) fatalError('Инструмент "pathexecutor.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("Event.class");
if (!$res) fatalError('Инструмент "Event.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("Attributes.class");
if (!$res) fatalError('Инструмент "Attributes.class.php" не удалось загрузить.',$coreErrorMessage);
$res = load("XMLnode.class");
if (!$res) fatalError('Инструмент "XMLnode.class.php" не удалось загрузить.',$coreErrorMessage);

// Проверяем существование файла конфигурации ядра
if ( ( !file_exists(root."/".configs)) || !file_exists(root."/".configs."system.json") )
	$res = false;

// Инсталируем ядро при необходимости
if ($res === false) {
	$res = load("install");
	if ($res) {
		printInstallPage();
		die();
	} else
		fatalError('Ядро нуждается в устновке, но скрипт установки не удалось загрузить.',$coreErrorMessage);
}

// Загружаем конфигурацию ядра
$configs = getConfig('system');
if (!$configs)
	fatalError('Не удалось загрузить конфигурацию ядра.',$coreErrorMessage);

// Проверяем данные, необходимые для базы данных
if (!isset($configs['database']['host']) || !isset($configs['database']['user']) || !isset($configs['database']['password']) || !isset($configs['database']['basename']))
	fatalError('В конфигурации не найдены данные о базе данных.<br>Ожидаемые данные: "database:{host, basename, user, password}"',$coreErrorMessage);

// Инициализируем базу данных
$GLOBALS["MYSQLI_CONNECTION"] = new MySQLI($configs['database']['host'],$configs['database']['user'],$configs['database']['password'],$configs['database']['basename']);
if ($GLOBALS["MYSQLI_CONNECTION"]->connect_errno)
	fatalError('Ошибка подключения к базе данных MYSQLI: '.$GLOBALS["MYSQLI_CONNECTION"]->connect_error,$coreErrorMessage);

// Закрыть базу, при остановке скрипта
register_shutdown_function('closeCoreDatabaseConnection');

// Принудительно указываем кодировку, если это необходимо
if (isset($configs['database']['charset']))
	$GLOBALS["MYSQLI_CONNECTION"]->query("SET CHARACTER SET '".$configs['database']['charset']."'");