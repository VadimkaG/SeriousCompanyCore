<?php
namespace modules\admin;
if (!defined('core') || core != "SeriousCompanyCore") die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) load('module');
if (!class_exists('\modules\Module')) fatalError("Ошибка: Система модулей не инициализирована");
class index extends \modules\Module {
	const VERSION = '1.3';
	public static $TEMPLATE;
	public function install() {
		if ((float)core_version < 3.6)
			throw new \Exception("core version must be >= 3.6");

		$db = \database::getInstance();
		
		$alias = $db->getTableAlias("admin_menu");
		$db->row_query("drop table if exists " . $alias);
	
		$db->row_query("create table " . $alias . " ( "
				. "id serial primary key,"
				. "name varchar(100) not null,"
				. "link varchar(100) not null,"
				. "weight int default 0"
			. ")"
		);
		
		$alias_paths = $db->getTableAlias("paths");
		
		$cond = $db->setCondition();
		$cond->add("alias","=","front");
		$query = $db->select($alias_paths,array("id"));
		$db->clear();
		
		if (isset($query[0]["id"])) $id = $query[0]["id"];
		else return true;
		
		$id_main = $db->insert($alias_paths,array(
			"parent"   => $id,
			"alias"    => "admin",
			"executor" => "/".instruments."modules.admin.Main",
			"params"   => ""
		));
		
		$id_res = $db->insert($alias_paths,array(
			"parent"   => $id_main,
			"alias"    => "res",
			"type"     => 0,
			"executor" => "/".instruments."modules.admin.Resources",
			"params"   => ""
		));
		
		$db->insert($alias_paths,array(
			"parent"   => $id_res,
			"alias"    => "res",
			"type"     => 2,
			"executor" => "/".instruments."modules.admin.Resources",
			"params"   => ""
		));

		// Если присутствует модуль accounts
		try {
			$module_accounts = \modules\Module::load("accounts");

			if ((float)\modules\accounts\index::VERSION < 1.1)
				throw new \Exception("module accounts version must be >= 1.1");
			// Добавляем страницу авторизации
			$db->insert($alias_paths,array(
				"parent"   => 0,
				"alias"    => "admin_login",
				"type"     => 0,
				"executor" => "/".instruments."modules.admin.Login",
				"params"   => ""
			));
		} catch (\modules\ModuleLoadException $e) {}

	}
	public function uninstall() {
		$db = \database::getInstance();
		$db->row_query("drop table if exists " . $db->getTableAlias("admin_menu"));

		$cond = $db->setCondition();
		$cond->add("executor","IN",array(
			"/".instruments."modules.admin.Main",
			"/".instruments."modules.admin.Resources",
			"/".instruments."modules.admin.Login"
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
	}
	public function loadAdminPage() {
		require_once(__DIR__.'/AdminPage.class.php');
	}
	/**
	 * Список ссылок в меню
	 */
	public function listMenu() {
		$this->db->sort("weight");
		$result = $this->db->select($this->db->getTableAlias("admin_menu"));
		$this->db->clear();
		return $result;
	}
	/**
	 * Добавить ссылку в меню
	 */
	public function addMenuLink($name,$link,$weight = 0) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_string($link)) throw new \InvalidArgumentException('$link must be string');
		if (!is_int($weight)) throw new \InvalidArgumentException('$weight must be int');
		$this->db->insert(
			$this->db->getTableAlias("admin_menu"),
			array(
				"name" => (string)$name,
				"link" => (string)$link,
				"weight" => (int)$weight
			)
		);
	}
	/**
	 * Удалить ссылку из меню
	 */
	public function delMenuLink($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition();
		$cond->add("id","=",$id);
		$this->db->delete($this->db->getTableAlias("admin_menu"));
		$this->db->clear();
	}
	/**
	 * редактировать ссылку
	 */
	public function editMenuLink($id,$name,$link,$weight = 0) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_string($link)) throw new \InvalidArgumentException('$link must be string');
		if (!is_int($weight)) throw new \InvalidArgumentException('$weight must be int');
		$cond = $this->db->setCondition();
		$cond->add("id","=",$id);
		$this->db->update($this->db->getTableAlias("admin_menu"),array(
			"name"=>$name,
			"link"=>$link,
			"weight"=>$weight
		));
		$this->db->clear();
	}
}
