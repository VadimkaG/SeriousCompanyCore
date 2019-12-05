<?PHP
// version 1.0
class database {
	
	// Инициализация
	private $connect_data = array();
	private $connection = null;
	private $status = false;
	private $error = "";
	private $lastQuery = "";
	private $pNavigator;
	private $WhereNode;
	private $OrderBy;
	private $GroupBy;
	public function __construct($host, $user, $password, $database = "") {
		$this->connect_data['host'] = $host;
		$this->connect_data['user'] = $user;
		$this->connect_data['password'] = $password;
		$this->connect_data['database'] = $database;
		$this->clear();
	}
	// =================
	
	// Основные функции
	/**
	 * Подключиться к базе дыннх
	 */
	public function connect() {
		if ($this->connect_data['host'] == null || $this->connect_data['user'] == null) return false;
		if ($this->isConnect()) $this->disconnect();
		$this->connection = new MySQLI($this->connect_data['host'],$this->connect_data['user'],$this->connect_data['password'],$this->connect_data['database']);
		if ($this->connection->connect_errno) {
			$this->error = $this->connection->connect_error;
			$this->connection = null;
			$this->status = false;
		}
		else $this->status = true;
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
	 */
	public function row_query($query) {
		if (!$this->isConnect() && !$this->connect()) return false;
		return $this->connection->query($query);
	}
	/**
	 * Аодключена ли база
	 */
	public function isConnect() {
		return $this->status;
	}
	/**
	 * Вывести последнюю ошибку
	 * row_query() не сохраняется
	 */
	public function getError() {
		return $this->error;
	}
	/**
	 * Вывести последний запрос в базу
	 * row_query() не сохраняется
	 */
	public function getLastQuery() {
		return $this->lastQuery;
	}
	// =================
	
	/**
	 * Установить условие выборки
	 * @return Вовзращается объект databaseWhereNode
	 */
	public function setCondition($condition = 'AND') {
		if ($this->WhereNode)
			return $this->WhereNode;
		else
			return $this->WhereNode = new databaseWhereNode(strtoupper($condition));
	}
	/**
	 * Получить установленные условия
	 * в строке. Для SQL запроса
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
	 */
	public function query($query,$isReturnId = false) {
		$this->lastQuery = $query;
		if(!$this->isConnect() && !$this->connect()) return false;
		$result = $this->connection->query($query);
		if ($this->connection->error)
			$this->error = $this->connection->error;
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
		return $q;
	}
	/**
	 * Занесение данных в базу
	 * @param $table - таблица в базе
	 * @param $values - Значения, которые будут занесены в базу
	 * @poles $poles - Поля, в которые будут занесены данные
	 * (поля должны передаваться в том же порядке, что и значения)
	 */
	public function insert($table,$values,$poles = false) {
		if (is_array($table)) $table = implode(',',$table);
		else if (!is_string($table)) $table = (string)$table;
		if ($poles) $poles = ' ('.implode(',',$poles).')';
		if ($values) $values = '('.implode(',',$values).')';
		else return false;
		return $this->query("INSERT INTO ".$table.$poles." VALUES ".$values,true);
	}
	/**
	 * Обновить данные в базе
	 * Условия задаются через setCondition()
	 * @param $table - Таблица в которой будут обновлены данные
	 * @param $values - Данные, которые будут обновлены - array()
	 */
	public function update($table,$values) {
		if ($this->WhereNode) $where = ' WHERE '.$this->WhereNode->getSQL();
		else $where = '';
		if (is_array($table)) $table = implode(',',$table);
		else if (!is_string($table)) $table = (string)$table;
		$str_values = '';
		if (is_array($values)) {
			foreach($values as $key=>$value) {
				if ($str_values != '') $str_values .= ',';
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
		$this->condition = $condition;
		$this->items = array();
		$this->nodes = array();
		$this->inBrackets = $inBrackets;
	}
	/**
	 * Добавить условие
	 * @param $item - Имя поля
	 * @param $operator - Оператор
	 * @param $value - Значение (тип определяется автоматически: string, integer, array)
	 * @param $row - Установить в true, еесли необходимо $value передать в чистом виде
	 */
	public function add($item,$operator,$value,$row=false) {
		if ($operator == "!=") $operator = "<>";
		if (!$row) $value = $this->getField($value);
		$this->items[] = array(
			'operator'=>$operator,
			'item'=>$item,
			'value'=>$value
		);
	}
	/**
	 * Добавить еще одну ноду.
	 */
	public function addNode($condition,$inBrackets=true){
		return $this->nodes[] = new databaseWhereNode($condition,$inBrackets);
	}
	/**
	 * Получить все ноды
	 */
	public function getNodes() {
		return $this->nodes;
	}
	/**
	 * Получить получившийся SQL
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
	 */
	public static function getField($field) {
		if (is_string($field))
			$field = "'".$field."'";
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
}
