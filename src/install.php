<? if (!defined(instruments)) die("Ошибка: Запуск возможен только через ядро");
if (isset($_GET['install'])) {
	if (isset($_GET['confirm'])) {
		install();
		echo "<html><head><title>SCcore</title></head><body><center><h1>Установщик</h1><div>Сайт установлен. Можно заходить на <a href='/'>сайт</a></div></center></body></html>";
	} else
		echo "<html><head><title>SCcore</title></head><body><center><h1>Установщик</h1><div>Подтвердив установку будут стерты из базы все таблицы, которые совпадают по имени с теми, которые требуются сайту.<br> И будут созданы новые, чистые таблицы с необходимой структурой.</div><div><a href='/?install&confirm'>Подтвердите установку сайта, нажав здесь</a></div></center></body></html>";
	die();
}

/**
 * Установка базы данных сайта
 */
function install() {
	loadInstuments("database.class");
	$tablenames = getConfig('loader');
	$database = $tablenames['database'];
	$tablenames = $tablenames['tablenames'];
	$db = new database($database['server'],$database['user'],$database['password'],$database['basename']);
	
	$db->row_query("drop table if exists ".$tablenames['SiteConfig']);
	$db->row_query("create table ".$tablenames['SiteConfig']." (option_name varchar(50), option_value varchar(244) )");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('Title', 'My Site')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('About', 'Не настроен')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('KeyWords', '')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('SiteClosed', 'True')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('ClosedReason', 'Технические работы.')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('authEnabled', 'False')");
	$db->row_query("insert into ".$tablenames['SiteConfig']." (option_name, option_value) values ('template', 'core')");
	
	$module = getRawModule('accounts');
	
	$module->install($db,$tablenames);
	
	$db->disconnect();
}

/**
 * Загрузить модуль
 * @param $module - название модуля
 */
function getRawModule($module) {
	$out = null;
	loadInstuments('modules/'.$module.'/'.$module.'.class');
	$className = 'modules\\'.$module.'\\index';
	if (class_exists($className))
		$out = new $className();
	return $out;
}
