<?php
class Path {
	private $id;
	private $parent;
	private $alias;
	private $executor;
	private function __construct($id,$alias,$executor,$parent = null){
		$this->id = (int)$id;
		$this->parent = $parent;
		$this->alias = (string)$alias;
		if (is_array($executor) && isset($executor["#executor"]) && isset($executor["db"]) && isset($executor["params"])) {
			$executor_name = &$executor["#executor"];
			$this->executor = new $executor_name($executor["db"],$executor["params"],$this);
		} else
			$this->executor = null;
	}
	/**
	 * Получить корневую страницу
	 * @return \Path
	 */
	public function getParent() {
		return $this->parent;
	}
	/**
	 * Получить обработчик
	 * @return \PathExecutor
	 */
	public function executor() {
		return $this->executor;
	}
	/**
	 * Получить идентификатор страницы
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
	/**
	 * Получить алиас
	 * @return string
	 */
	public function getAlias() {
		return $this->alias;
	}
	/**
	 * Преобразовать объект в URI строку
	 * @return string
	 */
	public function __toString() {
		$str = $this->getAlias();
		$parent = $this->getParent();
		while ($parent != null) {
			if ($parent->getParent() == null && $parent->getAlias() == "front") {
				$str = "/".$str;
				break;
			}
			$str = $parent->getAlias()."/".$str;
			$parent = $parent->getParent();
		}
		return $str;
	}
	/**
	 * Преобразовать путь в массив
	 * @return array()
	 */
	public function toArray(&$link = "/", &$arr = array()) {
		if ($this->parent != null)
			$this->parent->toArray($link, $arr);
		$title = "";
		if ($this->executor != null)
		$title = $this->executor->getTitle();
		if ($this->parent != null)
			$link .= $this->alias . "/";
		$arr[] = array(
			"#object" => &$this,
			"id"      => $this->id,
			"alias"   => $this->alias,
			"title"   => $title,
			"link"    => $link
		);
		return $arr;
	}
	/**
	 * Получить страницу "Страница не найдена"
	 * @return \Path
	 */
	public static function pageNotFound() {
		$parent = 0;
		$page = Path::getPage("not_found",$parent);
		if ($page == null)
			header('HTTP/1.0 404 Page Not Found', true, 404);
		return $page;
	}
	/**
	 * Получить текущий путь
	 * @param $str_path - Строка URL
	 * @return \Path
	 */
	public static function getPath($path) {

		if (is_array($path)) {
			$url = $path;
		} else {
			$a = explode('?',$path,2);
			$url = explode('/',$a[0]);
			unset($a);
		}
		if ($url[0] == "") $url[0] = "front";
		
		$currentPath = null;
		foreach ($url as $page) {
			$currentPath = Path::getPage($page,$currentPath);
			if ($currentPath == null) return null;
		}
		
		return $currentPath;
	}
	/**
	 * Получить страницу
	 * @param $page - Псевданим страницы
	 * @param $parentPath - Родитель страницы
	 * @return \Path
	 */
	public static function getPage($page, $parentPath = null) {
		if ($parentPath != null && !($parentPath instanceof Path) )
			throw new \InvalidArgumentException('$parentPath must be null or \Path');
		$db = \database::getInstance();
		$cond = $db->setCondition('OR');
		
		// Условие для обычного типа страниц
		$var = $cond->addNode('AND');
		if ($parentPath == null)
			$var->add("parent","=",0);
		else
			$var->add("parent","=",$parentPath->getID());
		
		// Условие для страниц типа variable
		$stat_or_var = $var->addNode('OR');
		$stat_or_var->add("alias","=",$page);
		$stat_or_var->add("type","<>",0);
		
		// Условие для бесконечно вложенных страниц
		$infinity = $cond->addNode('AND');
		if ($parentPath == null)
			$infinity->add("id","=",0);
		else
			$infinity->add("id","=",$parentPath->getID());
		$infinity->add("type","=","2");
		
		$db->sort("type");
		
		
		$query = $db->select($db->getTableAlias('paths'));
		$db->clear();

		if ($query == null && !isset($query[0])) return null;
		$executor = null;
		$executor_path = root.str_replace(".","/",$query[0]['executor']).".class.php";
		if (file_exists($executor_path)) {
			include_once($executor_path);
			$executor_path = explode("/",$query[0]['executor']);
			$executor_name = "\\".str_replace(".","\\",end($executor_path));
			if (class_exists($executor_name)) {
				$params = array();
				$params_row = explode("&",$query[0]['params']);
				foreach ($params_row as $param) {
					$param = explode("=",$param,2);
					if (count($param) == 2)
						$params[urldecode($param[0])] = urldecode($param[1]);
					else if (count($param) == 1)
						$params[urldecode($param[0])] = null;
				}
				$executor = array("#executor" => $executor_name, "db" => $db, "params" => $params);
			} else
				throw new \Exception("Класс исполнителя '".$executor_name."' не найден");
		} else
			throw new \Exception("Исполнитель по пути '".$executor_path."' не найден");
		
		return new Path($query[0]['id'],$page,$executor,$parentPath);
	}
	/**
	 * Добавить страницу
	 * @param $alias - 
	 * @param $parent - int - Родитель страницы
	 * @param $executor - string - Путь к обработчику страницы
	 * @param $type - 0,1,2 - Тип страницы
	 * @param $params - array() - Параметры страницы
	 */
	public static function addPage($alias,$parent,$executor,$type = 0,$params = array()) {
		if (!is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if (!is_string($executor)) throw new \InvalidArgumentException('$executor must be string');
		switch ($type) {
			case 0:case 1:case 2:break;
			default:throw new \InvalidArgumentException('$type must be 0 or 1 or 2');
		}
		if (!is_string($params) && !is_array($params)) throw new \InvalidArgumentException('$executor must be string or array');
		if (is_array($params)) {
			$params_str = "";
			foreach ($params as $key=>$param) {
				if ($params_str != "") $params_str .= "&";
				$params_str .= urlencode($key) . "=" . urlencode($param);
			}
			$params = $params_str;
			unset($params_str);
		}

		$executor_path = root.str_replace(".","/",$executor).".class.php";
		if (!file_exists($executor_path)) {
			throw new \Exception("Файл обработчика страницы \"". $executor_path ."\" не найден.");
		}
		include_once($executor_path);
		$executor_path = explode("/",$executor);
		$executor_name = "\\".str_replace(".","\\",end($executor_path));
		if (!class_exists($executor_name)) {
			throw new \Exception("Класс обработчика страницы \"". $executor_name ."\" не найден.");
		}

		$arr = array(
			"alias" => $alias,
			"parent" => $parent,
			"executor" => $executor,
			"type" => $type,
			"params" => $params
		);
		$db = \database::getInstance();
		$result = $db->insert($db->getTableAlias('paths'),$arr);
		$db->clear();
		return $result;
	}
	/**
	 * Получить количество страниц
	 * @return int
	 */
	public static function countPages($parent = null) {
		$db = \database::getInstance();
		if (is_int($parent)) {
			$cond = $db->setCondition();
			$cond->add("parent","=",$parent);
		}
		$query = $db->select(
			$db->getTableAlias('paths'),
			array(
				"cout" => "Count(*)"
			)
		);
		$db->clear();
		if (isset($query[0]["cout"]))
			return (int)$query[0]["cout"];
		else return 0;
	}
	/**
	 * Получить сырые данные страницы
	 * @param $id - Идентификатор страницы
	 */
	public static function getPageRaw($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$db = \database::getInstance();
		$cond = $db->setCondition();
		$cond->add("id","=",$id);
		$query = $db->select($db->getTableAlias('paths'));
		$db->clear();
		if (isset($query[0])) return $query[0];
		return $query;
	}
	/**
	 * Список всех страниц.
	 * @param $page - Номер страницы
	 * @param $perPage - Количество элементов на странице
	 * @return array()
	 */
	public static function listPagesRaw($page = 1, $perPage = 20, $parent = null) {
		if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
		if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
		$db = \database::getInstance();
		if (is_int($parent)) {
			$cond = $db->setCondition();
			$cond->add("parent","=",$parent);
		}
		$db->setPage($page,$perPage);
		$query = $db->select($db->getTableAlias('paths'));
		$db->clear();
		return $query;
	}
	/**
	 * Удалить страницу
	 * @param $id - Идентификатор страницы
	 */
	public static function delPage($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$db = \database::getInstance();
		$cond = $db->setCondition();
		$cond->add("id","=",$id);
		$result = $db->delete($db->getTableAlias('paths'));
		$db->clear();
		return $result;
	}
	/**
	 * Редактировать страницу
	 * @param $id - Идентификатор страницы
	 * @param $alias - 
	 * @param $parent - int - Родитель страницы
	 * @param $executor - string - Путь к обработчику страницы
	 * @param $type - 0,1,2 - Тип страницы
	 * @param $params - array() - Параметры страницы
	 */
	public static function editPage($id,$alias = null,$parent = null,$executor = null,$type = null,$params = null) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if ($alias != null && !is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		if ($parent != null && !is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if ($executor != null && !is_string($executor)) throw new \InvalidArgumentException('$executor must be string');
		if (is_int($type))
			switch ($type) {
				case 0:case 1:case 2:break;
				default:throw new \InvalidArgumentException('$type must be 0 or 1 or 2');
			}
		if ($params != null) {
			if (!is_string($params) && !is_array($params)) throw new \InvalidArgumentException('$params must be string or array');
			if (is_array($params)) {
				$params_str = "";
				foreach ($params as $key=>$param) {
					if ($params_str != "") $params_str .= "&";
					$params_str .= urlencode($key) . "=" . urlencode($param);
				}
				$params = $params_str;
				unset($params_str);
			}
		}

		if ($executor != null) {
			$executor_path = root.str_replace(".","/",$executor).".class.php";
			if (!file_exists($executor_path)) {
				throw new \Exception("Файл обработчика страницы \"". $executor_path ."\" не найден.");
			}
			include_once($executor_path);
			$executor_path = explode("/",$executor);
			$executor_name = "\\".str_replace(".","\\",end($executor_path));
			if (!class_exists($executor_name)) {
				throw new \Exception("Класс обработчика страницы \"". $executor_name ."\" не найден.");
			}
		}

		$arr = array();

		if ($alias != null) $arr["alias"] = $alias;
		if (is_int($parent)) $arr["parent"] = $parent;
		if ($executor != null) $arr["executor"] = $executor;
		if (is_int($type)) $arr["type"] = $type;
		if ($params != null) $arr["params"] = $params;

		if (count($arr) > 0) {
			$db = \database::getInstance();
			$cond = $db->setCondition();
			$cond->add("id","=",$id);
			$result = $db->update($db->getTableAlias('paths'),$arr);
			$db->clear();
			return $result;
		}
	}
}
class PathNotValidatedException extends \Exception {
	private $PAGE;
	public function __construct($message, $error_page_path = null, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->PAGE = $error_page_path;
	}
	public function getPage() {
		return $this->PAGE;
	}
}
