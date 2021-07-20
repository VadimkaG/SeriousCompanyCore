<?php
class Path extends \stored_object\StoredObject {

	protected $url = "";
	protected $urlArr = [];

	public static function databaseStructure() {
		return [
			"name" => "paths",
			"fields" => [
				"url"      => [ "varchar", "not null", 255      ],
				"variable" => [ "boolean", "default 0 not null" ],
				"executor" => [ "varchar", "not null", 255      ],
				"params"   => [ "text", "not null"              ]
			],
			"indexes" => [
				"searchByURL" => [ "url" ]
			]
		];
	}
	/**
	 * Получить URL, по которому была вызвана страница
	 * @return string
	 */
	public function URL() {
		return $this->url;
	}
	/**
	 * Получить URL, по которому вызвана страница, в виде массива
	 * @return array
	 */
	public function URLarr() {
		return $this->urlArr;
	}
	/**
	 * Загрузить исполнитель страницы
	 * @return \PathExecutor
	 */
	public function loadExecutor() {

		$executor_path = root.str_replace(".","/",$this->get("executor")).".class.php";
		if (file_exists($executor_path)) {
			include_once($executor_path);
			$executor_path = explode("/",$this->get("executor"));
			$executor_name = "\\".str_replace(".","\\",end($executor_path));

			if (!class_exists($executor_name))
				throw new \Exception("Класс исполнителя '".$executor_name."' не найден");

			// Преобразовываем параметры в массив
			$params = array();
			$params_row = $this->get("params");
			if ($params_row !== null && strlen($params_row) > 0) {
				$params_row = explode("&",$params_row);
				foreach ($params_row as $param) {
					$param = explode("=",$param,2);
					if (count($param) == 2)
						$params[urldecode($param[0])] = urldecode($param[1]);
					else if (count($param) == 1)
						$params[urldecode($param[0])] = null;
				}
			}
			unset($params_row);

			return new $executor_name($GLOBALS["MYSQLI_CONNECTION"],$this,$params);

		} else
			throw new \Exception("Исполнитель по пути '".$executor_path."' не найден");
	}
	/**
	 * Загрузить исполнитель страницы по URI
	 * @param string $url - URL по которому производится поиск
	 * @param array $ignore = [] - Список URl, которые будут проигнорированы
	 * @param string $realUrl = "" - Оригинальная ссылка, без всяких изменений
	 */
	public static function findExecutorFromURL(string $url, array $ignore = [], string $realUrl = "") {

		if (strlen($realUrl) < 1) $realUrl = $url;

		// Если в конце мешающий слэш - стираем его
		if (substr($url,-1) == "/")
			$url = substr($url,0,-1);

		$paths = Path::loadFromURL($url,$ignore,$realUrl);
		if (count($paths) < 1)
			$paths = Path::loadFromURL("not_found");

		if (count($paths) < 1) return null;

		foreach ($paths as $path) {
			$executor = $path->loadExecutor();

			// Вызываем проверку у исполнителя
			try {
				if ($executor->validate()) {

					// Если это промежуточное звено
					if ($path->get("variable") && substr($path->get("url"), -1) == "$") {
						$URIarr = explode("/",$url);
						$URIPatharr = explode("/",$path->get("url"));

						// Проверяем является ли текущая страница концом url
						if (count($URIarr) > count($URIPatharr)) {

							// Необходимо подправить текущий путь, чтобы корректно найти вложенные
							$aliasKey = count($URIPatharr)-1;
							$URIarr[$aliasKey] = "$";

							// Ищем вложенные исполнители
							$ignore[] = $path->get("url");
							$executor = Path::findExecutorFromURL(implode("/",$URIarr),$ignore,$realUrl);
							return $executor;

						// Если текущая страница является концом
						} else
							return $executor;

					// Если это не промжуточное звено
					} else
						return $executor;
				}
			} catch (\PathNotValidatedException $exception) {
				if ($exception->getPage() !== null) {
					$executor = $exception->getPage()->loadExecutor();
					if ($executor !== null) return $executor;
				}
			}
		}

		// Если ни один исполнитель не найден
		if ($url !== "not_found")
			return Path::findExecutorFromURL("not_found");

		// Если не найден даже исполнитель страницы, которая отвечает за ошибку "Страница не найдена"
		return null;
	}
	/**
	 * Загрузить путь по URI
	 * @param string $url - URL по которому производится поиск
	 * @param array $ignore = [] - Список URl, которые будут проигнорированы
	 * @param string $realUrl = "" - Оригинальная ссылка, без всяких изменений
	 * @return array( \Path )
	 */
	public static function loadFromURL(string $url,array $ignore = [], string $realUrl = "") {
		if (substr($url, -1) == "/")
			$url = substr($url, 0,-1);

		$URIarr = explode("/",$url);

		if (strlen($realUrl) < 1) $realUrl = $url;

		$suggestions = [ $url ];
		$current = "";
		$first = true;
		foreach ($URIarr as $alias) {
			if ($first) {
				$first = false;
				$current = $alias;
				$test[] = $current;
				continue;
			} else {
				$suggestions[] = $current."/$";
			}
			$current = $current."/".$alias;
		}

		$cond = Path::createCondition();
		$cond->add("url",$suggestions,"IN");
		if (count($ignore) > 0)
			$cond->add("url",$ignore,"NOT IN");
		$paths = Path::loadByCondition($cond);

		foreach ($paths as &$path) {
			$path->url = $realUrl;
			$path->urlArr = $URIarr;
		}

		return $paths;
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