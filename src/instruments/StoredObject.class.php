<?php
namespace stored_object;
abstract class StoredObject {
	/**
	 * Идентификатор в базе данных
	 */
	protected $ID;
	/**
	 * Список полей
	 */
	protected $fields;
	/**
	 * Создать новый объект
	 * array $fields - Поля
	 * int $id - Идентификатор в базе данных
	 * @return \Object
	 */
	public function __construct(array $fields = [], int $id = null) {
		$structure = static::databaseStructure();
		if (!is_array($structure["fields"]) || count($structure["fields"]) < 0)
			throw new \Exception("Count fields in struct < 0");

		$this->fields = [];
		$this->ID = $id;

		foreach ($structure["fields"] as $field => $params) {
			$this->fields[$field] = null;
		}

		foreach($fields as $alias=>$value) {
			if ($alias == "id") continue;
			$this->set($alias,$value);
		}
	}
	/**
	 * Получаем структуру в базе данных
	 * @return array (
	 *        name - Имя таблицы
	 *        fields - Поля
	 *            key - Имя поля
	 *           	0   - Тип поля
	 *            1   - Параметры sql
	 *            2   - Размер поля
	 *        indexes - Индексы
	 *            key - Имя индекса
	 *            value - Массив со списоком полей индекса
	 *    )
	 */
	public static abstract function databaseStructure();
	/**
	 * Получить идентификатор объекта
	 * @return long or null
	 */
	public function id() {
		return $this->ID;
	}
	/**
	 * Полудчить список полей
	 * @return array
	 */
	public function fields() {
		return $this->fields;
	}
	/**
	 * Получить поле
	 * @param string $alias - Псевданим поля
	 * @return ?
	 */
	public function get(string $alias) {
		return isset($this->fields[$alias])?$this->fields[$alias]:null;
	}
	/**
	 * Установить значение поля
	 * @param string $alias - Псевданим поля
	 * @param $value - Значение
	 */
	public function set(string $alias, $value) {
		$structure = static::databaseStructure();
		if (!is_array($structure["fields"]) || count($structure["fields"]) < 0)
			throw new \Exception("Count fields in struct < 0");
		if (!isset($structure["fields"][$alias])) throw new \Exception("Unknown field ".$alias);
		$this->fields[$alias] = $value;
	}
	/**
	 * Сохранить объект в базе данных
	 */
	public function save() {
		if (count($this->fields) < 1) return;

		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");

		$result = \Event::call("StoredObject_presave",$this);
		foreach ($result as $row) {
			if (isset($row) && is_bool($row) && $row == true)
				return;
		}

		// Создать новый объект
		if ($this->ID === null) {
			$keys = "";
			$values = "";
			foreach ($this->fields as $key=>$field) {
				if ($keys != "") $keys .= ",";
				$keys .= $key;
				if ($values != "") $values .= ",";
				$values .= Condition::getField($field);
			}
			$GLOBALS["MYSQLI_CONNECTION"]->query("INSERT INTO ".$structure["name"]." (".$keys.") VALUES (".$values.")");

			$this->ID = $GLOBALS["MYSQLI_CONNECTION"]->insert_id;

			if ($GLOBALS["MYSQLI_CONNECTION"]->error)
				throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);

		// Сохранить существующий объект
		} else {
			$values = "";
			foreach ($this->fields as $key=>$field) {
				if ($values != "") $values .= ",";
				$values .= $key."=".Condition::getField($field);
			}
			$GLOBALS["MYSQLI_CONNECTION"]->query("UPDATE ".$structure["name"]." SET ".$values." WHERE id=".$this->ID);

			if ($GLOBALS["MYSQLI_CONNECTION"]->error)
				throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);
		}
		\Event::call("StoredObject_postsave",$this);
	}
	/**
	 * Удалить объект из базы данных
	 */
	public function delete() {
		$result = \Event::call("StoredObject_predelete",$this);
		foreach ($result as $row) {
			if (isset($row) && is_bool($row) && $row == true)
				return;
		}
		if ($this->ID !== null) {
			$structure = static::databaseStructure();
			if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");
			$GLOBALS["MYSQLI_CONNECTION"]->query("DELETE FROM ".$structure["name"]." WHERE id=".$this->ID);

			if ($GLOBALS["MYSQLI_CONNECTION"]->error)
				throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);
		}
		\Event::call("StoredObject_postdelete",$this);
	}
	/**
	 * Отклонировать текущий объект
	 */
	public function clone() {
		return new static($this->fields, $this->ID);
	}
	/**
	 * Удалить под условием
	 * @param $condition - Объект условий
	 */
	public static function deleteByCondition($condition) {
		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");
		$GLOBALS["MYSQLI_CONNECTION"]->query("DELETE FROM ".$structure["name"]." WHERE ".$condition->getSQL());

		if ($GLOBALS["MYSQLI_CONNECTION"]->error)
			throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);
	}
	/**
	 * Инсталировать хранилище для объекта
	 */
	public static function install() {
		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");
		if (!is_array($structure["fields"]) || count($structure["fields"]) < 1) throw new \Exception("Count fields in struct < 0");
		$queryStr = "CREATE TABLE IF NOT EXISTS ".$structure["name"]." (id serial primary key";
		$first = true;
		foreach ($structure["fields"] as $field => $paramsArray) {
			if (!is_array($paramsArray)) throw new \Exception("params must be array");
			if (!isset($paramsArray[0])) throw new \Exception("undefinded type for ".$field);
			$paramsStr = static::convertFieldTypeToSQL($paramsArray);

			if (strlen($paramsStr) > 0)
				$queryStr .= ",".$field." ".$paramsStr;
		}

		$GLOBALS["MYSQLI_CONNECTION"]->query($queryStr.")");

		if ($GLOBALS["MYSQLI_CONNECTION"]->error)
			throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);

		if (isset($structure["indexes"]) && is_array($structure["indexes"]) && count($structure["indexes"]) > 0) {
			foreach ($structure["indexes"] as $alias=>$fields) {
				if (!is_array($fields)) continue;
				$GLOBALS["MYSQLI_CONNECTION"]->query("CREATE INDEX IF NOT EXISTS search_page on ". $alias ."(".implode(",",$fields).")");
				if ($GLOBALS["MYSQLI_CONNECTION"]->connect_errno)
					throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->connect_error);
			}
		}
	}
	/**
	 * Деинсталировать объект из хранилища
	 */
	public static function uninstall() {
		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");

		if (isset($structure["indexes"]) && is_array($structure["indexes"])) {
			foreach ($structure["indexes"] as $index=>$params) {
				$GLOBALS["MYSQLI_CONNECTION"]->query("DROP INDEX IF EXISTS " . $index);
			}
		}

		$GLOBALS["MYSQLI_CONNECTION"]->query("DROP TABLE IF EXISTS " . $structure["name"]);

		if ($GLOBALS["MYSQLI_CONNECTION"]->connect_errno)
			throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->connect_error);
	}
	/**
	 * Отдает объект базы данных
	 * @return mysqli
	 */
	protected function database() {
		return $GLOBALS["MYSQLI_CONNECTION"];
	}
	/**
	 * Преобразуем типы объекта в типы MYSQl данных
	 * @param $fieldParams - Параметры поля
	 * @return string - строка параметров sql
	 */
	protected static function convertFieldTypeToSQL($fieldParams) {
		switch (strtolower($fieldParams[0])) {
			case "date":
			case "time":
			case "datetime":
			case "year":

			case "tinytext":
			case "mediumtext":
			case "largetext":

			case "char":
			case "varchar":

			case "tinyint":
			case "smallint":
			case "mediumint":
			case "int":
			case "bigint":

			case "decimal":
			case "float":
			case "double":

			case "tinyblob":
			case "blob":
			case "mediumblob":
			case "largeblob":
			 $fieldParams[0] = strtolower($fieldParams[0]);
			 break;
			case "integer":
			 $fieldParams[0] = "int";
			 break;
			case "bool":
			case "boolean":
			 $fieldParams[0] = "tinyint";
			 if (!isset($fieldParams[1])) $fieldParams[1] = "default 0";
			 $fieldParams[2] = 1;
			 break;
			case "connection":
				$fieldParams = [ "bigint" ];
				break;
			default:
			 $fieldParams[0] = "text";
		}
		if (isset($fieldParams[2])) {
			$fieldParams[3] = "(".$fieldParams[2].")";

			// Производим ракировку
			$fieldParams[2] = $fieldParams[1];
			$fieldParams[1] = $fieldParams[3];
			unset($fieldParams[3]);
		}

		return implode(" ",$fieldParams);
	}
	/**
	 * Обработать значение, что пришло из базы данных
	 * @param $rawField - Необработанное значение
	 * @param $params - Параметры поля
	 * @return ?
	 */
	protected static function convertRawToTypedField($rawField,$params) {
		if (isset($rawField)) {
			switch (strtolower($params[0])) {
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "int":
				case "bigint":
					return (int)$rawField;

				case "decimal":
				case "float":
				case "double":
					return (float)$rawField;

				case "bool":
				case "boolean":
					return (int)$rawField>0?true:false;

				case "connection":
					if (!isset($params[1]))
						throw new \Exception("Not found 2 argument for field type connection");
					return new Connection((int)$rawField,$params[1]);

				default:
					return $rawField;
			}
		}
	}
	/**
	 * Загрузить объект, по его идентификатору
	 * @param int $id - Идентификатор объекта
	 * @return \stored_object\StoredObject or null
	 */
	public static function load(int $id) {
		$condition = static::createCondition();
		$condition->add("id",$id);
		$result = static::loadByCondition($condition);
		if (count($result) > 0) {
			return array_shift($result);
		}
	}
	/**
	 * Загрузить объекты по их идентификатору
	 * @param array( int ) $ids - Список идентификаторов
	 * @return array( int => \stored_object\StoredObject )
	 */
	public static function loadMultiple(array $ids) {
		if (count($ids) < 1) return [];
		$condition = static::createCondition();
		$condition->add("id",$ids,"IN");
		return static::loadByCondition($condition);
	}
	/**
	 * Загрузить, используя параметры
	 * @param array $params
	 * @param string $condition = "AND" - Условие
	 * @return array( int => \stored_object\StoredObject )
	 */
	public static function loadByParams(array $params,string $condition = "AND") {
		$condition = static::createCondition($condition);
		foreach ($params as $key=>$param) {
			if (is_array($param)) {
				if (count($param) > 1) {
					$condition->add($param[0],$param[1]);
				} elseif (count($param) > 2) {
					$condition->add($param[0],$param[1],$param[2]);
				}
			} else
				$condition->add($key,$param);
		}
		return static::loadByCondition($condition);
	}
	/**
	 * Загрузить объекты по продвинутой выборке
	 * @param $condition - Объект условий
	 * @param int $perPage = -1 - Количество элементов на странице
	 * @param int $page = 1 - Страница
	 * @param array( fieldAlias => asc\desc ) $sort = [] - Сортировка объектов
	 * @param array( fieldAlias ) $group = [] - Группировка объектов
	 * @param bool $loadConnections = false - Также предзагрузить все связи с другими объектами
	 * @return array( int => \stored_object\StoredObject )
	 */
	public static function loadByCondition($condition = null,int $perPage = -1,int $page = 1,array $sort = [],array $group = [], bool $loadConnections = false) {
		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");
		if (!is_array($structure["fields"]) || count($structure["fields"]) < 0)
			throw new \Exception("Count fields in struct < 0");

		// WHERE
		$conditionStr = "";
		if ($condition != null)
			$conditionStr = $condition->getSQL();
		if ($conditionStr != "")
			$conditionStr = " WHERE ".$conditionStr;

		// GROUP BY
		if (count($group) > 0)
			$groupby = " GROUP BY ".implode(',',$group);
		else
			$groupby = "";

		// ORDER BY
		$orderby = "";
		foreach ($sort as $field=>$type) {
			if ($orderby !== "")
				$orderby .= ",";
			$orderby .= $field;
			if (is_string($type)) {
				if (strtoupper($type) == "DESC")
					$orderby .= " DESC";
				else
					$orderby .= " ASC";
			} elseif ($type)
				$orderby .= " ASC";
			else
				$orderby .= " DESC";
		}
		if ($orderby !== "")
			$orderby = " ORDER BY ".$orderby;

		// LIMIT
		$limit = "";
		if ($perPage > 0)
			$limit = " LIMIT ".( ($page*$perPage)-$perPage ).", ".$perPage;

		// Список полей
		$fieldsStr = "id";
		foreach ($structure["fields"] as $field=>$params) {
			$fieldsStr .= ",".$field;
		}

		$query = "SELECT ".$fieldsStr." FROM ".$structure["name"].$conditionStr.$groupby.$orderby.$limit;
		$result = $GLOBALS["MYSQLI_CONNECTION"]->query($query);

		if ($GLOBALS["MYSQLI_CONNECTION"]->error)
			throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);

		if ($result === false)
			throw new \Exception("SQL запрос вернул false: ".$query);

		// Идентификаторы объектов, которые нужно предзагрузить в по связям
		$connections = [];
		$connectionsTypes = [];
		$connectionFields = [];

		$list = [];
		while($row = $result->fetch_assoc()) {
			foreach ($structure["fields"] as $alias=>$params) {
				if (isset($row[$alias])) {
					// Получаем все id, коорые надо предзагрузить
					if ($loadConnections && $params[0] == "connection") {
						$connections[$params[1]][(int)$row[$alias]] = (int)$row[$alias];
						$connectionFields[$alias] = $params[1];
					}
					$row[$alias] = static::convertRawToTypedField($row[$alias],$params);
				}
			}
			$obj = new static($row,(int)$row["id"]);
			$list[$obj->ID] = $obj;
		}
		$result->close();

		// Подгружаем все связи
		if ($loadConnections) {
			// Подгружаем все связи
			$connectionsRendered = [];
			foreach ($connections as $type=>$arr) {
				$connType = $type;
				$condition = $connType::createCondition();
				$condition->add("id",$arr,"IN");
				$rendered = $connType::loadByCondition($condition);
				foreach ($rendered as $row) {
					$connectionsRendered[$type][$row->id()] = $row;
				}
				unset($rendered);
			}
			unset($connections);

			// Заполняем связи
			foreach ($list as &$item) {
				foreach ($connectionFields as $field=>$type) {
					$obj = $item->get($field);
					if ($obj->id() !== null)
						$item->set($field,$obj->set($connectionsRendered[$type][$obj->id()]));
				}
			}
		}

		return $list;
	}
	/**
	 * Посчитать количество объектов
	 * @param $condition - Объект условий
	 * @param array( fieldAlias ) $group = [] - Группировка объектов
	 * @return int
	 */
	public static function count($condition = null,array $group = []) {
		$structure = static::databaseStructure();
		if (!is_string($structure["name"])) throw new \Exception("Table name not found in structure");

		// WHERE
		$conditionStr = "";
		if ($condition != null)
			$conditionStr = $condition->getSQL();
		if ($conditionStr != "")
			$conditionStr = " WHERE ".$conditionStr;

		// GROUP BY
		if (count($group) > 0)
			$groupby = " GROUP BY ".implode(',',$group);
		else
			$groupby = "";

		$result = $GLOBALS["MYSQLI_CONNECTION"]->query("SELECT Count(*) c FROM ".$structure["name"].$conditionStr.$groupby);

		if ($GLOBALS["MYSQLI_CONNECTION"]->error)
			throw new \Exception($GLOBALS["MYSQLI_CONNECTION"]->error);

		$row = $result->fetch_assoc();
		$result->close();

		if (!$row) throw new \Exception("count result == null");

		return (int)$row["c"];
	}
	/**
	 * Создать новое условие
	 * @return \stored_object\Condition
	 */
	public static function createCondition($condition = "AND") {
		return new Condition($condition);
	}
}