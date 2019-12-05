<?PHP
namespace modules\accounts;
if (!class_exists('\modules\accounts\index')) die("Ошибка: Используйте \modules\accounts\index::getGroupUtils()");
class GroupUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	/**
	 * Список групп
	 * @param $page - int - страница (необязательный параметр)
	 * @param $perPage - int - Количество аккаунтов на странице (необязательный параметр)
	 * @return array [ int ] = { array{ id, name } ... }
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	public function listGroups($page = null,$perPage = null) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		$result = $this->db->select($this->tablenames['PermissionGroups']);
		if ($result != false) {
			$accs = array();
			while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
				$accs[] = array(
					'id'=>(int)$arr['id'],
					'name'=>$arr['name']
				);
			}
			$result->close();
			$this->db->clear();
			return $accs;
		} else throw new AccountQueryFailedException($this->db->getError());
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
		$result = $this->db->select($this->tablenames['PermissionGroups']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$result->close();
				$this->db->clear();
				return false;
			}
			$result->close();
			$this->db->clear();
			return true;
		} else {
			$this->db->clear();
			throw new AccountQueryFailedException($this->db->getError());
		}
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
		$result = $this->db->select($this->tablenames['PermissionGroups']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) throw new GroupNotFoundException('Группа с id '.$id.' не найдена.');
			$acc = array(
					'id'=>(int)$arr['id'],
					'name'=>$arr['name']
			);
			unset($arr);
			$result->close();
			$this->db->clear();
			return $acc;
		} else {
			$this->db->clear();
			throw new AccountQueryFailedException($this->db->getError());
		}
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
		$result = $this->db->select($this->tablenames['accounts'],array('ugroup'));
		$usergroup = null;
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$result->close();
				$this->db->clear();
				throw new AccountNotFoundException('Пользователь с данным именем не найден.');
			}
			$usergroup = (int)$arr['ugroup'];
			$result->close();
		} else {
			$this->db->clear();
			throw new AccountQueryFailedException($this->db->getError());
		}
		$this->db->clear();
		unset($result);
		return $this->getGroup($usergroup);
	}
	/**
	 * Добавить группу
	 * @param $name - string - Имя группы
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	public function addGroup($name) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		$result = $this->db->insert($this->tablenames['PermissionGroups'],
			array(
				"'".$name."'",
			),
			array(
				'name'
			)
		);
		$this->db->clear();
		if ($result != false) throw new AccountQueryFailedException($this->db->getError());
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
		$this->db->update($this->tablenames['accounts'],array(
			'ugroup'=>2
		));
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['PermissionGroups']);
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$id);
		$cond->add('type','=',0);
		$result = $this->db->delete($this->tablenames['Permissions']);
		$this->db->clear();
		if ($result != false)
			throw new AccountQueryFailedException($this->db->getError());
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
		$this->db->update($this->tablenames['PermissionGroups'],array(
			'name'=>$name
		));
		$this->db->clear();
	}
	/**
	 * Список привилегий аккаунта
	 * @param $groupid - int - Идентификатор аккаунта
	 * @return array [ int ] { array { id, name } ... }
	 */
	function listPermission($groupid) {
		if (!is_int($groupid)) throw new \InvalidArgumentException('$groupid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$groupid);
		$cond->add('type','=',0);
		$result = $this->db->select($this->tablenames['Permissions']);
		if ($result != false) {
			$perms = array();
			while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
				$perms[] = $arr['perm'];
				/*$perms[] = array(
					'parent'=>(int)$arr['parent'],
					'perm'=>$arr['perm'],
					'type'=>(int)$arr['type']
				);*/
			}
			$result->close();
			$this->db->clear();
			return $perms;
			} else {
				$this->db->clear();
				throw new AccountQueryFailedException($this->db->getError());
			}
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
		$result = $this->db->select($this->tablenames['Permissions']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$result->close();
				$this->db->clear();
				return false;
			}require_once(__DIR__.'/grutils.class.php');
			$result->close();
			$this->db->clear();
			return true;
		} else {
			$this->db->clear();
			throw new AccountQueryFailedException($this->db->getError());
		}
	}
	/**
	 * Добавить привилегию группе
	 * @param $groupid - int - Идентификатор
	 * @param $perm - string - привилегия
	 */
	function addPermission($groupid,$perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!$this->existsGroup($groupid)) throw new GroupNotFoundException('Группа с id '.$groupid.' не найдена');
		if ($this->isSetPermission($groupid,$perm)) throw new PermissionExistsException('Привилегия '.$perm.' уже установлена у группы с id '.$groupid);
		$result = $this->db->insert($this->tablenames['Permissions'],
			array(
				$groupid,
				0,
				"'".$perm."'"
			),
			array(
				'parent',
				'type',
				'perm'
			)
		);
		$this->db->clear();
		if ($result != false) throw new AccountQueryFailedException($this->db->getError());
	}
	/**
	 * Удалить привилегию у группы
	 * @param $groupid - int - Идентификатор
	 * @param $perm - string - привилегия
	 */
	function delPermission($groupid, $perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!is_int($groupid)) throw new \InvalidArgumentException('$groupid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$groupid);
		$cond->add('type','=',0);
		$cond->add('perm','=',$perm);
		$result = $this->db->delete($this->tablenames['Permissions']);
		$this->db->clear();
		if ($result != false) throw new AccountQueryFailedException($this->db->getError());
	}
}
