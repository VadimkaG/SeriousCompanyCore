<?PHP
namespace modules\accounts;
if (!class_exists('\modules\accounts\index')) die("Ошибка: Используйте \modules\accounts\index::getGroupUtils()");
class GroupUtils {
	private $db;
	public function __construct(&$database){
		$this->db = $database;
	}
	/**
	 * Количество всех групп
	 */
	public function countGroups() {
		$result = $this->db->select($this->db->getTableAlias('PermissionGroups'),array('c'=>'Count(*)'));
		$this->db->clear();
		if (count($result) > 0)
			return (int)$result[0]['c'];
		else
			return 0;
	}
	/**
	 * Список групп
	 * @param $page - int - страница (необязательный параметр)
	 * @param $perPage - int - Количество аккаунтов на странице (необязательный параметр)
	 * @return array [ int ] = { array{ id, name } ... }
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	public function listGroups($page = 1,$perPage = 20) {
		if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
		if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
		$this->db->setPage($page,$perPage);
		$result = $this->db->select($this->db->getTableAlias('PermissionGroups'));
		$this->db->clear();
		foreach ($result as &$row) {
			$row["id"] = (int)$row["id"];
		}
		return $result;
	}
	/**
	 * Существует ли гроуппа
	 * @param $id - int - Идентификатор группы
	 * @return boolean
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе происхзошла ошибка
	 */
	public function existsGroup($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->db->setPage(1,1);
		$result = $this->db->select(
			$this->db->getTableAlias('PermissionGroups'),
			array(
				"cout" => "Count(*)"
			)
		);
		$this->db->clear();
		if (isset($result[0]["cout"]) && ((int)$result[0]["cout"]) > 0) return true;
		return false;
	}
	/**
	 * Получить Группу
	 * @param $id - int - Идентификатор группы
	 * @return array{ id, name } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \modules\accounts\GroupNotFoundException Если группа по заданным параметрам не найдена
	 */
	public function getGroup($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->setPage(1,1);
		$result = $this->db->select($this->db->getTableAlias('PermissionGroups'));
		$this->db->clear();

		if (!isset($result[0]["id"]))
			throw new GroupNotFoundException('Группа с id '.$id.' не найдена.');
		$result[0]["id"] = (int)$result[0]["id"];
		return $result[0];
	}
	/**
	 * Получить группу пользователя
	 * @param $userid - int - Идентификатор пользователя
	 * @return array{ id, name } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \modules\accounts\AccountNotFoundException Если пользователь по заданным параметрам не найдена
	 */
	public function getGroupByUserId($userid) {
		if (!is_int($userid)) throw new \InvalidArgumentException('$userid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$userid);
		$this->setPage(1,1);
		$result = $this->db->select($this->db->getTableAlias('accounts'),array('ugroup'));
		$this->db->clear();

		if (!isset($result[0]))
			throw new AccountNotFoundException('Пользователь с данным именем не найден.');
		return $this->getGroup((int)$result[0]["ugroup"]);
	}
	/**
	 * Получить список групп, начинающихся на $name
	 * @param $name - string
	 * @return array()
	 */
	public function autocompleteGroupName($name, $count = 10) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_int($count)) throw new \InvalidArgumentException('$count must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('name','like',$name."%");
		$this->db->setPage(1,$count);
		$result = $this->db->select($this->db->getTableAlias('PermissionGroups'));
		$this->db->clear();

		foreach ($result as &$row) {
			$row["id"] = (int)$row["id"];
		}
		return $result;
	}
	/**
	 * Добавить группу
	 * @param $name - string - Имя группы
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	public function addGroup($name) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		$result = $this->db->insert(
			$this->db->getTableAlias('PermissionGroups'),
			array(
				"name" => $name
			)
		);
		$this->db->clear();
		return $result;
	}
	/**
	 * Удалить группу
	 * @param $id - int - Идентификатор группы
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	public function delGroup($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if ($id == 1 || $id == 2) throw new \InvalidArgumentException('Группу '.$id.' нельзя удалять.');
		$cond = $this->db->setCondition('and');
		$cond->add('ugroup','=',$id);
		$this->db->update(
			$this->db->getTableAlias('accounts'),
			array(
				'ugroup' => 2
			)
		);
		$this->db->clear();

		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$id);
		$cond->add('type','=',0);
		$this->db->delete($this->db->getTableAlias('Permissions'));
		$this->db->clear();

		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->db->delete($this->db->getTableAlias('PermissionGroups'));
		$this->db->clear();
	}
	/**
	 * Изменить данные группы
	 * @param $id - int - Идентификатор аккаунта
	 * @param $name - string - Новое имя группы
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	public function setGroup($id,$name) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!$this->existsGroup($id)) throw new AccountNotFoundException('Группа с id '.$id.' не найдена');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->db->update(
			$this->db->getTableAlias('PermissionGroups'),
			array(
				'name' => $name
			)
		);
		$this->db->clear();
	}
	/**
	 * Список привилегий аккаунта
	 * @param $groupid - int - Идентификатор аккаунта
	 * @return array [ int ] { array { id, name } ... }
	 */
	function listPermission($groupid,$page = 1, $perPage = 20) {
		if (!is_int($groupid)) throw new \InvalidArgumentException('$groupid must be int');
		if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
		if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
		$this->db->setPage($page,$perPage);
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$groupid);
		$cond->add('type','=',0);
		$result = $this->db->select($this->db->getTableAlias('Permissions'));
		$this->db->clear();
		foreach ($result as &$perm) {
			$perm["id"] = (int)$perm["id"];
			unset($perm["type"]);
			unset($perm["parent"]);
		}
		return $result;
	}
	/**
	 * Проверить привилегию у группы
	 * @param $groupid - int - Идентификатор
	 * @param $perm - string - привилегия
	 */
	function isSetPermission($groupid,$perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!is_int($groupid)) throw new \InvalidArgumentException('$groupid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add("parent",'=',$groupid);
		$cond->add("perm",'=',$perm);
		$cond->add("type",'=',0);
		$this->db->setPage(1,1);
		$result = $this->db->select($this->db->getTableAlias('Permissions'));
		$this->db->clear();

		if (count($result) > 0) return true;
		return false;
	}
	/**
	 * Добавить привилегию группе
	 * @param $groupid - int - Идентификатор
	 * @param $perm - string - привилегия
	 */
	function addPermission($groupid,$perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!$this->existsGroup($groupid)) throw new GroupNotFoundException('Группа с id '.$groupid.' не найдена');
		if ($groupid == 1) throw new \InvalidArgumentException('Нельзя добавлять привилегии в системную группу, у которой полные ривилегии');
		if ($this->isSetPermission($groupid,$perm)) throw new PermissionExistsException('Привилегия '.$perm.' уже установлена у группы с id '.$groupid);
		$result = $this->db->insert(
			$this->db->getTableAlias('Permissions'),
			array(
				"parent" => (int)$groupid,
				"type"   => 0,
				"perm"   => $perm
			)
		);
		$this->db->clear();
		return $result;
	}
	/**
	 * Удалить привилегию у группы
	 * @param $id - int - Идентификатор привилегии
	 */
	function delPermission($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$this->db->delete($this->db->getTableAlias('Permissions'));
		$this->db->clear();
	}
}
