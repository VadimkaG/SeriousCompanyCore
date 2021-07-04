<?php
if (!defined('core') || core != "SeriousCompanyCore") die("Ошибка: Ядро не импортировано");
/**
 * Главная страница установки
 */
function printInstallPage() {
	if (!isset($_POST['step'])) {
		TemplateWelcome();
		die();
	}
	switch ($_POST['step']) {
		case "system_configs": TemplateSystemConfigs(); break;
		case "install":
			if (isset($_POST['db_name']) && isset($_POST['db_login']) && isset($_POST['db_password']) && isset($_POST['db_server']))
				if (!createConfig($_POST['db_server'],$_POST['db_name'],$_POST['db_login'],$_POST['db_password'])) TemplateError("Не удалось создать настройки");
			install();
			TemplateDone();
			break;
		default: TemplateWelcome(); break;
	}
}
/**
 * Страница приветствия
 */
function TemplateWelcome() {
?><html>
<head>
	<title>Установщик SeriousCompany Core</title>
	<style>
		.container {display:block;text-align:center;width:600px;height:600px;position:absolute;top:50%;left:50%;margin-left:-300px;margin-top:-200px;}
		.btn{border:none;font:13.3333px Arial;text-decoration:none;padding:3px 10px;margin:1px;display:inline-block;border-radius:0;text-shadow:none;text-align:center;min-width:5px;min-height:10px;background:#bbb}
		.btn:hover{box-shadow:0 0 2px 1px #bdbdbd;cursor:pointer}
		.bg_blue{color:#ececec;background:#2f7794;}
	</style>
</head>
<body>
	<div class="container">
		<h1>Установщик SeriousCompany Core</h1>
		<div>Вас приветствует установщик SeriousCompany Core.</div>
		<div>Прежде чем структура в базе данных будет создана</div>
		<div>вам необходимо указать несколько опций.</div>
		<div>Нажмите "Установить", чтобы продолжить.</div>
		<br>
		<form method="POST">
			<input type="hidden" name="step" value="system_configs">
			<button class="btn bg_blue">Установить</button>
		</form>
	</div>
</body>
</html><?
}
/**
 * Создание главного файла конфигурации
 */
function TemplateSystemConfigs() {
?><html>
<head>
	<title>Установщик SeriousCompany Core</title>
	<style>
		.container {display:block;text-align:center;width:600px;height:600px;position:absolute;top:50%;left:50%;margin-left:-300px;margin-top:-200px;}
		.btn{border:none;font:13.3333px Arial;text-decoration:none;padding:3px 10px;margin:1px;display:inline-block;border-radius:0;text-shadow:none;text-align:center;min-width:5px;min-height:10px;background:#bbb}
		.btn:hover{box-shadow:0 0 2px 1px #bdbdbd;cursor:pointer}
		.bg_blue{color:#ececec;background:#2f7794;}
		.form__field_wrapper {margin-bottom:5px;}
	</style>
</head>
<body>
	<div class="container">
		<h1>Установщик SeriousCompany Core</h1>
		<h2>Данные для доступа к базе данных</h2>
		<form method="POST">
			<input type="hidden" name="step" value="install">
			<div class="form__field_wrapper">
				<label for="db_server">Сервер:</label>
				<input id="db_server" name="db_server" value="localhost" placeholder="хост">
			</div>
			<div class="form__field_wrapper">
				<label for="db_name">Имя базы:</label>
				<input id="db_name" name="db_name" placeholder="база данных">
			</div>
			<div class="form__field_wrapper">
				<label for="db_login">Логин:</label>
				<input id="db_login" name="db_login" placeholder="Логин">
			</div>
			<div class="form__field_wrapper">
				<label for="db_password">Пароль:</label>
				<input id="db_password" name="db_password" type="password" placeholder="Пароль">
			</div>
			<button class="btn bg_blue">Далее</button>
		</form>
<? if (file_exists(configs."system.json")) { ?> 
		<form method="POST">
			<input type="hidden" name="step" value="install">
			<button class="btn bg_blue">Пропустить</button>
		</form>
<? } ?>
	</div>
</body>
</html><?
}
/**
 * Страница "Выполнено"
 */
function TemplateDone() {
?><html>
<head>
	<title>SeriousCompany Core</title>
	<style>
		.container {display:block;text-align:center;width:600px;height:600px;position:absolute;top:50%;left:50%;margin-left:-300px;margin-top:-200px;}
		.btn{border:none;font:13.3333px Arial;text-decoration:none;padding:3px 10px;margin:1px;display:inline-block;border-radius:0;text-shadow:none;text-align:center;min-width:5px;min-height:10px;background:#bbb}
		.btn:hover{box-shadow:0 0 2px 1px #bdbdbd;cursor:pointer}
		.bg_blue{color:#ececec;background:#2f7794;}
	</style>
</head>
<body>
	<div class="container">
		<h1>Установщик SeriousCompany Core</h1>
		<div>Установка завершена.</div>
		<div>Теперь вы можете перейти на сайт.</div>
		<br>
		<a href="/" class="btn bg_blue">На сайт</a>
	</div>
</body>
</html><?
}
/**
 * Страница ошибки
 */
function TemplateError($message) {
?><html>
<head>
	<title>SeriousCompany Core</title>
	<style>
		.container {display:block;text-align:center;width:600px;height:600px;position:absolute;top:50%;left:50%;margin-left:-300px;margin-top:-200px;}
		.btn{border:none;font:13.3333px Arial;text-decoration:none;padding:3px 10px;margin:1px;display:inline-block;border-radius:0;text-shadow:none;text-align:center;min-width:5px;min-height:10px;background:#bbb}
		.btn:hover{box-shadow:0 0 2px 1px #bdbdbd;cursor:pointer}
		.bg_blue{color:#ececec;background:#2f7794;}
	</style>
</head>
<body>
	<div class="container">
		<h1>Установщик SeriousCompany Core</h1>
		<div>Не удалось установать сайт.</div>
		<div>Исправьте ошибку и обновите страницу.</div>
		<br>
		<div>Описание ошибки:</div>
		<div><?=$message;?></div>
		<br>
		<a href="" class="btn bg_blue">Вернуться</a>
	</div>
</body>
</html><?
die();
}
/**
 * Создание файла конфигурации
 * @param string $host - Хост базы данных
 * @param string $name - Имя базы данных
 * @param string $login - Логин, для полючения к базе данных
 * @param string $password - Пароль для подключения к базе данных
 * @return boolean
 */
function createConfig(string $host, string $name, string $login, string $password) {
	if ($host == "") TemplateError("Хост базы данных не может быть пустым");
	if ($name == "") TemplateError("Имя базы данных не может быть пустым");
	if ($login == "") TemplateError("Логин базы данных не может быть пустым");

	// Создаем директорию конфигурации
	if (!file_exists(root."/".configs) && !is_writable(root))
		TemplateError("Директория конфигурации (".configs.") не существует и нет прав ее создать");
	elseif (file_exists(root."/".configs) && !is_writable(root."/".configs))
		TemplateError("Отсутствуют права на запись в директорию конфигурации (".configs.").");
	elseif (!file_exists(root."/".configs))
		mkdir(root."/".configs);

	// Создаем файл конфигурации
	if (!file_exists(root."/".configs."system.json") && !is_writable(root."/".configs))
		TemplateError("Файл конфигураци (".configs."system.json".") не существует. Его не возможно создать, так как директория конфигурации не доступна для записи.");
	elseif (file_exists(root."/".configs."system.json") && !is_writable(root."/".configs."system.json"))
		TemplateError("Файл конфигураци (".configs."system.json".") не доступен для записи.");

	// СОздаем директорию слушателей событий
	if (!file_exists(root."/".configs."listeners") && !is_writable(root."/".configs))
		TemplateError("Не удалось создать директорию для слушателей событий (".configs."listeners".").");
	elseif (file_exists(root."/".configs."listeners") && !is_writable(root."/".configs."listeners"))
		TemplateError("Нет прав на запись в директорию слушателей событий (".configs."listeners".").");
	elseif (!file_exists(root."/".configs."listeners"))
		mkdir(root."/".configs."listeners");

	$db = new MySQLI($host,$login,$password,$name);
	if ($db->connect_errno)
		TemplateError('Ошибка подключения к базе данных MYSQLI: '.$db->connect_error);
	$db->close();
	$result = file_put_contents(root."/".configs."system.json",json_encode(array(
		"database" => array(
			"host"   => $host,
			"basename"     => $name,
			"user"     => $login,
			"password" => $password
		)
	)));
	chmod(root."/".configs."system.json", 256);
	return $result;
}
/**
 * Установка
 */
function install() {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$res = load("StoredObjectCondition.class");
	if (!$res) fatalError('Инструмент "StoredObjectCondition.class.php" не удалось загрузить.',$coreErrorMessage);
	$res = load("StoredObject.class");
	if (!$res) fatalError('Инструмент "StoredObject.class.php" не удалось загрузить.',$coreErrorMessage);
	$configs = getConfig('system');
	if (!$configs) TemplateError('Файл конфигурации "system" не найден.');
	if (!isset($configs['database']['host']) || !isset($configs['database']['user']) || !isset($configs['database']['password']) || !isset($configs['database']['basename']))
		TemplateError('В конфигурации не найдены данные о базе данных.<br>Ожидаемые данные: "database:{host, basename, user, password}"');

	$GLOBALS["MYSQLI_CONNECTION"] = new MySQLI($configs['database']['host'],$configs['database']['user'],$configs['database']['password'],$configs['database']['basename']);
	if ($GLOBALS["MYSQLI_CONNECTION"]->connect_errno)
		TemplateError('Ошибка подключения к базе данных MYSQLI: '.$GLOBALS["MYSQLI_CONNECTION"]->connect_error);

	$res = load("path.class");
	if (!$res) TemplateError('Инструмент "path.class.php" не удалось загрузить.');

	try {
		Path::uninstall();
		Path::install();
	} catch (\Exception $e) {
		TemplateError('Ошибка установки объекта Path: '.$e->getMessage());
	}

	(new Path([
		"url"      => "",
		"variable" => false,
		"executor" => "/".instruments."PageEditor",
		"params"   => ""
	]))->save();
	
	load('module.class.php');

	// Список модулей
	$module_list = \modules\Module::allModules();
	/**
	 * Все модули
	 * key = Имя модуля
	 * value = true - установлен ли
	 */
	$modules = array();
	/**
	 * Модули не удовлетворяющие зависимостями
	 * key = имя модуля
	 * value = array зависимостей
	 */
	$not_allowed = array();

	// Заносим все модули в $module_list
	foreach($module_list as $module_name) {
		$module = \modules\Module::load($module_name);
		$depends = $module->depends();
		// Удовлетворяют ли зависимости
		$allowed = true;
		// Провеяем зависемости
		foreach ($depends as $depend) {
			if (!isset($modules[$module_name])) {
				$allowed = false;
				break;
			}
		}
		// Если зависемости удовлетворяют, то устанавливаем.
		if ($allowed) {
			// Устновка модуля $module_name
			try {
				$module = \modules\Module::load($module_name);
				$module->install();
			} catch (Exception $e) {
				$db->row_query("drop table if exists " . $db->getTableAlias("paths"));
				TemplateError("Ошибка установки модуля '".$module_name."'. Причина:<br>".$e->getMessage());
			}
			$modules[$module_name] = true;
		// Иначе не устанавливаем и добавляем в список неудовлетворенных
		} else {
			$not_allowed[$module_name] = $depends;
			$modules[$module_name] = false;
		}
	}
	// Список модулей нам больше не нужен
	unset($module_list);

	
	// Далее проходим по одному сценарию
	// до тех пор пока список неудовлетворенных не опустеет
	// Чтобы не уйти в бесконечный цикл, количество циклов будет ограничено до 1000
	$c = 0;
	do {
		if ($c > 1000) {
			var_dump($modules,$not_allowed);
			TemplateError("Слишком много переадресаций, чтото пошло не так");
			break;
		}
		$c++;
		// Перебираем все модули
		foreach($modules as $module=>&$installed) {
			// Если модуль уже установлен, то он нам не интересен - пропускаем
			if ($installed) continue;
			// Эта переменная будет нам говорить удовлетворены ли зависимости
			$allow = true;
			// Проверяем зависимости
			foreach($not_allowed[$module] as $depend=>$require) {
				// Есть ли зависимость в списке модов
				$depend_exists = isset($modules[$depend]);
				// Если модуль требует зависимость, но ее просто нету в списке
				// То просто выдает ошибку. Такого быть не должно
				if ($require && !$depend_exists) TemplateError("Модуль \"". $module. "\" зависит от \"". $depend. "\", но модуль \"". $depend ."\" не найден");
				// Если модуль важен, но не установлен
				// Или
				// Если модуль не важен, но он есть в списках модов и он не установлен
				// То зависимости не удовлетворены
				elseif (
					($require && !$modules[$depend])
					||
					(!$require && $depend_exists && !$modules[$depend])
				) {
					$allow = false;
					break;
				}
			}
			// Если все зависимости удовлетворены
			// Устанавливаем модуль
			if ($allow) {
				// Устновка модуля $module
				try {
					$module_obj = \modules\Module::load($module);
					$module_obj->install();
				} catch (Exception $e) {
					$db->row_query("drop table if exists " . $db->getTableAlias("paths"));
					TemplateError("Ошибка установки модуля '".$module."'. Причина:<br>".$e->getMessage());
				}
				
				// Теперь удалим модуль из списка неудовлетворенных
				unset($not_allowed[$module]);
				$installed = true;
			}
		}
	} while(count($not_allowed) > 0);
	
	$GLOBALS["MYSQLI_CONNECTION"]->close();
}
