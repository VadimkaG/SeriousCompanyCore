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
			try {
				install();
			} catch (\Exception $e) {
				TemplateError($e->getMessage());
			}
			TemplateDone();
			break;
		default: TemplateWelcome(); break;
	}
}
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
		<div>Прежде чем структура в базе данных будет создана.</div>
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
		<h2>Системная конфигурация</h2>
		<form method="POST">
			<input type="hidden" name="step" value="install">
			<div class="form__field_wrapper">
				<label for="db_server">Сервер базы данных:</label>
				<input id="db_server" name="db_server" value="localhost">
			</div>
			<div class="form__field_wrapper">
				<label for="db_name">Имя базы данных:</label>
				<input id="db_name" name="db_name">
			</div>
			<div class="form__field_wrapper">
				<label for="db_login">Логин базы данных:</label>
				<input id="db_login" name="db_login">
			</div>
			<div class="form__field_wrapper">
				<label for="db_password">Пароль базы данных:</label>
				<input id="db_password" name="db_password" type="password">
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
function createConfig($server,$name,$login,$password) {
	if ($server == "") TemplateError("Сервер базы данных не может быть пустым");
	if ($name == "") TemplateError("Имя базы данных не может быть пустым");
	if ($login == "") TemplateError("Логин базы данных не может быть пустым");
	if (!file_exists(root."/".configs)) {
		TemplateError("Директория конфигурации не существует.");
	} else
	if (file_exists(root."/".configs) && !is_writable(root."/".configs)) {
		TemplateError("Директория конфигурации не доступна для записи.");
	} else
	if (file_exists(root."/".configs."system.json") && !is_writable(root."/".configs."system.json")) {
		TemplateError("Файл \"".configs."system.json"."\"". "недоступен для записи, пожалуйста настройте файл в ручную.");
	}
	$db = new database($server,$login,$password,$name);
	try {
		$db->connect();
		$db->disconnect();
	} catch (\databaseExceptuion $e) {
		TemplateError("Данные для базы данных не верны");
	}
	return file_put_contents(configs."system.json",json_encode(array(
		"database" => array(
			"server"   => $server,
			"basename" => $name,
			"user"     => $login,
			"password" => $password
		),
		"tablenames"     => array(),
		"locales"        => array(),
		"locale_default" => "ru"
	)));
}
function install() {
	$res = load("database.class");
	if (!$res) TemplateError('Не удалось загрузить инструмент database.class');
	$configs = getConfig('system');
	if (!$configs) TemplateError('Файл конфигурации "system" не найден.');
	if (!isset($configs['database']['server']) || !isset($configs['database']['user']) || !isset($configs['database']['password']) || !isset($configs['database']['basename']))
		TemplateError('В конфигурации не найдены данные о базе данных.<br>Ожидаемые данные: "database:{server, basename, user, password}"');
	
	$db = database::getInstance($configs['database']['server'],$configs['database']['user'],$configs['database']['password'],$configs['database']['basename'],$configs['tablenames']);
	
	$alias = $db->getTableAlias("paths");
	$db->row_query("drop table if exists " . $alias);
	
	$db->row_query("create table " . $alias . " ( "
			. "id serial primary key,"
			. "parent bigint default 0,"
			. "alias varchar(50) not null,"
			. "executor varchar(255) not null,"
			. "type tinyint not null default 0,"
			. "params text not null"
		. ")"
	);
	
	$db->row_query("create index search_page on ". $alias ."( parent, alias, type, id )");

	$db->insert($alias,array(
			"parent"   => 0,
			"alias"    => "front",
			"executor" => "/".instruments."PageEditor",
			"params"   => ""
		));
	
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
			$module = \modules\Module::load($module_name);
			$module->install();
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
			throw new \Exception("Слишком много переадресаций, чтото пошло не так");
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
				if ($require && !$depend_exists) throw new \Exception("Модуль \"". $module. "\" зависит от \"". $depend. "\", но модуль \"". $depend ."\" не найден");
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
				$module_obj = \modules\Module::load($module);
				$module_obj->install();

				// Теперь удалим модуль из списка неудовлетворенных
				unset($not_allowed[$module]);
				$installed = true;
			}
		}
	} while(count($not_allowed) > 0);
	
	$db->disconnect();
}
