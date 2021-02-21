<?PHP
// version 1.3
class database {
	
	// Инициализация
	private $connect_data = array();
	private $connection = null;
	private $status = false;
	private $lastQuery = "";
	private $pNavigator;
	private $WhereNode;
	private $OrderBy;
	private $GroupBy;
	private $tableAliases;
	private $charset;
	private static $instance = null;
	
	/**
	 * Создать объект базы данных
	 * @param $host - Хост базы данных
	 * @param $user - Логин пользователя базы данных
	 * @param $password - Пароль базы данных
	 * @param $database - Имя базы данных
	 * @param $databaseAliases - Алиасы таблиц базы данных
	 */
	public function __construct($host, $user, $password, $database = "", $databaseAliases = array(), $charset = null) {
		$this->connect_data['host'] = $host;
		$this->connect_data['user'] = $user;
		$this->connect_data['password'] = $password;
		$this->connect_data['database'] = $database;
		$this->tableAliases = $databaseAliases;
		$this->charset = $charset;
		$this->clear();
	}
	/**
	 * Получить объект базы данных
	 */ 
	public static function getInstance($host = null, $user = null, $password = null, $database = "", $databaseAliases = array(), $charset = null) {
		if (self::$instance != null) return self::$instance;
		else if ($host != null && $user != null && $password != null)
			self::$instance = new database($host,$user,$password,$database,$databaseAliases, $charset);
		return self::$instance;
	}
	
	// =================
	
	// Основные функции
	/**
	 * Подключиться к базе дыннх
	 * @return boolean - Успешно ли подключение
	 */
	public function connect() {
		if ($this->connect_data['host'] == null || $this->connect_data['user'] == null) return false;
		if ($this->isConnect()) $this->disconnect();
		$this->connection = new MySQLI($this->connect_data['host'],$this->connect_data['user'],$this->connect_data['password'],$this->connect_data['database']);
		if ($this->connection->connect_errno) {
			throw new databaseExceptuion($this->connection->connect_error);
			$this->connection = null;
			$this->status = false;
		}
		else $this->status = true;
		if (is_string($this->charset))
			$this->row_query("SET CHARACTER SET '".$this->charset."'");
		return $this->status;
	}
	/**
	 * Отключиться от базы данных
	 */
	public function disconnect() {
		if (!$this->isConnect()) return null;
		if ($this->connection)
			$this->connection->close();
		$this->connection = null;
		$this->status = false;
	}
	/**
	 * Запрос без каких либо других манипуляций
	 * @param $query - SQL запрос
	 * @return Ответ
	 */
	public function row_query($query) {
		if (!$this->isConnect() && !$this->connect()) return false;
		return $this->connection->query($query);
	}
	/**
	 * Аодключена ли база
	 * @return boolean
	 */
	public function isConnect() {
		return $this->status;
	}
	/**
	 * Вывести последний запрос в базу
	 * row_query() не сохраняется
	 * @return string
	 */
	public function getLastQuery() {
		return $this->lastQuery;
	}
	// =================
	
	/**
	 * Установить условие выборки
	 * @param $condition - AND или OR
	 * @return \databaseWhereNode
	 */
	public function setCondition($condition = 'AND') {
		if ($this->WhereNode)
			return $this->WhereNode;
		else
			return $this->WhereNode = new databaseWhereNode($condition);
	}
	/**
	 * Получить установленные условия
	 * в строке. Для SQL запроса
	 * @return \databaseWhereNode
	 */
	private function getCondition() {
		if ($this->WhereNode) {
			$cond = $this->WhereNode->getSQL();
			if ($cond != "") $cond = " WHERE ".$cond;
		} else
			$cond = "";
		if ($this->GroupBy)
			$cond .= " ".$this->GroupBy;
		if ($this->OrderBy)
			$cond .= " ".$this->OrderBy;
		if ($this->pNavigator['isset']) $cond .= " LIMIT ".$this->pNavigator['start'].",".$this->pNavigator['perPage'];
		return $cond;
	}
	/**
	 * Получить имя таблицы по ее алиасу
	 * @param $tableAlias - Получить имя таблицы по ее алиасу
	 * @return string
	 */
	public function getTableAlias($tableAlias) {
		if (isset($this->tableAliases[$tableAlias])) return $this->tableAliases[$tableAlias];
		else return $tableAlias;
	}
	
