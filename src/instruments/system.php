<?PHP
/**
 * Шаблон вывода ошибки
 */
function fatalError($text,$title = 'Ошибка') {
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
 * Инициализация ядра
 */
function initCore() {
	global $config;
	$c = getConfig('loader');
	if ($c == null) fatalError('Не удалось загрузить настройки сайта.<br>Ошибка чтения настроек loader.','Критическая ошибка');
	if (!isset($c['database']['server']) || !isset($c['database']['user']) || !isset($c['database']['password']) || !isset($c['database']['basename']))
	    fatalError('В конфигурации не найдены данные о базе данных.<br>Ожидаемые данные: "database:{server, basename, user, password}"',"Критическая ошибка.");
	loadInstuments("database.class");
	loadInstuments("module");
	$config['data'] = new database($c['database']['server'],$c['database']['user'],$c['database']['password'],$c['database']['basename']);
	if (isset($c['locales']) && is_array($c['locales']))
		$config['locales'] = $c['locales'];
	else
		$config['locales'] = array();
	$config['locale'] = "";
	$config['tablenames'] = $c['tablenames'];
	$config['tables'] = &$config['tablenames'];
	if (!$config['data']->connect())
		fatalError('Не удается подключиться к базе данных: '.$config['data']->getError());
	$site_config = array();
	if (!isset($c['tablenames']['SiteConfig']))
		fatalError('В конфигурации не присутствует "tablenames.SiteConfig"');
	else {
		$site_config=$config['data']->row_query("select * from ".$c['tablenames']['SiteConfig']);
		if (!$site_config) {
			if (file_exists('install.php')) {
				if (!isset($_GET['install'])) Redirect('/?install');
				require_once('install.php');
			} else
				fatalError('Таблица настроек "'.$c['tablenames']['SiteConfig'].'" не найдена.');
		}
	}
	if ($site_config)
		while ($row = $site_config->fetch_assoc()) {
			$config[$row['option_name']]=$row['option_value'];
		}
	else fatalError('Неудалось загрузить настройки');
	$config['data']->disconnect();
	$config['core_version'] = '2.0'; // Версия ядра
	$config['modules'] = array();
	$config['pages'] = getConfig('site_scheme');
	if ($config['pages'] == null) fatalError('Не удалось загрузить структуру сайта.<br>Ошибка чтения настроек site_scheme.','Критическая ошибка');
	if (isset($c['sitePathInUrl']) && $c['sitePathInUrl'] == true)
		$uri = $_SERVER['REQUEST_URI'];
	else if (!isset($_GET['uri']))
		Redirect('/?uri=%2F');
	else 
		$uri = $_GET['uri'];
	$config['path'] = explode('/', trim(parse_url($uri, PHP_URL_PATH))); // Местоположение на сайте
	if (end($config['path']) == "") array_pop($config['path']);
	$config['page'] = array(); // Текущая страница
	
	if (
			isset($config['locales'])
			&&
			count($config['locales']) > 0
		) {
			if (
					isset($config['path'][1])
					&&
					is_string($config['path'][1])
					&&
					in_array($config['path'][1],$config['locales'])
				) {
					$config['locale'] = $config['path'][1];
					unset($config['path'][1]);
			} else {
				$config['locale'] = $config['locales'][0];
			}
	}
}
/**
 * Получить данные из конфига
 */
function getConfig($config_name) {
	$file_path = '/configs/'.$config_name.'.json';
	if (!file_exists($_SERVER['DOCUMENT_ROOT'].$file_path)) fatalError('Файл "'.$file_path.'" не найден.','Ошибка загрузки конфигурации');
	$dec = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].$file_path),true);
	if (!$dec) fatalError('Не удалось разобрать JSON в файле "'.$file_path.'".','Ошибка загрузки конфигурации');
	return $dec;
}
/**
 * Проверка авторизации пользователя
 * @return - True / False
 */
function isAuth() {
	global $optimizer;
	if (!isset($_SESSION['user_id'])) return false;
	if (isset($optimizer['isAuth'])) return $optimizer['isAuth'];
	
	$am = getModule('accounts');
	
	$optimizer['isAuth'] = false;
	if (!$am) {
		if (isset($_SESSION['user_id']))
			$optimizer['isAuth'] = true;
		else
			$optimizer['isAuth'] = false;
	} else if (isset($_SESSION['user_id'])) {
		try {
			$userUtils = $am->getAccountUtils();
			if ($userUtils->existsAccount((int) $_SESSION['user_id']))
				$optimizer['isAuth'] = true;
		} catch (\modules\accounts\AuthFailedException $e) {
			$optimizer['isAuth'] = false;
		}
	}
	return $optimizer['isAuth'];
}
/**
 * Проверка привелегии пользователя
 * @param $perm - Привилегия
 * @return - True / False
 */ 
