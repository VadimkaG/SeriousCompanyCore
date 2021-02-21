<?php
namespace modules\accounts;
if (!defined('core') || core != "SeriousCompanyCore") die("Ошибка: Ядро не импортировано");
require_once(__DIR__.'/Exceptions.php');
class index extends \modules\Module {
	const VERSION = '1.2';
	public function install() {
		if ((float)core_version < 3.4)
			throw new \Exception("core version must be >= 3.4");

		$db = \database::getInstance();

		// Пользователи
		$db->row_query("drop table if exists ".$db->getTableAlias('accounts'));
		$db->row_query("create table ".$db->getTableAlias('accounts')." ( "
				."id serial primary key, "
				."login varchar(100), "
				."password varchar(100), "
				."ugroup bigint, "
				."registered datetime not null, "
				."lastlogin datetime "
			.")");
		$db->row_query("create index accountsByLogin on ".$db->getTableAlias('accounts')." ( login )");
		$db->row_query("create index accountsByPassword on ".$db->getTableAlias('accounts')." ( login, password )");
		$db->row_query("create index accountsByGroup on ".$db->getTableAlias('accounts')." ( ugroup )");
		$db->row_query("insert into ".$db->getTableAlias('accounts')." (login,password,ugroup,registered) values ('admin','".index::cryptString('admin')."',1,NOW())");
		
		// Группы
		$db->row_query("drop table if exists ".$db->getTableAlias('PermissionGroups'));
		$db->row_query("create table ".$db->getTableAlias('PermissionGroups')." (id serial primary key, name varchar(50) )");
		$db->row_query("insert into ".$db->getTableAlias('PermissionGroups')." (id, name) values (1, 'Администратор')");
		$db->row_query("insert into ".$db->getTableAlias('PermissionGroups')." (id, name) values (2, 'Пользователь')");
		/**
		 * Значения type:
		 * 0 = user
		 * 1 = group
		 */
		$db->row_query("drop table if exists ".$db->getTableAlias('Permissions'));
		$db->row_query("create table ".$db->getTableAlias('Permissions')." (id serial primary key, parent bigint not null, type BIT(1) not null, perm text)");
		$db->row_query("create index permissions on ".$db->getTableAlias('Permissions')." ( type, parent, perm )");

		// Кастомные поля пользователей
		$db->row_query("drop table if exists ".$db->getTableAlias('AccountFields'));
		$db->row_query("create table ".$db->getTableAlias('AccountFields')." ( "
				."id serial primary key, "
				."alias varchar(50) not null, "
				."name varchar(100), "
				."type varchar(50) not null, "	
				."length int default 0"
			.")");
		
		// Добавляем страницу авторизации
		$alias_paths = $db->getTableAlias("paths");
		
		$cond = $db->setCondition();
		$cond->add("alias","=","front");
		$cond->add("parent","=",0);
		$query = $db->select($alias_paths,array("id"));
		$db->clear();
		
		if (isset($query[0]["id"])) $id = $query[0]["id"];
		else return true;

		\Event::regListener("login",instruments."modules/accounts/index.class.php","\modules\accounts\index::loginEvent");
		\Event::regListener("logout",instruments."modules/accounts/index.class.php","\modules\accounts\index::logout");
		\Event::regListener("isSetPermission",instruments."modules/accounts/index.class.php","\modules\accounts\index::isSetPermission");
		\Event::regListener("isAuth",instruments."modules/accounts/index.class.php","\modules\accounts\index::isAuth");
		\Event::regListener("getUserCurrent",instruments."modules/accounts/index.class.php","\modules\accounts\index::getUserCurrent");
		
		// Если есть модуль templates, тогда добавляем страницу авторизации
		// DEPRECATED - Устарело. Теперь эта страница по желанию
		/*try {
			\modules\Module::load("templates");
			$id_main = $db->insert($alias_paths,array(
				"parent"   => $id,
				"alias"    => "auth",
				"executor" => "/".instruments."modules.accounts.Login",
				"params"   => ""
			));
		} catch (\modules\ModuleLoadException $e) {}*/

		// Если есть модуль admin, тогда добавляем в него свои страницы
		try {
			$module_admin = \modules\Module::load("admin");
			if ((float)\modules\admin\index::VERSION < 1.0)
				throw new \Exception("module admin version must be >= 1.0");
			$cond = $db->setCondition();
			$cond->add("parent","=",$id);
			$cond->add("alias","=","admin");
			$query = $db->select($alias_paths);
			$db->clear();

			if (isset($query[0]["id"])) {
				$db->insert($alias_paths,array(
					"parent"   => $query[0]["id"],
					"alias"    => "accounts",
					"executor" => "/". instruments ."modules.accounts.PageAccounts",
					"params"   => ""
				));
				$db->insert($alias_paths,array(
					"parent"   => $query[0]["id"],
					"alias"    => "groups",
					"executor" => "/". instruments ."modules.accounts.PageGroups",
					"params"   => ""
				));
				$module_admin->addMenuLink("Пользователи","/admin/accounts/");
				$module_admin->addMenuLink("Группы","/admin/groups/");
			}
		} catch (\modules\ModuleLoadException $e) {}
	}
	public function uninstall() {
		$db = \database::getInstance();
		$db->row_query("drop table if exists ".$db->getTableAlias('accounts'));
		$db->row_query("drop table if exists ".$db->getTableAlias('PermissionGroups'));
		$db->row_query("drop table if exists ".$db->getTableAlias('Permissions'));
		$db->row_query("drop table if exists ".$db->getTableAlias('AccountFields'));

		$cond = $db->setCondition();
		$cond->add("executor","IN",array(
			"/". instruments ."modules.accounts.PageAccounts",
			"/". instruments ."modules.accounts.PageGroups"
		));
		$query = $db->select($db->getTableAlias("paths"),array("id"));
		$db->clear();

		$ids = array();
		$count = 0;
		foreach ($query as $item) {
			if (isset($item["id"])) {
				$count++;
				$ids[] = (int)$item["id"];
			}
		}

		if ($count > 0) \Path::delPage($ids);

		\Event::unregListener("login","\modules\accounts\index::loginEvent");
		\Event::unregListener("logout","\modules\accounts\index::logout");
		\Event::unregListener("isSetPermission","\modules\accounts\index::isSetPermission");
		\Event::unregListener("isAuth","\modules\accounts\index::isAuth");
		\Event::unregListener("getUserCurrent","\modules\accounts\index::getUserCurrent");
	}
	/**
	 * Зависемости
	 * @return array( module_name => required )
	 */
	public function depends() {
		return array(
			"admin" => false
		);
	}
	public function init() {
		session_start();
	}
	public static function cryptString($str) {
		if (!is_string($str)) throw new \InvalidArgumentException();
		return md5($str);
	}
	public static function genSession($login,$password) {
		return genMD("SES00135002log00".$login."001122pass00".$password."huia125");
	}
	private $accutils = null;
	public function getAccountUtils() {
		if ($this->accutils == null) {
			require_once(__DIR__.'/accutils.class.php');
			$this->accutils = new AccountUtils($this->db);
		}
		return $this->accutils;
	}
	private $grutils = null;
	public function getGroupUtils() {
		if ($this->grutils == null) {
			require_once(__DIR__.'/grutils.class.php');
			$this->grutils =  new GroupUtils($this->db);
		}
		return $this->grutils;
	}
	/**
	 * Установить авторизационную сессию
	 */
	public function setAuthSession($id) {
		$util = $this->getAccountUtils();
		if ($util->existsAccount($id)) {
			$_SESSION['USER_ID'] = (int)$id;
			$db = \database::getInstance();
			$cond = $db->setCondition('and');
			$cond->add('id','=',$_SESSION['USER_ID']);
			$db->update($db->getTableAlias('accounts'),array(
				"lastlogin" => "NOW()"
			),true);
			$db->clear();
		}
	}
	/**
	 * Получить авторизационную сессию
	 */
	public function getAuthSession() {
		if (isset($_SESSION['USER_ID'])) return $_SESSION['USER_ID'];
		return null;
	}
	/**
	 * Удалить авторизационную сессию
	 */
	public function delAuthSession() {
		unset($_SESSION['USER_ID']);
	}
	/**
	 * Событие авторизации
	 * @param array( login, password ) $params
	 * @return boolean
	 */
	public static function loginEvent(array $params) {
		$accounts = \modules\Module::load("accounts");
		if (isset($params["login"]) && isset($params["password"])) {
			$accUtils = $accounts->getAccountUtils();
			if ($accUtils)
				try {
					$arr = $accUtils->getAccountByLogin($params["login"],$params["password"]);
					$accounts->setAuthSession($arr["id"]);
					return true;
				} catch (\modules\accounts\AccountNotFoundException $e) {}
		}
		return false;
	}
	public static function logout() {
		$accounts = \modules\Module::load("accounts");
		$accounts->delAuthSession();
	}
	/**
	 * Событие проверки привилегии
	 * @reutrn boolean
	 */
	public static function isSetPermission(array $params) {
		$accounts = \modules\Module::load("accounts");
		$user_id = $accounts->getAuthSession();
		if ($user_id) {
			$accUtils = $accounts->getAccountUtils();
			if (isset($params["permission"]))
				return $accUtils->isSetPermission($user_id,$params["permission"]);
			elseif (isset($params["perm"]))
				return $accUtils->isSetPermission($user_id,$params["perm"]);
		} else return false;
	}
	/**
	 * Авторизован ли текущий пользователь
	 * @return boolean
	 */
	public static function isAuth() {
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id) return true;
		else return false;
	}
	/**
	 * Получить текущего пользователя
	 * @param null|array
	 */
	public static function getUserCurrent() {
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id != NULL) {
			try {
				$utils = $accounts->getAccountUtils();
				return $utils->getAccount($id);
			} catch (\Exception $e) {}
		}
		return null;
	}
}