	// Функции отбора
	/**
	 * Очистить все условия
	 */
	public function clear() {
		$this->WhereNode = null;
		$this->OrderBy = "";
		$this->GroupBy = "";
		$this->pNavigator['isset'] = false;
	}
	/**
	 * Вывод определенной страницы
	 * @param $page - Номер страницы
	 * @param $perPage - Количество элементов на странице
	 */
	public function setPage($page,$perPage = 20) {
		$this->pNavigator['isset'] = true;
		$this->pNavigator['page'] = $page;
		$this->pNavigator['start'] = ($page*$perPage)-$perPage;
		$this->pNavigator['perPage'] = $perPage;
	}
	/**
	 * Установить сортировку данных
	 * @param $fields - Данные по которым будет сортировка
	 * @param $asc - true - Сортировка по возрастанию. false - по убыванию
	 */
	public function sort($fields,$asc=true) {
		if (!is_string($fields)) $fields = (string)$fields;
		if ($asc) $asc = "ASC";
		else $asc = "DESC";
		if ($this->OrderBy != '')
			$this->OrderBy .= ', '.$fields.' '.$asc;
		else
			$this->OrderBy = 'ORDER BY '.$fields.' '.$asc;
	}
	/**
	 * Сгрупировать данные
	 * @param $fields - Данные
	 */
	public function groupBy($fields) {
		if (is_array($fields)) $fields = implode(',',$fields);
		else if (!is_string($fields)) $fields = (string)$fields;
		$this->GroupBy = 'GROUP BY '.$fields;
	}
	// ===============================
	