function checkPerm($perm = 'admin') {
	global $optimizer;
	if (!isset($_SESSION['user_id'])) return false;
	if (isset($optimizer['checkPerm'][$perm])) return $optimizer['checkPerm'][$perm];
	if (!isAuth()) return false;
	$am = getModule('accounts');
	
	$optimizer['checkPerm'][$perm] = false;
	if (!$am) {
		if (isset($_SESSION['user_admin']) && $_SESSION['user_admin'] = true)
			$optimizer['checkPerm'][$perm] = true;
		else 
			$optimizer['checkPerm'][$perm] = false;
	} else {
		$userUtils = $am->getAccountUtils();
		try {
			if ($userUtils->isSetPermission((int)$_SESSION['user_id'],$perm))
				$optimizer['checkPerm'][$perm] = true;
			else
				$optimizer['checkPerm'][$perm] = false;
		} catch (\modules\accounts\AuthFailedException $e) {
			$optimizer['checkPerm'][$perm] = false;
		}
	}
	
	return $optimizer['checkPerm'][$perm];
}
/**
 * Загрузить страницу
 */
function loadPage() {
	global $config;
	
	$enested = false;
	$pages = $config['pages'];
	$currentPage = array();
	$config['page'] = array();
	/**
	 * Ошибки:
	 * 404 - Страница не найдена
	 * 403 - Доступ запрещен
	 * 601 - Не найдена файл страницы
	 * 602 - Ошибка чтения конфига
	 */
	$error = false;
	
	loadInstuments('TemplateEngine.class');
	
	if (isset($pages['RedirectIfNotFound'])) $PINF = $pages['RedirectIfNotFound'];
	else $PINF = "";
	
	if (
			isset($pages['errorPage']) and
			file_exists(templates.'/'.$pages['errorPage']['template'].'.html')
		) {
			$errPage = $pages['errorPage'];
			if (!isset($errPage['container']) || !file_exists(containers.'/'.$pages['errorPage']['container'].'.php'))
				$pages['errorPage']['container'] = false;
		}
	else $errPage = false;
	
	$config['template_config'] = json_decode(file_get_contents(res."/config.json"),true);
	
	// Не инициализирован конфиг шаблона
	if (!$config['template_config']) $error = 602;
	
	$lenpath = count($config['path'])-1;
	
	foreach ($config['path'] as $key=>$page) {
		$config['path_current_key'] = $key;
		if (isset($pages['RedirectIfNotFound'])) $PINF = $pages['RedirectIfNotFound'];
		if (isset($pages['nested']) && is_array($pages['nested']) && isset($pages['nested'][$page]))
			$pages = $pages['nested'][$page];
		else if ($page!="" /*&& !isset($pages['eNested'])*/ && !isset($pages['variables'])) {
			$error = 404;
		} else if (isset($pages['variables'])) {
			$founded = false;
			foreach ($pages['variables'] as $vkey=>&$varpage) {
				if (!isset($varpage['container']) || !isset($varpage['template'])) continue;
				try {
					$varpage['template'] = new \page\TemplateEngine($varpage['template']);
					if (!$varpage['template']->exists()) continue;
					$varpage['container'] = loadContainer($varpage['container'],$varpage['template']);
					if (!$varpage['container']->validate()) continue;
					$pages = $pages['variables'][$vkey];
					$founded = true;
				} catch (Exception $e) {}
			}
			if (!$founded) $error = 404;
		}
		if ($error != false && isset($pages['eNested']) && $enested == false) {
			foreach ($pages['eNested'] as $vkey=>&$varpage) {
				if (!isset($varpage['container']) || !isset($varpage['template'])) continue;
				try {
					$varpage['template'] = new \page\TemplateEngine($varpage['template']);
					if (!$varpage['template']->exists()) continue;
					
					if (!isset($config['page']['StartPathKey']) || $config['page']['StartPathKey'] == null)
						$config['page']['StartPathKey'] = $key;
						
					$varpage['container'] = loadContainer($varpage['container'],$varpage['template']);
					if (!$varpage['container']->validate()) continue;
					
					$pages = $pages['eNested'][$vkey];
					
					$enested = true;
					
					if ($error == 404) $error = false;
				} catch (Exception $e) {}
			}
		} else if ($enested	 != false) {
			if (!$pages['container']->validate()) continue;
			if ($error == 404) $error = false;
		}
		if ($error != false) break;
	}
	$currentPage = $pages;
	unset($config['pages']);
	
	$config['page']['events'] = &$currentPage['events'];
	
	// Шаблон не задан
	if (!isset($currentPage['template'])) $error = 404;
	else if (!($currentPage['template'] instanceof \page\TemplateEngine))
		$currentPage['template'] = new \page\TemplateEngine($currentPage['template']);
	
	// Контейнер не задан
	if (!isset($currentPage['container'])) $currentPage['container'] = false;
	
	// Проверка прав
	if (isset($currentPage['permission']) && !checkPerm($currentPage['permission'])) $error = 403;

	// Проверка существовании файлов страницы
	if ($error == false && !$currentPage['template']->exists() ) {
		if ($PINF) Redirect($PINF);
		else $error = 601;
	}
	
	// Проверка существования контейнера
	/*if (!file_exists(containers.'/'.strtolower(str_replace(".",'/',$currentPage['container']).'.php')) ) {
		$currentPage['container'] = false;
	}*/
	if (!$error && $currentPage['container']!= false && !($currentPage['container'] instanceof \page\Container)) {
		try{
			$currentPage['container'] = loadContainer($currentPage['container'],$currentPage['template']);
			if (!$currentPage['container']->validate()) {
				$error = 404;
			}
		} catch (Exception $e) {
			$currentPage['container_error'] = $e->getMessage();
			$error = 603;
		}
	}
	
	// Подгрузка эвентов
	if (!$error && isset($currentPage['events'])) {
		loadInstuments('event.class');
		foreach ($currentPage['events'] as $key=>&$event) {
			if (!isset($key) || !isset($event['classname'])) continue;
			if (!isset($event['name'])) $event['name'] = 'action';
			$Brequest = true;
			if (isset($event['type']) && $event['type'] == 'GET') $Brequest = false;
			if ($Brequest) {
				$exist = isset($_POST[$event['name']]);
				if ($exist) $request = $_POST[$event['name']];
				else $request = "";
			} else {
				$exist = isset($_GET[$event['name']]);
				if ($exist) $request = $_GET[$event['name']];
				else $request = "";
			}
			$event['path'] = events.'/'.str_replace('.','/',$event['classname']).'.class.php';
			if ($exist && $request == $key && file_exists($event['path'])) {
				require_once($event['path']);
				$event['classname'] = 'events\\'.str_replace('.','\\',$event['classname']);
				if (!class_exists($event['classname']))
					fatalError('Не найден класс эвента '.$event['classname'], 'Ошибка');
				$event['class'] = new $event['classname']($Brequest);
				$event = $event['class']->execute();
				$config['page']['last_event_out'] = &$event;
			} else {
				$event = false;
			}
		}
	}
	
	// Обработка ошибок
	switch ($error) {
		case 404:
			header('HTTP/1.0 404 Not Found', true, 404);
			$config['error']['message'] = 'Страница не найдена';
			break;
		case 403:
			header('HTTP/1.0 403 Forbidden', true, 403);
			$config['error']['message'] = 'У вас нет доступа к этой странице';
			$config['error']['permission'] = &$currentPage['permission'];
			break;
		case 601:
			header('HTTP/1.0 404 Not Found', true, 404);
			$config['error']['message'] = 'Файл страницы не найден';
			$config['error']['path'] = $currentPage['template']->getTemplatePath();
			break;
		case 602:
			header('HTTP/1.0 404 Not Found', true, 404);
			$config['error']['message'] = 'Ошибка чтения файла конфига';
			$config['error']['config'] = &$config['template_config'];
			break;
		case 603:
			header('HTTP/1.0 404 Not Found', true, 404);
			$config['error']['message'] = &$currentPage['container_error'];
			break;
	}
	
	if ($error != false) {
		$config['error']['code'] = &$error;
		if (!$errPage)
			fatalError($config['error']['message'], 'Ошибка');
		else {
			$errPage['error'] = $error;
			$currentPage = &$errPage;
			try{
				$currentPage['container'] = loadContainer($currentPage['container'],$currentPage['template']);
			} catch (Exception $e) {
				var_dump($e->getMessage());
				$config['error']['message'] .= '  ;  Не удалось загрузить контейнер страницы ошибки: '.$e->getMessage();
				$currentPage['container'] = false;
			}
		}
	}
	
	// Активизируем контейнер
	if ($currentPage['container'] instanceof \page\Container) {
		$rusult = $currentPage['container']->init();
		if (is_array($rusult) || $rusult == true) {
		    if ($rusult == true) $rusult = array();
		    $currentPage['container']->proc($rusult);
		}
	} else if ($currentPage['template'] instanceof \page\TemplateEngine)
		require_once($currentPage['template']->getHtmlPath());
	else
		require_once(templates.'/'.$currentPage['template'].'.html');
}
/**
 * Загрузить контейнер
 */
