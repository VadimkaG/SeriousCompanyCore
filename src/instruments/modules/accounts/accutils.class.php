<?PHP
namespace modules\accounts;
if (!class_exists('\modules\accounts\index')) die("Ошибка: Используйте \modules\accounts\index::getAccountUtils()");
class AccountUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	function countAccounts() {
		$result = $this->db->select($this->tablenames['accounts'],array('c'=>'Count(*)'));
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			$result->close();
			$this->db->clear();
			if ($arr)
				return (int)$arr['c'];
			else
			    return 0;
		} else throw new AccountQueryFailedException($this->db->getError());
	}
	/**
	 * Список аккаунтов
	 * @param $page - int - страница (необязательный параметр)
	 * @param $perPage - int - Количество аккаунтов на странице (необязательный параметр)
	 * @return array [ int ] = { array{ login, group, email, password, id } ... }
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	function listAccounts($page = null,$perPage = null) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		$result = $this->db->select($this->tablenames['accounts']);
		if ($result != false) {
			$accs = array();
			while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
				//$accs[] = new Account($arr['login'],$arr['ugroup'],$arr['email'],$arr['password'],$arr['id']);
				$accs[] = array(
					'login'=>$arr['login'],
					'group'=>(int)$arr['ugroup'],
					'email'=>$arr['email'],
					'password'=>$arr['password'],
					'id'=>(int)$arr['id']
				);
			}
			$result->close();
			$this->db->clear();
			return $accs;
		} else throw new AccountQueryFailedException($this->db->getError());
	}
	/**
	 * Получить аккаунт
	 * @param $id - int - Идентификатор пользователя
	 * @return array{ login, group, email, password, id } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \modules\accounts\AccountNotFoundException Если пользователь по заданным параметрам не найден
	 */
	function getAccount($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->select($this->tablenames['accounts']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) throw new AccountNotFoundException('Пользователь с id '.$id.' не найден.');
			//$acc = new Account($arr['login'],$arr['ugroup'],$arr['email'],$arr['password'],$arr['id']);
			$acc = array(
				'login'=>$arr['login'],
				'group'=>$arr['ugroup'],
				'email'=>$arr['email'],
				'password'=>$arr['password'],
				'id'=>(int)$arr['id']
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
	 * Получить аккаунт используя логин и пароль
	 * @param $login - string - Логин пользователя
	 * @param $password - string - Пароль пользователя (необязательный параметр)
	 * @return array{ login, group, email, password, id } 
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 * @throw \modules\accounts\AccountNotFoundException Если пользователь по заданным параметрам не найден
	 */
	function getAccountByLogin($login,$password = null) {
		if (!is_string($login)) throw new \InvalidArgumentException('$login must be string');
		if ($password!= null && !is_string($login)) throw new \InvalidArgumentException('$password must be string or null');
		$cond = $this->db->setCondition('and');
		
		$cond->add('login','=',$login);
		
		if ($password != null) {
			$cond->add('password','=',index::cryptString($password));
		}
		
		$result = $this->db->select($this->tablenames['accounts']);
		
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$this->db->clear();
				throw new AccountNotFoundException('Неверный логин или пароль.');
			}
			//$acc = new Account($arr['login'],$arr['ugroup'],$arr['email'],$arr['password'],$arr['id']);
			$acc = array(
				'login'=>$arr['login'],
				'group'=>$arr['ugroup'],
				'email'=>$arr['email'],
				'password'=>$arr['password'],
				'id'=>(int)$arr['id']
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
	 * Существует ли аккаунт
	 * @param $id - int - Идентификатор пользователя
	 * @return boolean
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе происхзошла ошибка
	 */
	function existsAccount($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->select($this->tablenames['accounts']);
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
	 * Существует ли аккаунт
	 * @param $login - string - Логин пользователя
	 * @return boolean
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе происхзошла ошибка
	 */
	function existsAccountByLogin($login) {
		if (!is_string($login)) throw new \InvalidArgumentException('$login must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('login','=',$login);
		$result = $this->db->select($this->tablenames['accounts']);
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
	 * Добавить аккаунт
	 * @param $login - string - Логин
	 * @param $password - string - Пароль
	 * @param $email - string - Электронная почта
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	function addAccount($login,$password,$email = '') {
		//if (!($acc instanceof Account)) throw new \InvalidArgumentException('$acc must be \\modules\\accounts\\Account');
		if (!is_string($login)) throw new \InvalidArgumentException('$login must be string');
		if (!is_string($password)) throw new \InvalidArgumentException('$password must be string');
		if (!is_string($email)) throw new \InvalidArgumentException('$email must be string');
		if ($this->existsAccountByLogin($login)) throw new AccountExistsException('Пользователь с данным именем уже существует.');
		$result = $this->db->insert($this->tablenames['accounts'],
			array(
				"'".$login."'",
				"'".index::cryptString($password)."'",
				"'".$email."'",
				2
			),
			array(
				'login',
				'password',
				'email',
				'ugroup'
			)
		);
		$this->db->clear();
		if ($result != false)
			throw new AccountQueryFailedException($this->db->getError());
	}
	/**
	 * Удалить аккаунт
	 * @param $id - int - Идентификатор аккаунта
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 * @throw \modules\accounts\AccountQueryFailedException Если при SQL запросе произошла ошибка
	 */
	function delAccount($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		
		$acc = $this->getAccount($id);
		
		if ($acc['group'] == 1) {
		    $cond = $this->db->setCondition('and');
			$cond->add('ugroup','=',1);
			$result = $this->db->select($this->tablenames['accounts'], array('c'=>"Count(*)"));
			if ($result != false) {
				$arr = $result->fetch_array(MYSQLI_ASSOC);
				if ($arr != null) {
					$result->close();
					$this->db->clear();
					if ($arr['c'] <= 1) {
						throw new NotEnoughAdminsException('Администратор с id '.$id.' единственный администратор. Удаление заблокировано.');
					}
				} else {
					$result->close();
					$this->db->clear();
				}
			} else {
				$this->db->clear();
				throw new AccountQueryFailedException($this->db->getError());
			}
			$this->db->clear();
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['accounts']);
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$id);
		$cond->add('type','=',1);
		$result = $this->db->delete($this->tablenames['Permissions']);
		$this->db->clear();
		if ($result != false)
			throw new AccountQueryFailedException($this->db->getError());
	}
	/**
	 * Изменить данные аккаунта
	 * @param $id - int - Идентификатор аккаунта
	 * @param $newlogin - string - Новый логин аккаунта
	 * @param $password - string - Новый пароль аккаунта
	 * @param $email - string - Новый email аккаунта
	 * @param $group - int - Новая группа аккаунта
	 * @throw \InvalidArgumentException Если параметры не верного типа
	 */
	function setAccount($id, $newlogin = null,$password = null,$email = null,$group = null) {
		if ($newlogin != null && !is_string($newlogin)) throw new \InvalidArgumentException('$newlogin must be string');
		if ($password != null && !is_string($password)) throw new \InvalidArgumentException('$password must be string');
		if ($email != null && !is_string($email)) throw new \InvalidArgumentException('$email must be string');
		if ($group != null && !is_int($group)) throw new \InvalidArgumentException('$group must be int');
		
		$acc = $this->getAccount($id);
		
		if ($acc['group'] == 1 && $group != null) {
			$cond = $this->db->setCondition('and');
			$cond->add('ugroup','=',1);
			$result = $this->db->select($this->tablenames['accounts'], array('c'=>"Count(*)"));
			if ($result != false) {
				$arr = $result->fetch_array(MYSQLI_ASSOC);
				if ($arr != null) {
					$result->close();
					$this->db->clear();
					if ($arr['c'] <= 1 && $group != 1) {
						throw new NotEnoughAdminsException('Администратор с id '.$id.' единственный администратор. Изменение группы заблокировано.');
					}
				} else {
					$result->close();
					$this->db->clear();
				}
			} else {
				$this->db->clear();
				throw new AccountQueryFailedException($this->db->getError());
			}
			$this->db->clear();
		}
		
		if ($group != null) {
		    $cond = $this->db->setCondition('and');
		    $cond->add('id','=',$group);
		    $result = $this->db->select($this->tablenames['PermissionGroups']);
		    if ($result != false) {
			    $arr = $result->fetch_array(MYSQLI_ASSOC);
			    if ($arr == null) {
				    $result->close();
				    $this->db->clear();
				    throw new GroupNotFoundException('Группа с id '.$group.' не найдена');
			    }
			    $result->close();
			    $this->db->clear();
		    } else {
			    $this->db->clear();
			    throw new AccountQueryFailedException($this->db->getError());
		    }
		    $this->db->clear();
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($newlogin != null)
		    $tables['login'] = $newlogin;
		if ($password != null)
		    $tables['password'] = index::cryptString($password);
		if ($group != null)
		    $tables['ugroup'] = $group;
		if ($email != null)
		    $tables['email'] = $email;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['accounts'],$tables);
		$this->db->clear();
	}
	/**
	 * Список привилегий аккаунта
	 * @param $userid - int - Идентификатор аккаунта
	 * @return array [ int ] { array { id, name } ... }
	 */
	function listPermission($userid) {
		if (!is_int($userid)) throw new \InvalidArgumentException('$userid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$userid);
		$cond->add('type','=',1);
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
	 * Проверить привилегию у игрока
	 * @param $userid - int - Идентификатор аккаунта
	 * @param $perm - string - привилегия аккаунта
	 */
	function isSetPermission($userid,$perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!is_int($userid)) throw new \InvalidArgumentException('$userid must be int');
		$acc = $this->getAccount($userid);
		if (((int)$acc['group']) == 1) return true;
		$cond = $this->db->setCondition('and');
		$cond->add("parent",'=',$acc['group']);
		$cond->add("perm",'=',$perm);
		$cond->add("type",'=',0);
		$result = $this->db->select($this->tablenames['Permissions']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr != null) return true;
			$result->close();
		} else {
			$this->db->clear();
			throw new AccountQueryFailedException($this->db->getError());
		}
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add("parent",'=',$acc['id']);
		$cond->add("perm",'=',$perm);
		$cond->add("type",'=',1);
		$result = $this->db->select($this->tablenames['Permissions']);
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
	 * Добавить привилегию игроку
	 * @param $userid - int - Идентификатор аккаунта
	 * @param $perm - string - привилегия аккаунта
	 */
	function addPermission($userid,$perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!$this->existsAccount($userid)) throw new AccountNotFoundException('Пользователь с id '.$userid.' не найден');
		if ($this->isSetPermission($userid,$perm)) throw new PermissionExistsException('Привилегия '.$perm.' уже установлена у аккаунта с id '.$userid);
		$result = $this->db->insert($this->tablenames['Permissions'],
			array(
				$userid,
				1,
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
	 * Удолить привилегию у игрока
	 * @param $userid - int - Идентификатор аккаунта
	 * @param $perm - string - привилегия аккаунта
	 */
	function delPermission($userid, $perm) {
		if (!is_string($perm)) throw new \InvalidArgumentException('$perm must be string');
		if (!is_int($userid)) throw new \InvalidArgumentException('$userid must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$userid);
		$cond->add('type','=',1);
		$cond->add('perm','=',$perm);
		$result = $this->db->delete($this->tablenames['Permissions']);
		$this->db->clear();
		if ($result != false) throw new AccountQueryFailedException($this->db->getError());
	}
}
