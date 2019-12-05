<?PHP
namespace modules\shops;
if (!class_exists('\modules\shops\index')) die("Ошибка: Используйте \modules\shops\index::getShopUtils()");
class CommentUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	public function count($parent = null) {
		if ($parent != null && !is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if ($parent != null) {
			$cond = $this->db->setCondition('and');
			$cond->add('parent','=',$parent);
		}
		$result = $this->db->select(
				$this->tablenames['Shops.Comments'],
				array(
					"c"=>'Count(*)'
				)
			);
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		$result->close();
		$this->db->clear();
		return (int)$arr['c'];
	}
	public function listCom($page = null,$perPage = null,$parent = null) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		if ($parent != null) {
			if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
			$cond = $this->db->setCondition('and');
			$cond->add('parent','=',$parent);
		}
		$this->db->sort('time_created',false);
		$result = $this->db->select($this->tablenames['Shops.Comments']);
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$comments = array();
		while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
			$arr['id'] = (int)$arr['id'];
			$arr['parent'] = (int)$arr['parent'];
			$arr['user'] = (int)$arr['user'];
			$arr['rating'] = (int)$arr['rating'];
			$comments[] = $arr;
		}
		$result->close();
		$this->db->clear();
		return $comments;
	}
	public function add($parent,$user,$rating,$comment) {
		if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		if (!is_int($rating)) throw new \InvalidArgumentException('$rating must be int');
		if ($rating < 0 || $rating > 5) throw new \InvalidArgumentException('0 >= $rating <= 5');
		if (!is_string($comment)) throw new \InvalidArgumentException('$comment must be string');
		
		$accmodule = getModule('accounts');
		if ($accmodule != null) {
			$accu = $accmodule->getAccountUtils();
			if (!$accu->existsAccount($user)) throw new ShopNotExistsException('Аккаунт '.$user.' не существует');
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$parent);
		$result = $this->db->select($this->tablenames['Shops.Header']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$result->close();
				$this->db->clear();
				throw new ShopNotExistsException('Запись '.$parent.' не существует');
			}
			$result->close();
		} else throw new ShopQueryFailedException($this->db->getError());
		$this->db->clear();
		
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$parent);
		$cond->add('user','=',$user);
		$result = $this->db->select($this->tablenames['Shops.Comments']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr != null) {
				$result->close();
				$this->db->clear();
				throw new ShopNotExistsException('Ваша оценка уже присутствует');
			}
			$result->close();
		} else throw new ShopQueryFailedException($this->db->getError());
		
		$this->db->clear();
		if ($this->db->getError() != '') throw new ShopQueryFailedException($this->db->getError());
		$result = $this->db->insert($this->tablenames['Shops.Comments'],
			array(
				$parent,
				$user,
				"'".$comment."'",
				$rating,
				'NOW()'
			),
			array(
			    'parent',
			    'user',
			    'comment',
			    'rating',
			    'time_created'
			)
		);
		$this->db->clear();
		if ($this->db->getError() != '')
			throw new ShopQueryFailedException($this->db->getError());
	}
	public function get($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->select($this->tablenames['Shops.Comments']);
		if ($result == false) {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		if ($arr == null) {
			$this->db->clear();
			throw new ShopNotExistsException('Комментирий не существует');
		}
		$arr['id'] = (int)$arr['id'];
		$arr['parent'] = (int)$arr['parent'];
		$arr['user'] = (int)$arr['user'];
		$arr['rating'] = (int)$arr['rating'];
		$result->close();
		$this->db->clear();
		return $arr;
	}
	public function getByParent($parent,$user) {
		if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$parent);
		$cond->add('user','=',$user);
		$result = $this->db->select($this->tablenames['Shops.Comments']);
		if ($result == false) {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		if ($arr == null) {
			$this->db->clear();
			throw new ShopNotExistsException('Комментирий не существует');
		}
		$arr['id'] = (int)$arr['id'];
		$arr['parent'] = (int)$arr['parent'];
		$arr['user'] = (int)$arr['user'];
		$arr['rating'] = (int)$arr['rating'];
		$result->close();
		$this->db->clear();
		return $arr;
	}
	public function set($id,$rating,$comment) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if ($rating != null && !is_int($rating)) throw new \InvalidArgumentException('$rating must be int');
		if ($rating != null && ($rating < 0 || $rating > 5)) throw new \InvalidArgumentException('0 >= $rating <= 5');
		if ($comment != null && !is_string($comment)) throw new \InvalidArgumentException('$comment must be string');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($comment != null)
		    $tables['comment'] = $comment;
		if ($rating != null)
		    $tables['rating'] = $rating;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Comments'],$tables);
		$this->db->clear();
	}
	public function setByParent($parent,$user,$rating,$comment) {
		if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		if ($rating != null && !is_int($rating)) throw new \InvalidArgumentException('$rating must be int');
		if ($rating != null && ($rating < 0 || $rating > 5)) throw new \InvalidArgumentException('0 >= $rating <= 5');
		if ($comment != null && !is_string($comment)) throw new \InvalidArgumentException('$comment must be string');
		
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$parent);
		$cond->add('user','=',$user);
		$tables = array();
		if ($comment != null)
		    $tables['comment'] = $comment;
		if ($rating != null)
		    $tables['rating'] = $rating;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Comments'],$tables);
		$this->db->clear();
	}
	public function del($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Comments']);
		$this->db->clear();
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
	}
	public function delByParent($parent,$user) {
		if (!is_int($parent)) throw new \InvalidArgumentException('$parent must be int');
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$parent);
		$cond->add('user','=',$user);
		$result = $this->db->delete($this->tablenames['Shops.Comments']);
		$this->db->clear();
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
	}
}
