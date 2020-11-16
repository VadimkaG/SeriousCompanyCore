<?php
namespace modules\accounts;
if (!class_exists('\modules\accounts\index')) die("Ошибка: Используйте \modules\accounts\index::getAccountUtils()");
class AccountUtils {
	private $db;
	public function __construct(\database &$database){
		$this->db = $database;
	}
	/**
	 * Список аккаунтов
	 * @return int
	 */
	function countAccounts() {
		$result = $this->db->select($this->db->getTableAlias('accounts'),array('c'=>'Count(*)'));
		$this->db->clear();
		if (count($result) > 0)
			return (int)$result[0]['c'];
		else
			return 0;
	}
	/**
	 * Список аккаунтов
	 * @param $page - int - страница (необязательный параметр)
	 * @param $perPage - int - Количество аккаунтов на странице (необязательный параметр)
	 * @return array [ int ] = { array{ login, group, password, id } ... }
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function listAccounts(int $page = 1, int $perPage = 20) {

		$fields = array(
				"id"          => "a.id",
				"login"       => "a.login",
				"password"    => "a.password",
				"registered" => "a.registered",
				"lastlogin" => "a.lastlogin",
				"ugroup"      => "g.id",
				"ugroup_name" => "g.name"
			);

		$acc_fields = $this->listCustomFields();
		foreach ($acc_fields as $field) {
			$fields["field_".$field["alias"]] = "field_".$field["alias"];
		}

		$this->db->setPage($page,$perPage);
		$cond = $this->db->setCondition();
		$cond->add("a.ugroup","=","g.id",true);
		$accs = $this->db->select(
			array(
				"a" => $this->db->getTableAlias('accounts'),
				"g" => $this->db->getTableAlias('PermissionGroups')
			),
			$fields
		);
		$this->db->clear();
		foreach ($accs as &$acc) {
			$acc["id"] = (int)$acc["id"];
			$acc["ugroup"] = (int)$acc["ugroup"];
			$acc["group"] = &$acc["ugroup"];
			$acc["group_name"] = &$acc["ugroup_name"];
		}
		return $accs;
	}
	/**
	 * Получить аккаунт
	 * @param $id - int - Идентификатор пользователя
	 * @return array{ login, group, password, id } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountNotFoundException Если пользователь по заданным параметрам не найден
	 */
	function getAccount(int $id) {
		$cond = $this->db->setCondition('and');
		$cond->add('a.id','=',$id);
		$this->db->setPage(1,1);
		$cond->add("a.ugroup","=","g.id",true);
		$result = $this->db->select(
			array(
				"a" => $this->db->getTableAlias('accounts'),
				"g" => $this->db->getTableAlias('PermissionGroups')
			),
			array(
				"id"          => "a.id",
				"login"       => "a.login",
				"password"    => "a.password",
				"ugroup"      => "g.id",
				"ugroup_name" => "g.name"
			)
		);
		$this->db->clear();

		if (count($result) < 1) throw new AccountNotFoundException('Пользователь с id '.$id.' не найден.');
		$acc = array(
			'login'     => $result[0]['login'],
			'group'     => (int)$result[0]['ugroup'],
			'group_name'=> $result[0]['ugroup_name'],
			'password'  => $result[0]['password'],
			'id'        => (int)$result[0]['id']
		);
		unset($result);
		return $acc;
	}
	/**
	 * Получить аккаунт используя логин и пароль
	 * @param $login - string - Логин пользователя
	 * @param $password - string - Пароль пользователя (необязательный параметр)
	 * @return array{ login, group, password, id } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountNotFoundException Если пользователь по заданным параметрам не найден
	 */
	function getAccountByLogin(string $login, $password = null) {
		if ($password != null && !is_string($login)) throw new \InvalidArgumentException('$password must be string or null');
		$cond = $this->db->setCondition('and');
		
		$cond->add('a.login','=',$login);
		if ($password != null)
			$cond->add('a.password','=',index::cryptString($password));
		$this->db->setPage(1,1);
		$cond->add("a.ugroup","=","g.id",true);
		$result = $this->db->select(
			array(
				"a" => $this->db->getTableAlias('accounts'),
				"g" => $this->db->getTableAlias('PermissionGroups')
			),
			array(
				"id"          => "a.id",
				"login"       => "a.login",
				"password"    => "a.password",
				"ugroup"      => "g.id",
				"ugroup_name" => "g.name"
			)
		);
		$this->db->clear();
		
		if (count($result) < 1) throw new AccountNotFoundException('Неверный логин или пароль.');
		
		$acc = array(
			'login'     => $result[0]['login'],
			'group'     => (int)$result[0]['ugroup'],
			'group_name'=> $result[0]['ugroup_name'],
			'password'  => $result[0]['password'],
			'id'        => (int)$result[0]['id']
		);
		unset($result);
		return $acc;
	}
	/**
	 * Существует ли аккаунт
	 * @param $id - int - Идентификатор пользователя
	 * @return boolean
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function existsAccount(int $id) {
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->db->setPage(1,1);
		$result = $this->db->select(
			$this->db->getTableAlias('accounts'),
			array(
				"cout" => "Count(*)"
			)
		);
		$this->db->clear();
		
		if (isset($result[0]["cout"]) && ((int)$result[0]["cout"]) > 0) return true;
		return false;
	}
	/**
	 * Существует ли аккаунт
	 * @param $login - string - Логин пользователя
	 * @return boolean
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function existsAccountByLogin(string $login) {
		$cond = $this->db->setCondition('and');
		$cond->add('login','=',$login);
		$this->db->setPage(1,1);
		$result = $this->db->select(
			$this->db->getTableAlias('accounts'),
			array(
				"cout" => "Count(*)"
			)
		);
		$this->db->clear();
		
		if (isset($result[0]["cout"]) && ((int)$result[0]["cout"]) > 0) return true;
		return false;
	}
	/**
	 * Добавить аккаунт
	 * @param $login - string - Логин
	 * @param $password - string - Пароль
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function addAccount(string $login,string $password,int $group = 2,$fields = array()) {
		if ($group < 1) throw new \InvalidArgumentException('$group must be > 0');
		if (!is_array($fields)) throw new \InvalidArgumentException('$fields must be array');
		if ($this->existsAccountByLogin($login)) throw new AccountExistsException('Пользователь с данным именем уже существует.');
		foreach ($fields as &$field) {
			$field = \databaseWhereNode::getField($field);
		}
		$fields["login"]      = '"'.$login.'"';
		$fields["password"]   = '"'.index::cryptString($password).'"';
		$fields["ugroup"]     = $group;
		$fields["registered"] = "NOW()";
		$result = $this->db->insert(
			$this->db->getTableAlias('accounts'),
			$fields,
			true
		);
		$this->db->clear();
	}
	/**
	 * Удалить аккаунт
	 * @param $id - int - Идентификатор аккаунта
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function delAccount($id) {
		if (!is_int($id) && !is_array($id)) throw new \InvalidArgumentException('$id must be int or array( int )');
		
		if (is_int($id))
			$id = array( $id );

		$cond = $this->db->setCondition();
		$cond->add('id','IN',$id);
		$query = $this->db->select($this->db->getTableAlias('accounts'), array("ugroup"));
		$this->db->clear();

		$admins_count = 0;
		foreach ($query as $item) {
			if ((int)$item["ugroup"] == 1)
				$admins_count ++;
		}

		if ($admins_count > 0) {
			$cond = $this->db->setCondition();
			$cond->add('ugroup','=',1);
			$this->db->setPage(1,$admins_count+1);
			$query = $this->db->select($this->db->getTableAlias('accounts'), array('c'=>"Count(*)"));
			$this->db->clear();
			if (count($query) > 0 && $query[0]['c'] <= $admins_count)
				throw new NotEnoughAdminsException('Если выполнить эту операцию, то не останется ни одного администратора.');
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','IN',$id);
		$this->db->delete($this->db->getTableAlias('accounts'));
		$this->db->clear();

		$cond = $this->db->setCondition('and');
		$cond->add('parent','IN',$id);
		$cond->add('type','=',1);
		$this->db->delete($this->db->getTableAlias('Permissions'));
		$this->db->clear();
	}
	/**
	 * Изменить данные аккаунта
	 * @param $id - int - Идентификатор аккаунта
	 * @param $newlogin - string - Новый логин аккаунта
	 * @param $password - string - Новый пароль аккаунта
	 * @param $group - int - Новая группа аккаунта
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function setAccount(int $id, string $newlogin = null,string $password = null,int $group = null,$fields = array()) {
		if (!is_array($fields)) throw new \InvalidArgumentException('$fields must be array');
		
		$acc = $this->getAccount($id);
		
		// Если это администратор, то проверяем есть ли еще администраторы
		if ($acc['group'] == 1 && $group != null) {
			$cond = $this->db->setCondition('and');
			$cond->add('ugroup','=',1);
			$this->db->setPage(1,2);
			$result = $this->db->select($this->db->getTableAlias('accounts'), array('c'=>"Count(*)"));
			$this->db->clear();
			
			if (count($result) > 0 && $result[0]['c'] <= 1 && $group != 1)
				throw new NotEnoughAdminsException('Администратор с id '.$id.' единственный администратор. Изменение заблокировано.');
		}
		
		// Проверяем существование группы группа
		if ($group != null) {
			$cond = $this->db->setCondition('and');
			$cond->add('id','=',$group);
			$this->db->setPage(1,1);
			$result = $this->db->select($this->db->getTableAlias('PermissionGroups'));
			$this->db->clear();
			if (count($result) < 1)
				throw new GroupNotFoundException('Группа с id '.$group.' не найдена');
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		//$fields = array();
		if ($newlogin != null)
			$fields['login'] = $newlogin;
		if ($password != null)
			$fields['password'] = index::cryptString($password);
		if ($group != null)
			$fields['ugroup'] = $group;
		if (count($fields) > 0)
			$this->db->update($this->db->getTableAlias('accounts'),$fields);
		$this->db->clear();
	}
	/**
	 * Список кастомных полей
	 * @oaram $perPage Количество элементов
	 */
	function listCustomFields(int $perPage = 20) {
		$this->db->setPage(1,$perPage);
		$result = $this->db->select($this->db->getTableAlias('AccountFields'));
		$this->db->clear();
		foreach ($result as &$row) {
			$row["id"] = (int)$row["id"];
			$row["length"] = (int)$row["length"];
		}
		return $result;
	}
	/**
	 * Добавить дополнительное поле
	 */
	function addCustomField(string $alias, string $name, string $type, int $length = 0) {
		if ($length < 0) throw new \InvalidArgumentException('$length must be >= 0');

		switch (strtolower($type)) {
		
		case "tinyint":
			$type = "tinyint";
			if ($length > 3) $length = 3;
			break;

		case "int":
			$type = "int";
			if ($length > 10) $length = 10;
			break;

		case "bigint":
			$type = "bigint";
			if ($length > 19) $length = 19;
			break;

		case "varchar":
			$type = "varchar";
			if ($length > 255) $length = 255;
			break;

		case "decimal":
			$type = "decimal";
			if ($length > 65) $length = 65;
			break;

		case "float":
			$type = "float";
			break;

		case "real":
		case "double":
			$type = "double";
			break;

		case "datetime":
		case "time":
		case "timestamp":
		case "date":
			$type = strtolower($type);
			$length = 0;
			break;

		default:
			$type = "text";
			$length = 0;
		}

		if ($length > 0)
			$length_str = "(".$length.")";
		else
			$length_str = "";
		
		$this->db->query("alter table ".$this->db->getTableAlias('accounts')." add ( field_".$alias." ".$type.$length_str." )");
		$this->db->insert(
			$this->db->getTableAlias('AccountFields'),
			array(
				"alias" => $alias,
				"name" => $name,
				"type" => $type,
				"length" => $length
			)
		);
	}
	/**
	 * Удалить дополнительное поле
	 */
	function delCustomField($id) {
		if (!is_int($id) && !is_array($id)) throw new \InvalidArgumentException('$id must be int or array( int )');

		if (is_int($id))
			$id = array( $id );

		$cond = $this->db->setCondition();
		$cond->add("id","IN",$id);
		$query = $this->db->select($this->db->getTableAlias('AccountFields'));
		if (count($query) < 1) {
			$this->db->clear();
			throw new \Exception("Поля с таким id не найдены.");
		}
		$this->db->delete($this->db->getTableAlias('AccountFields'));
		$this->db->clear();
		$this->db->query("alter table ".$this->db->getTableAlias('accounts')." drop column field_".$query[0]["alias"]);
	}
	/**
	 * Список привилегий аккаунта
	 * @param $userid - int - Идентификатор аккаунта
	 * @return array [ int ] { array { id, name } ... }
	 */
	function listPermission(int $userid, int $page = 1, int $perPage = 20) {
		$this->db->setPage($page,$perPage);
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$userid);
		$cond->add('type','=',1);
		$result = $this->db->select($this->db->getTableAlias('Permissions'));
		$this->db->clear();
		foreach ($result as &$perm) {
			$perm['id'] = (int)$perm['id'];
			unset($perm['type']);
			unset($perm['parent']);
		}
		return $result;
	}
	/**
	 * Проверить привилегию у игрока
	 * @param $userid - int - Идентификатор аккаунта
	 * @param $perm - string - привилегия аккаунта
	 */
	function isSetPermission(int $userid, string $perm) {
		$acc = $this->getAccount($userid);
		
		// Администраторам разрешено все
		if (((int)$acc['group']) == 1) return true;
		
		// Проверяем привилегию у пользователя
		$cond = $this->db->setCondition("and");
		
		$types = $cond->addNode("or");
		
		$type_group = $types->addNode();
		$type_group->add("type",'=',0);
		$type_group->add("parent",'=',$acc['group']);
		
		$type_user = $types->addNode();
		$type_user->add("type",'=',1);
		$type_user->add("parent",'=',$acc['id']);
		
		$cond->add("perm",'=',$perm);

		$this->db->setPage(1,1);
		
		$result = $this->db->select($this->db->getTableAlias('Permissions'));
		$this->db->clear();
		
		if (count($result) > 0) return true;
		return false;
	}
	/**
	 * Добавить привилегию игроку
	 * @param $userid - int - Идентификатор аккаунта
	 * @param $perm - string - привилегия аккаунта
	 */
	function addPermission(int $userid, string $perm) {
		if (!$this->existsAccount($userid)) throw new AccountNotFoundException('Пользователь с id '.$userid.' не найден');
		if ($this->isSetPermission($userid,$perm)) throw new PermissionExistsException('Привилегия '.$perm.' уже установлена у аккаунта с id '.$userid);
		$this->db->insert(
			$this->db->getTableAlias('Permissions'),
			array(
				"parent" => $userid,
				"type"   => 1,
				"perm"   => $perm
			)
		);
		$this->db->clear();
	}
	/**
	 * Удолить привилегию у игрока
	 * @param $id - int - Идентификатор привилегии
	 */
	function delPermission($id) {
		if (!is_int($id) && !is_array($id)) throw new \InvalidArgumentException('$id must be int or array( int )');
		if (is_int($id))
			$id = array( $id );
		$cond = $this->db->setCondition('and');
		$cond->add('id','IN',$id);
		$result = $this->db->delete($this->db->getTableAlias('Permissions'));
		$this->db->clear();
	}
}