function loadContainer($container,&$template) {
		$contPath = containers.'/'.strtolower(str_replace(".",'/',$container)).'.php';
		if (!file_exists($contPath)) throw new \Exception('Не найден файл контейнера '.$contPath);
		$cont = 'page\\containers\\'.str_replace(".","\\",$container);
		loadInstuments('PageContainer.class');
		require_once($contPath);
		if (!class_exists($cont)) throw new \Exception('Не найден класс контейнера '.$cont);
		return new $cont($template);
}
/**
 * Загрузить инструменты
 * @param $instruments - список инструментов
 */
function loadInstuments($instruments=array()) {
	global $config;
	if (is_array($instruments)) {
		foreach ($instruments as $ins) {
			if (file_exists(instruments.'/'.$ins.'.php')) {
				require_once(instruments.'/'.$ins.'.php');
				return true;
			}
		}
	} else if (file_exists(instruments.'/'.$instruments.'.php')) {
		require_once(instruments.'/'.$instruments.'.php');
		return true;
	}
	return false;
}
/**
 * Загрузить модуль
 * @param $module - название модуля
 */
function loadModule($module) {
	global $config;
	if (!isset($config['modules'][$module])) {
		loadInstuments('modules/'.$module.'/'.$module.'.class');
		$className = 'modules\\'.$module.'\\index';
		if (class_exists($className)) {
			$config['modules'][$module] = new $className();
			if ($config['modules'][$module]->init() == false)
				$config['modules'][$module] = null;
		} else {
			$config['modules'][$module] = null;
		}
	} else return null;
	return $config['modules'][$module];
}
/**
 * Получить модуль
 * @param $name - имя модуля
 * @return Если модуль не загружен, попатается его загрузить и вернет модуль
 */
