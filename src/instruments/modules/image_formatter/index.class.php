<?php
namespace modules\image_formatter;
if (!defined('core') || core != "SeriousCompanyCore") die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) load('module');
if (!class_exists('\modules\Module')) fatalError("Ошибка: Система модулей не инициализирована");
class index extends \modules\Module {
	const VERSION = '1.1';
	public function install() {
		if ((float)core_version < 3.4)
			throw new \Exception("core version must be >= 3.4");

		$db = \database::getInstance();

		$alias_paths = $db->getTableAlias("paths");
		
		$cond = $db->setCondition();
		$cond->add("alias","=","front");
		$query = $db->select($alias_paths,array("id"));
		$db->clear();
		
		if (isset($query[0]["id"])) $id = $query[0]["id"];
		else return true;

		$cond = $db->setCondition();
		$cond->add("alias","=","imsc");
		$cond->add("parent","=",$id);
		$query = $db->select($alias_paths,array("id"));
		$db->clear();
		
		if (isset($query) && count($query) > 0) return true;
		
		$id_main = $db->insert($alias_paths,array(
			"parent"   => $id,
			"alias"    => "imsc",
			"executor" => "/".instruments."modules.image_formatter.ImageScaling",
			"params"   => "",
			"type"     => 0
		));
		$db->insert($alias_paths,array(
			"parent"   => $id_main,
			"alias"    => "imsc",
			"executor" => "/".instruments."modules.image_formatter.ImageScaling",
			"params"   => "",
			"type"     => 2
		));
	}
	public function uninstall() {
		$db = \database::getInstance();

		$cond = $db->setCondition();
		$cond->add("executor","=","/".instruments."modules.image_formatter.ImageScaling");
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
}