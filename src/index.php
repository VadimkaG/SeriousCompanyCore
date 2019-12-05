<?

$start_time = microtime(true);

// Для вывода ошибок
if(isset($_GET['display_errors'])) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}

ini_set('default_charset', 'utf-8');

// Подгрузка пути к инструментам
define('instruments','instruments');

// Старт сессии
session_start();

// Подключение системных функций
if (!file_exists(instruments."/system.php")) die('Критическая ошибка: Системные функции не найдены.');
require_once(instruments."/system.php");
if (!function_exists('initCore') || !function_exists('loadPage')) die('Критическая ошибка: Ядро SeriousCompanyCore не найдено.');

// Инициализируем ядро
initCore();

// Ручное управление шаблонами
if (isset($_SESSION['template'])) $config['template'] = formChars($_SESSION['template']);
if (isset($_GET['template'])) {
	$_SESSION['template']=formChars($_GET['template']);
	Redirect('/');
}
if (isset($_GET['cleartemplate']) and isset($_SESSION['template'])) {
	unset($_SESSION['template']);
	Redirect('/');
}
// ===========================*/

// Отдельный шаблон под админку
if (
			(
				isset($config['path'][1])
				&&
				$config['path'][1] == 'admin'
			)
			||
			(
				$config['locale'] != ""
				&&
				$config['path'][1] == null
				&&
				isset($config['path'][2])
				&&
				$config['path'][2] == 'admin'
			)
		) {
			$config['old_template'] = $config['template'];
			$config['template'] = 'admin';
}

// ==== Подгрузка путей =====
define('root',$_SERVER['DOCUMENT_ROOT']);
define('res','resources/'.$config['template']);
define('pubres','pubres');
define('templates',res.'/templates');
define('locales',res.'/locales');
define('content','content');
define('containers',instruments.'/containers');
define('events',instruments.'/events');
define('c_templates',content.'/templates/'.$config['template']);
define('scripts',res.'/scripts');
define('styles',res.'/styles');
// ==========================

if (!file_exists(c_templates)) mkdir(c_templates);

// Деавторизация
if (isset($_REQUEST['logout']) and isAuth()) {
	if (isset($_SESSION['user_id'])) unset($_SESSION['user_id']);
	Redirect('/');
}
// =============

// Не пускаем дальше, если сайт закрыт
$adminPass = '11213';
if ($config['SiteClosed']==="True" && (!isset($_SESSION['closed_perm']) || $_SESSION['closed_perm'] != $adminPass)) {
	if (isset($_GET['login']) && $_GET['login'] == $adminPass) {
		$_SESSION['closed_perm'] = true;
		Redirect('/');
	}
	fatalError($config['ClosedReason'],'Сайт закрыт для посещения');
} else if ($config['SiteClosed']==="True" && isset($_GET['clearlogin'])) {
	unset($_SESSION['closed_perm']);
	Redirect('/');
}
// ============================

// Проверка существования шаблона
if (!file_exists(templates) || !file_exists(res.'/config.json')) fatalerror('Шаблон '.$config['template'].' не найден');

// Функция изменения разрешения изображений
if (isset($config['path'][1]) && $config['path'][1] == "preview") {
	if (!isset($config['path'][2]) || !isset($config['path'][3])) fatalerror('Неверное использование preview.',$config['path'][1]);
	$size = explode("x",$config['path'][2]);
	$neww = (int)$size[0];
	$newh = (int)$size[1];
	if ($neww <= 0) fatalerror('Ошибка ширины: '.$neww,$config['path'][1]);
	if ($newh <= 0) fatalerror('Ошибка длинны: '.$newh,$config['path'][1]);
	$pathA = $config['path'];
	unset($pathA[0],$pathA[1],$pathA[2]);
	$path = "";
	foreach ($pathA as $node) {
		$path .= $node;
		if ($node != end($pathA)) $path .= "/";
	}
	if (!file_exists($path)) fatalerror('Файл не найден.',$config['path'][1]);
	$exPath = explode(".", $path);
	$fileSuffix = end($exPath);
	if ($fileSuffix == 'jpg' or $fileSuffix == 'jpeg') {
		$im=imagecreatefromjpeg($path);
		$im1=imagecreatetruecolor($neww,$newh);
		imagecopyresampled($im1,$im,0,0,0,0,$neww,$newh,imagesx($im),imagesy($im));
		header('Content-Type: image/jpeg');
		imagejpeg($im1,null,75);
		imagedestroy($im);
		imagedestroy($im1);
	} else if ($fileSuffix == 'png') {
		$im=imagecreatefrompng($path);
		$im1=imagecreatetruecolor($neww,$newh);
		imagecopyresampled($im1,$im,0,0,0,0,$neww,$newh,imagesx($im),imagesy($im));
		header('Content-Type: image/png');
		imagepng($im1,null,0);
		imagedestroy($im);
		imagedestroy($im1);
	} else {
		fatalerror('Ошибка определения формата '.$fileSuffix,$config['path'][1]);
	}
	die();
}
// ===================

// Подгрузка страницы
loadPage();
echo '<!-- page loaded with '.(microtime(true)-$start_time).' seconds-->';
?>
