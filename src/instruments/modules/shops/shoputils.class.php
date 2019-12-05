<?PHP
namespace modules\shops;
if (!class_exists('\modules\shops\index')) die("Ошибка: Используйте \modules\shops\index::getShopUtils()");
class ShopUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	public function countShops($active = true) {
		$cond = $this->db->setCondition('and');
		$cond->add('s.id','=','h.id',true);
		if ($active == true)
			$cond->add('active','=','1');
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"h"=>$this->tablenames['Shops.Header']
				),
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
	public function listShops($page = null,$perPage = null,$active = true) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			if (!is_bool($active)) throw new \InvalidArgumentException('$active must be boolean');
			$this->db->setPage($page,$perPage);
		}
		$cond = $this->db->setCondition('and');
		if ($active == true)
			$cond->add('h.active','=','1');
		$cond->add('s.id','=','h.id',true);
		$this->db->sort('s.rating', false);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"h"=>$this->tablenames['Shops.Header']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"name"=>"s.name",
					"adress"=>"s.adress",
					"phones"=>"s.phones",
					"email"=>"s.email",
					"map"=>"s.map",
					"created"=>"h.time_created",
					"active"=>"h.active",
					"rating"=>"s.rating_five",
					"rating_total"=>"s.rating"
				)
			);
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$shops = array();
		while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
			$arr['id'] = (int)$arr['id'];
			$arr['rating'] = 0;
			if ($arr['active'] == 1) $arr['active'] = true;
			else $arr['active'] = false;
			$shops[] = $arr;
		}
		$result->close();
		$this->db->clear();
		return $shops;
	}
	public function addShop($alias,$name,$adress = "",$phones = "",$email = "",$map = "") {
		if ($this->existsShopWithName($name)) throw new ShopExistsException('Магазин с названием '.$name.' уже существует');
		if (!is_string($adress)) throw new \InvalidArgumentException('$adress must be string');
		if (!is_string($phones)) throw new \InvalidArgumentException('$phones must be string');
		if (!is_string($email)) throw new \InvalidArgumentException('$email must be string');
		if (!is_string($map)) throw new \InvalidArgumentException('$map must be string');
		
		if (!is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('g.id','=','h.id',true);
		$cond->add('h.alias','=',$alias);
		$result = $this->db->select(array(
					"h"=>$this->tablenames['Shops.Header'],
					"g"=>$this->tablenames['Shops']
				),array(
					"c"=>"Count(*)"
				));
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null || ((int)$arr['c']) > 0) {
				$result->close();
				$this->db->clear();
				throw new AliasExistsException('Псевдоним '.$alias.' уже занят');
			}
			$result->close();
			$this->db->clear();
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
		
		$id = $this->db->insert($this->tablenames['Shops.Header'],
			array(
				"'".$alias."'",
				'NOW()',
				0
			),
			array(
			    'alias',
			    'time_created',
			    'active'
			),
			true
		);
		if ($this->db->getError() != '') throw new ShopQueryFailedException($this->db->getError());
		$result = $this->db->insert($this->tablenames['Shops'],
			array(
				$id,
				"'".$name."'",
				"'".$adress."'",
				"'".$phones."'",
				"'".$email."'",
				"'".$map."'"
			),
			array(
				'id',
			    'name',
			    'adress',
			    'phones',
			    'email',
			    'map'
			),
			true
		);
		$this->db->clear();
		if ($this->db->getError() != '')
			throw new ShopQueryFailedException($this->db->getError());
		return $id;
	}
	function existsShop($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->select($this->tablenames['Shops']);
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
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	function existsShopWithName($name) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('name','=',$name);
		$result = $this->db->select($this->tablenames['Shops']);
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
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	public function getShop($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('s.id','=','h.id',true);
		$cond->add('s.id','=',$id);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"h"=>$this->tablenames['Shops.Header']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"name"=>"s.name",
					"adress"=>"s.adress",
					"phones"=>"s.phones",
					"email"=>"s.email",
					"map"=>"s.map",
					"created"=>"h.time_created",
					"active"=>"h.active"
				)
			);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$this->db->clear();
				throw new ShopNotExistsException('Магазин с id '.$id.' не найден.');
			}
			$arr['id'] = (int)$arr['id'];
			$arr['rating'] = 0;
			$result->close();
			$this->db->clear();
			return $arr;
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	public function getShopByAlias($alias) {
		if (!is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('s.id','=','h.id',true);
		$cond->add('h.alias','=',$alias);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"h"=>$this->tablenames['Shops.Header']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"name"=>"s.name",
					"adress"=>"s.adress",
					"phones"=>"s.phones",
					"email"=>"s.email",
					"map"=>"s.map",
					"created"=>"h.time_created",
					"active"=>"h.active"
				)
			);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$this->db->clear();
				throw new ShopNotExistsException('Магазин с псевдонимом '.$alias.' не найден.');
			}
			$arr['id'] = (int)$arr['id'];
			$arr['rating'] = 0;
			$arr['active'] = (int)$arr['active'];
			$result->close();
			$this->db->clear();
			return $arr;
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	public function setHeader($id,$alias,$active = 'NULL') {
	
		// Проверяем указана ли переменная
		if (is_string($active) && $active == 'NULL') $active = 3;
		
		// Если указано true ставим 1
		else if ($active == true) $active = 1;
		
		// Если false, то ставим 0-ль
		else $active = 0;
		
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if ($alias != null && !is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($alias != null)
		    $tables['alias'] = $alias;
		    
		// Если переменная указана, то записываем значение (0 или 1)
		if ($active != 3)
		    $tables['active'] = $active;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Header'],$tables);
		$this->db->clear();
	}
	public function setShop($id,$name,$adress = null,$phones = null,$email = null,$map = null) {
		if (!$this->existsShop($id)) throw new ShopNotExistsException('Магазин с id '.$id.' не найден.');
		if ($name != null && !is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if ($adress != null && !is_string($adress)) throw new \InvalidArgumentException('$adress must be string');
		if ($phones != null && !is_string($phones)) throw new \InvalidArgumentException('$phones must be string');
		if ($email != null && !is_string($email)) throw new \InvalidArgumentException('$email must be string');
		if ($map != null && !is_string($map)) throw new \InvalidArgumentException('$map must be string');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($name != null)
		    $tables['name'] = $name;
		if ($adress != null)
		    $tables['adress'] = $adress;
		if ($phones != null)
		    $tables['phones'] = $phones;
		if ($email != null)
		    $tables['email'] = $email;
		if ($map != null)
		    $tables['map'] = $map;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops'],$tables);
		$this->db->clear();
	}
	public function delShop($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if (!$this->existsShop($id)) 
			throw new ShopNotExistsException('Магазин с id '.$id.' не найден.');
		$cond = $this->db->setCondition('and');
		$cond->add('id','IN','(SELECT id FROM '.$this->tablenames['Shops.Goods'].' WHERE shop='.$id.')',true);
		$result = $this->db->delete($this->tablenames['Shops.Header']);
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('shop','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Goods']);
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Header']);
		$result = $this->db->delete($this->tablenames['Shops']);
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
	}
}