	// Функции обращения
	/**
	 * Запрос в базу данных
	 * @param $query - Запрос на SQL
	 * @param $isReturnId - Возвращать ли значение (false)
	 * @return connection->query()
	 */
	public function query($query,$isReturnId = false) {
		$this->lastQuery = $query;
		if(!$this->isConnect() && !$this->connect()) return false;
		$result = $this->connection->query($query);
		if ($this->connection->error)
			throw new databaseExceptuion($this->connection->error, $query);
		if ($result == true && $isReturnId)
			$result = $this->connection->insert_id;
		$this->disconnect();
		return $result;
	}
	/**
	 * Выборка из базы
	 * Условия задаются через setCondition()
	 * Сортировка задается через sort()
	 * Группировка полей задается через groupBy()
	 * Выборка определенной страницы осуществляется через setPage()
	 * @param $table - список таблиц - array()
	 * @param $poles - поля - array()
	 * @return array()
	 */
	public function select($table,$poles = "*") {
		if (is_array($poles)) {
			$str = '';
			foreach ($poles as $key=>$value) {
				if ($str != '') $str .=',';
				if (is_string($key)) {
					$str .= $value.' '.$key;
				} else
					$str .= $value;
			}
			$poles = $str;
			unset($str);
		}
		if (is_array($table)) {
			$str = '';
			foreach ($table as $key=>$value) {
				if ($str != '') $str .=',';
				if (is_string($key)) {
					$str .= $value.' '.$key;
				} else
					$str .= $value;
			}
			$table = $str;
			unset($str);
		}
		$q = $this->query("SELECT ".$poles." FROM ".$table.$this->getCondition());
		if ($q == null) return asd;
		$data = $q->fetch_all(MYSQLI_ASSOC);
		$q->close();
		return $data;
	}
	/**
	 * Занесение данных в базу
	 * @param $table - таблица в базе
	 * @param $fields - Данные, которые будут занесены в базу
	 * @param $raw - true - не будет автоматически преобразовывать типы переменных
	 * (поля должны передаваться в том же порядке, что и значения)
	 * @return $this->query()
	 */
	public function insert($table,$fields,$raw = false) {
		if (is_array($table)) $table = implode(',',$table);
		else if (!is_string($table)) $table = (string)$table;
		$str_poles = "";
		$str_values = "";
		$first = true;
		foreach ($fields as $key=>$field) {
			if ($first == false) {
				$str_poles .= ",";
				$str_values .= ",";
			} $first = false;
			$str_poles .= $key;
			if ($raw)
				$str_values .= $field;
			else
				$str_values .= databaseWhereNode::getField($field);
		}
		if ($str_poles != "") $str_poles = "(".$str_poles.")";
		if ($str_values != "") $str_values = "(".$str_values.")";
		else return false;
		return $this->query("INSERT INTO ".$table.$str_poles." VALUES ".$str_values,true);
	}
	/**
	 * Обновить данные в базе
	 * Условия задаются через setCondition()
	 * @param $table - Таблица в которой будут обновлены данные
	 * @param $values - Данные, которые будут обновлены - array()
	 * @return $this->query()
	 */
	public function update($table,$values,$row=false) {
		if ($this->WhereNode) $where = ' WHERE '.$this->WhereNode->getSQL();
		else $where = '';
		if (is_array($table)) $table = implode(',',$table);
		else if (!is_string($table)) $table = (string)$table;
		$str_values = '';
		if (is_array($values)) {
			foreach($values as $key=>$value) {
				if ($str_values != '') $str_values .= ',';
				if ($row)
					$str_values .= $key.' = '.$value;
				else
					$str_values .= $key.' = '.databaseWhereNode::getField($value);
			}
		} else return false;
		return $this->query("UPDATE ".$table." SET ".$str_values.$where);
	}
	/**
	 * Удалить данные из базы
	 * Условия задаются через setCondition()
	 * @param $table - Таблица из которых будут удалены данные
	 */
	public function delete($table) {
		if ($this->WhereNode) $where = ' WHERE '.$this->WhereNode->getSQL();
		else $where = '';
		if (is_array($table)) $table = implode(',',$table);
		else if (!is_string($table)) $table = (string)$table;
		return $this->query("DELETE FROM ".$table.$where);
	}
	// =====================
	
}
class databaseWhereNode {
	private $condition;
	private $items;
	private $nodes;
	private $inBrackets;
	public function __construct($condition,$inBrackets = false) {
		switch ($condition) {
			case "O":
			case "o":
			case "OR":
			case "or":
				$this->condition = "OR";
				break;
			default:
				$this->condition = "AND";
		}
		$this->items = array();
		$this->nodes = array();
		$this->inBrackets = $inBrackets;
	}
	/**
	 * Добавить условие
	 * @param $item - Имя поля
	 * @param $operator - Оператор
	 * @param $value - Значение (тип определяется автоматически: string, integer, array)
	 * @param $raw - Установить в true, еесли необходимо $value передать в чистом виде
	 */
	public function add($item,$operator,$value,$raw=false) {
		if ($operator == "!=") $operator = "<>";
		if (!$raw) $value = databaseWhereNode::getField($value);
		$this->items[] = array(
			'operator'=>$operator,
			'item'=>$item,
			'value'=>$value
		);
	}
	/**
	 * Добавить еще одну ноду.
	 * @param $condition - AND или OR
	 * @param $inBrackets - Использывать ли скобки
	 * @return \databaseWhereNode
	 */
	public function addNode($condition = "AND",$inBrackets=true){
		return $this->nodes[] = new databaseWhereNode($condition,$inBrackets);
	}
	/**
	 * Получить всех наследников
	 * @return array( \databaseWhereNode )
	 */
	public function getNodes() {
		return $this->nodes;
	}
	/**
	 * Получить получившийся SQL
	 * @return string
	 */
	public function getSQL() {
		if (count($this->items) < 1 && count($this->nodes) < 1) return '';
		$str = '';
		if ($this->inBrackets)
			$str = '(';
		$isFirst = true;
		foreach ($this->items as $item) {
			if ($isFirst)
				$isFirst = false;
			else
				$str .= ' '.$this->condition.' ';
			$str .= $item['item'].' '.$item['operator'].' '.$item['value'];
		}
		foreach ($this->nodes as $node) {
			if ($str != '(' && $str != '')
				$str .= ' '.$this->condition.' ';
			$str .= $node->getSQL();
		}
		if ($this->inBrackets)
			$str .= ')';
		return $str;
	}
	/**
	 * Функция автоопределения переменной
	 * @param $field - 
	 * @return \databaseWhereNode
	 */
	public static function getField($field) {
		if (is_string($field))
			$field = "'".str_replace("'","''",$field)."'";
		else if (is_array($field)) {
			$str = '(';
			foreach ($field as $inner) {
				if ($str != '(') $str .= ',';
				$str .= databaseWhereNode::getField($inner);
			}
			$field = $str.')';
			unset($str);
		} else
			$field = (string)$field;
		return $field;
	}
	public function __toString() {
		return $this->getSQL();
	}
}
class databaseExceptuion extends \Exception {
	private $lastQuery;
	public function __construct($errorMessage,$lastQuery= "") {
		$this->lastQuery = $lastQuery;
		parent::__construct($errorMessage);
	}
	public function getLastQuery() {
		return $this->lastQuery;
	}
}
