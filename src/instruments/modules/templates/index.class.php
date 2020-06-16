<?php
namespace modules\templates;
if (!defined('core') || core != "SeriousCompanyCore") die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) load('module');
if (!class_exists('\modules\Module')) die("Ошибка: Система модулей не инициализирована");
class index extends \modules\Module {
	const VERSION = '1.0';
	public static $TEMPLATE;
	public static $CUSTOM_TEMPLATE_PATH;
	public static $ROOT;
	public function install() {

		if (!file_exists(root."/templates")) mkdir(root."/templates");
		if (!file_exists(root."/templates/templates")) mkdir(root."/templates/templates");
		if (!file_exists(root."/templates/compilled")) mkdir(root."/templates/compilled");

		if (
				file_exists(root."/".configs)
				&&
				!file_exists(root."/".configs."templates.json")
				&&
				is_writable(root."/".configs)
			) {
			file_put_contents(
				root."/".configs."templates.json",
				json_encode(
					array(
						"current" => "default"
					)
				)
			);
		}


		$db = \database::getInstance();
		
		$alias_paths = $db->getTableAlias("paths");
		
		$cond = $db->setCondition();
		$cond->add("alias","=","front");
		$cond->add("parent","=",0);
		$query = $db->select($alias_paths,array("id"));
		$db->clear();
		
		if (isset($query[0]["id"])) $id = $query[0]["id"];
		else return true;
		
		$id_res = $db->insert($alias_paths,array(
			"parent"   => $id,
			"alias"    => "res",
			"executor" => "/".instruments."modules.templates.PageResourcesFront",
			"params"   => ""
		));
		
		$db->insert($alias_paths,array(
			"parent"   => $id_res,
			"type"     => 2,
			"alias"    => "res",
			"executor" => "/".instruments."modules.templates.PageResources",
			"params"   => ""
		));

		// Если есть модуль admin, тогда добавляем в него свои страницы
		try {
			$module_admin = \modules\Module::load("admin");
			$cond = $db->setCondition();
			$cond->add("parent","=",$id);
			$cond->add("alias","=","admin");
			$query = $db->select($alias_paths);
			$db->clear();

			if (isset($query[0]["id"])) {
				$id_templatesFront = $db->insert($alias_paths,array(
					"parent"   => $query[0]["id"],
					"alias"    => "templates",
					"executor" => "/". instruments ."modules.templates.PageTemplatesFront",
					"params"   => ""
				));
				$db->insert($alias_paths,array(
					"parent"   => $id_templatesFront,
					"alias"    => "templates",
					"type"     => 2,
					"executor" => "/". instruments ."modules.templates.PageTemplates",
					"params"   => ""
				)); 
				$module_admin->addMenuLink("Страницы","/admin/templates/");
			}
		} catch (\modules\ModuleLoadException $e) {}
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
	public function init(){
		require_once(__DIR__.'/Handler.class.php');
		require_once(__DIR__.'/Template.class.php');
		
		$config = getConfig("templates");
		if (is_array($config) && isset($config["current"]))
			index::$TEMPLATE = $config["current"];
		else
			index::$TEMPLATE = "default";
			
		index::$CUSTOM_TEMPLATE_PATH = null;
		
		// Путь к скомпилированным шаблонам
		define('templates_compiled',"templates/compilled");
	}
	/**
	 * Установить шаблон
	 */
	public function setCustomTemplate($name,$path = null) {
		if ($name != null) {
			index::$TEMPLATE = $name;
		} else {
			$config = getConfig("templates");
			if (is_array($config) && isset($config["current"]))
				index::$TEMPLATE = $config["current"];
			else
				index::$TEMPLATE = "default";
		}
		index::$CUSTOM_TEMPLATE_PATH = $path;
	}
	/**
	 * Поулчить путь к текущему шаблону
	 */
	public static function getTemplatePath() {
		if (index::$CUSTOM_TEMPLATE_PATH != null) return index::$CUSTOM_TEMPLATE_PATH;
		return "/templates/"."templates/".index::$TEMPLATE."/";
	}
	/**
	 * Получить локали шаблона
	 */
	public static function getTemplateLocalesPath() {
		return index::getTemplatePath()."locales/";
	}
}
