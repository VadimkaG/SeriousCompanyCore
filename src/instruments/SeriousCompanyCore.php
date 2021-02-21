<?php
// Идентификатор ядра
define("core","SeriousCompanyCore");
// Версия ядра
define("core_version","3.6");
/**
 * Шаблон вывода ошибки
 * @param $text - Текст ошибки
 * @param $title - Заголовок ошибки
 */
function fatalError($text,$title = 'Ошибка',$error = "500 Internal Server Error") {
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
 * @param $instruments - список инструментов
 */
function load($instruments=array()) {
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
 */
function getConfig($config_name) {
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
function Redirect($path = '') {
	if ($path == '') $path = $_SERVER['REQUEST_URI'];
	die(header('Location: '.$path));
}
/**
 * Инициализация ядра
 */
function SeriousCompany_start() {
	
	$coreErrorMessage = "Ошибка инициализации ядра";
	
	// Проверяем константы
	if (!defined("instruments")) fatalError('Константа "instruments" не инициализирована.',$coreErrorMessage);
	else if (!defined('configs')) fatalError('Константа "configs" не инициализирована.',$coreErrorMessage);
	else if (!defined('instruments')) fatalError('Константа "instruments" не инициализирована.',$coreErrorMessage);
	else if (!defined('content')) fatalError('Константа "content" не инициализирована.',$coreErrorMessage);
	else if (!defined('root')) fatalError('Константа "root" не инициализирована.',$coreErrorMessage);
	
	// Загружаем необходимые инструменты
	$res = load("database.class");
	if (!$res) fatalError('Инструмент "database.class" не удалось загрузить.',$coreErrorMessage);
	$res = load("module.class");
	if (!$res) fatalError('Инструмент "module.class" не удалось загрузить.',$coreErrorMessage);
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
	
	if (!file_exists(root."/".configs)) {
		if (is_writable(root))
			mkdir(root."/".configs);
		else fatalError('Дирректории configs не существует и нет привилегий, чтобы ее создать.',$coreErrorMessage);
	}

	// Подгружаем системный конфиг
	$configs = getConfig('system');
	if (!$configs) {
		$res = load("install");
		printInstallPage();
		die();
	}
	
	// Инициализируем базу данных
	if (!isset($configs['database']['server']) || !isset($configs['database']['user']) || !isset($configs['database']['password']) || !isset($configs['database']['basename']))
		fatalError('В конфигурации не найдены данные о базе данных.<br>Ожидаемые данные: "database:{server, basename, user, password}"',$coreErrorMessage);
	
	if (isset($configs['database']['character']))
		$db_character = $configs['database']['character'];
	else
		$db_character = null;
	$db = database::getInstance($configs['database']['server'],$configs['database']['user'],$configs['database']['password'],$configs['database']['basename'],$configs['tablenames'],$db_character);
	try {
		$db->connect();
	} catch(\databaseExceptuion $e) {
		fatalError('Не удается подключиться к базе данных: '.$e->getMessage(), $coreErrorMessage);
	}
	
	// Установка базы данных
	$site_config = $db->row_query("select * from ".$db->getTableAlias('paths'));
	if (!$site_config || isset($_GET['reinst'])) {
		$res = load("install");
		if (!$res)
			fatalError('Требуется установка базы данных, но инструмент "install" не удалось загрузить',$coreErrorMessage);
		else {
			printInstallPage();
			die();
		}
	}

	// Настройка локализации
	$uri = explode('?',$_SERVER['REQUEST_URI'],2);
	$url = explode('/',$uri[0]);
	if (trim(end($url)) == "") unset($url[count($url)-1]);
	$locale = "ru";
	if (
			isset($configs["locales"])
			&&
			count($configs["locales"]) > 0
			&&
			isset($url[1])
			&&
			in_array($url[1],$configs["locales"])
		) {
		$locale = $url[1];
		$url = array_slice($url,1);
		$url[0] = "front";
	} else if (isset($configs["locale_default"])) $locale = $configs["locale_default"];
	define("LOCALE",$locale);
	if ($locale == $configs["locale_default"])
		define("LOCALE_PREFIX","");
	else
		define("LOCALE_PREFIX","/".$locale);

	\Event::call("preloadPage");
	
	// Ищем страницу
	try {
		$path = Path::getPath($url);
	} catch (databaseExceptuion $e) {
		fatalError($e->getMessage() . "<br><br>" . $e->getLastQuery() . "<br><br>" . str_replace("\n","\n<br>",$e->getTraceAsString()),'Ошибка обработки страницы');
	} catch (Exception $e) {
		fatalError($e->getMessage() . "<br><br>" . str_replace("\n","\n<br>",$e->getTraceAsString()),'Ошибка обработки страницы');
	} catch (Error $e) {
		fatalError($e->getMessage() . " on ". $e->getFile() .":".$e->getLine()."<br><br>" . str_replace("\n","\n<br>",$e->getTraceAsString()),'Фатальная ошибка');
	}
	if ($path == null) {
		$path = Path::pageNotFound();
		if ($path == null) fatalError("","Страница не найдена","404 Not Found");
	}

	// Активируем обработчик страницы
	$executor = $path->executor();
	if ($executor == null)
		fatalError('Исполнитель страницы не найден','Ошибка обработки страницы');
	try {
		if (!$executor->validate()) {
			$page = Path::pageNotFound();
			if ($page == null) fatalError("","Страница не найдена","404 Not Found");
			$executor = $page->executor();
			if ($executor == null)
				fatalError("Упс.. Похоже исполнитель страницы ошибки не найден.","Ошибка при загрузке страницы");
		}
	} catch (PathNotValidatedException $e) {
		$page = $e->getPage();
		if ($page == null) {
			try {
			$page =  Path::pageNotFound();
			} catch (PathNotValidatedException $e) {
				fatalError(
					"Похоже чтото пошло не так.<br>".
					"Я попытался вывести страницу ошибки,".
					" но получил еще ошибку.<br>".
					"И что мне теперь делать? Давай так... ".
					"Я просто покажу ошибку и все, хорошо?<br><br>".
					"Ошибка:<br>" . $e->getMessage(),
					"Выход из безвыходной ситуации"
				);
			}
			if ($page == null) fatalError("","Страница не найдена","404 Not Found");
		}
		$executor = $page->executor();
		if ($executor == null)
			fatalError("Упс.. Похоже исполнитель страницы ошибки не найден.","Ошибка при загрузке страницы");
	}
	$executor->response();
}