function getModule($name) {
	global $config;
	if (isset($config['modules'][$name]) && $config['modules'][$name] != null) return $config['modules'][$name];
	else return loadModule($name);
}
/**
 * Проверить зашел ли пользователь с телефона
 * @return True / False
 */
function check_mobile_device() { 
	if (!isset($_SERVER['HTTP_USER_AGENT'])) return false;
	$agent_array = array(
			'ipad',
			'iphone',
			'android',
			'pocket',
			'palm',
			'windows ce',
			'windowsce',
			'cellphone',
			'opera mobi',
			'ipod',
			'small',
			'sharp',
			'sonyericsson',
			'symbian',
			'opera mini',
			'nokia',
			'htc_',
			'samsung',
			'motorola',
			'smartphone',
			'blackberry',
			'playstation portable',
			'tablet browser'
		);
	$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	foreach ($agent_array as $value)
		if (strpos($agent, $value) !== false) return true;
	return false; 
}
/**
 * Отдать клиенту файл сервера
 * @param $file - Путь к файлу
 */
function downloadFile($file) {
	if (file_exists($file)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}
}
/**
 * Запрос на сайт
 * @param $url - Ссылка на сайт
 * @param $req - Параметры запроса
 * @param $get - True - POST запрос, False - GET запрос
 * @return Результат запроса
 */
function reqURL($url,$req = array(),$get = false) {
	if ($get != false) $url = $url.'?'.http_build_query($req);
	$ch = curl_init($url);
	if ($get == false) {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
	}
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec($ch);
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
 * Замена символов (например, если нужно обрезать кирилицу или символы)
 * @param &$string - Строка
 * @param $symbol - True - Обрезать символы
 * @param $arrPush - Список символов, которые нужно дополнительно обрезать
 */
function translate(&$string,$symbol=false,$arrPush = array()) {
		$tr = array(
			"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
			"Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
			"Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
			"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
			"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
			"Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
			"Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
		);
		if ($symbol) {
			$symb = array(
				" "=>"_","?"=>"_","/"=>"_","\\"=>"_",
				"*"=>"_",":"=>"_","*"=>"_","\""=>"_","<"=>"_",
				">"=>"_","|"=>"_","!"=>"_"
			);
			$tr = array_merge($tr,$symb);
		}
		if (count($arrPush) > 0)
			$tr = array_merge($tr,$arrPush);
		return strtr($string,$tr);
}
?>
