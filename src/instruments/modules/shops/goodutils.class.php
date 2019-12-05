<?PHP
namespace modules\shops;
if (!class_exists('\modules\shops\index')) die("Ошибка: Используйте \modules\shops\index::getGoodUtils()");
class GoodUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	public function count($shop = null) {
		$cond = $this->db->setCondition('and');
		$cond->add('g.id','=','h.id',true);
		if ($shop != null)
			$cond->add('g.shop','=',$shop);
		$result = $this->db->select(
				array(
					"g"=>$this->tablenames['Shops.Goods'],
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
	public function listGoods($page = null,$perPage = null, $shop = null,$active = true) {
		if ($shop != null && !is_int($shop)) throw new \InvalidArgumentException('$shop must be int');
		if ($active != null && !is_bool($active)) throw new \InvalidArgumentException('$active must be boolean');
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		$cond = $this->db->setCondition('and');
		if ($active == true) {
			$cond->add('h.active','=','1');
			$cond->add('sh.active','=','1');
		}
		$cond->add('s.id','=','sh.id',true);
		$cond->add('g.id','=','h.id',true);
		$cond->add('g.shop','=','s.id',true);
		$cond->add('g.category','=','c.id',true);
		if ($shop != null)
			$cond->add('g.shop','=',$shop,true);
		$this->db->sort('g.rating',false);
		$this->db->sort('g.rating_five',false);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"g"=>$this->tablenames['Shops.Goods'],
					"h"=>$this->tablenames['Shops.Header'],
					"sh"=>$this->tablenames['Shops.Header'],
					"c"=>$this->tablenames['Shops.Category']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"shop_id"=>"g.shop",
					"shop_name"=>"s.name",
					"email"=>"s.email",
					"phones"=>"s.phones",
					"address"=>"s.adress",
					"map"=>"s.map",
					"shop_alias"=>"sh.alias",
					"name"=>"g.name",
					"rating_booster"=>"g.rating_booster",
					"price"=>"g.price",
					"discount"=>"g.discount",
					"created"=>"h.time_created",
					"active"=>"h.active",
					"category_id"=>"c.id",
					"category"=>"c.name",
					"rating"=>"g.rating_five",
					"rating_total"=>"g.rating"
				)
			);
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$shops = array();
		while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
			$arr['id'] = (int)$arr['id'];
			$arr['shop_id'] = (int)$arr['shop_id'];
			$arr['rating_booster'] = (int)$arr['rating_booster'];
			$arr['rating'] = (int)$arr['rating'];
			$arr['discount'] = (int)$arr['discount'];
			$arr['price'] = (double)$arr['price'];
			$arr['category_id'] = (int)$arr['category_id'];
			$shops[] = $arr;
		}
		$result->close();
		$this->db->clear();
		return $shops;
	}
	function exists($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->select($this->tablenames['Shops.Goods']);
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
	function existsHeader($alias) {
	}
	public function add($alias,$shop,$category,$name,$price = 0.0, $discount = 0) {
		//if ($this->existsHeader($alias)) throw new AliasExistsException('Алиас '.$alias.' уже занят');
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_int($category)) throw new \InvalidArgumentException('$category must be int');
		if (!is_double($price)) throw new \InvalidArgumentException('$price must be double');
		if (!is_int($discount)) throw new \InvalidArgumentException('$discount must be int');
		if ($discount < 0 or $discount > 100) throw new \InvalidArgumentException('0 > $discount < 100');
		if (!is_int($shop)) throw new \InvalidArgumentException('$shop must be int');
		if (!is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('g.id','=','h.id',true);
		$cond->add('g.shop','=',$shop);
		$cond->add('h.alias','=',$alias);
		$result = $this->db->select(array(
					"h"=>$this->tablenames['Shops.Header'],
					"g"=>$this->tablenames['Shops.Goods']
				),array(
					"c"=>"Count(*)"
				));
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null || ((int)$arr['c']) > 0) {
				$result->close();
				$this->db->clear();
				throw new AliasExistsException('Псевдоним '.$alias.' уже занят в магазине '.$shop);
			}
			$result->close();
			$this->db->clear();
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$shop);
		$result = $this->db->select($this->tablenames['Shops']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$result->close();
				$this->db->clear();
				throw new ShopNotExistsException('Магазин с id '.$shop.' не найден.');
			}
			$result->close();
		} else throw new ShopQueryFailedException($this->db->getError());
		$this->db->clear();
		
		$result = $this->db->insert($this->tablenames['Shops.Header'],
			array(
				"'".$alias."'",
				'NOW()'
			),
			array(
			    'alias',
			    'time_created'
			),
			true
		);
		if ($this->db->getError() != '') throw new ShopQueryFailedException($this->db->getError());
		$result = $this->db->insert($this->tablenames['Shops.Goods'],
			array(
				$result,
				"'".$name."'",
				$price,
				$discount,
				$shop,
				$category
			),
			array(
				'id',
			    'name',
			    'price',
			    'discount',
			    'shop',
			    'category'
			),
			true
		);
		$this->db->clear();
		if ($this->db->getError() != '')
			throw new ShopQueryFailedException($this->db->getError());
	}
	public function get($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('g.id','=','h.id',true);
		$cond->add('g.shop','=','s.id',true);
		$cond->add('g.category','=','c.id',true);
		$cond->add('g.id','=',$id);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"g"=>$this->tablenames['Shops.Goods'],
					"h"=>$this->tablenames['Shops.Header'],
					"c"=>$this->tablenames['Shops.Category']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"shop_id"=>"g.shop",
					"shop_name"=>"s.name",
					"name"=>"g.name",
					"email"=>"s.email",
					"phones"=>"s.phones",
					"address"=>"s.adress",
					"map"=>"s.map",
					"rating_booster"=>"g.rating_booster",
					"price"=>"g.price",
					"discount"=>"g.discount",
					"created"=>"h.time_created",
					"active"=>"h.active",
					"category_id"=>"c.id",
					"category"=>"c.name",
					"rating"=>"g.rating_five"
				)
			);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$this->db->clear();
				throw new GoodNotExistsException('Товар с id '.$id.' не найден.');
			}
			$arr['id'] = (int)$arr['id'];
			$arr['shop_id'] = (int)$arr['shop_id'];
			$arr['rating_booster'] = (int)$arr['rating_booster'];
			$arr['rating'] = (int)$arr['rating'];
			$arr['discount'] = (int)$arr['discount'];
			$arr['category_id'] = (int)$arr['category_id'];
			$arr['price'] = (double)$arr['price'];
			$result->close();
			$this->db->clear();
			return $arr;
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	public function getByAlias($alias,$shop_alias) {
		if (!is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		if (!is_string($shop_alias)) throw new \InvalidArgumentException('$shop_alias must be string');
		$cond = $this->db->setCondition('and');
		$cond->add('g.id','=','h.id',true);
		$cond->add('sh.id','=','g.shop',true);
		$cond->add('g.shop','=','s.id',true);
		$cond->add('h.alias','=',$alias);
		$cond->add('sh.alias','=',$shop_alias);
		$cond->add('g.category','=','c.id',true);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"g"=>$this->tablenames['Shops.Goods'],
					"h"=>$this->tablenames['Shops.Header'],
					"sh"=>$this->tablenames['Shops.Header'],
					"c"=>$this->tablenames['Shops.Category']
				),
				array(
					"id"=>"h.id",
					"alias"=>"h.alias",
					"shop_id"=>"g.shop",
					"shop_name"=>"s.name",
					"email"=>"s.email",
					"phones"=>"s.phones",
					"address"=>"s.adress",
					"map"=>"s.map",
					"shop_alias"=>"sh.alias",
					"name"=>"g.name",
					"rating_booster"=>"g.rating_booster",
					"price"=>"g.price",
					"discount"=>"g.discount",
					"created"=>"h.time_created",
					"active"=>"h.active",
					"category"=>"c.name",
					"rating"=>"g.rating_five"
				)
			);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr == null) {
				$this->db->clear();
				throw new GoodNotExistsException('Товар с id '.$alias.' не найден.');
			}
			$arr['id'] = (int)$arr['id'];
			$arr['shop_id'] = (int)$arr['shop_id'];
			$arr['rating_booster'] = (int)$arr['rating_booster'];
			$arr['rating'] = (int)$arr['rating'];
			$arr['discount'] = (int)$arr['discount'];
			$arr['price'] = (double)$arr['price'];
			$result->close();
			$this->db->clear();
			
			return $arr;
		} else {
			$this->db->clear();
			throw new ShopQueryFailedException($this->db->getError());
		}
	}
	public function updateRating($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		
		// Обновляем рейтинг у товара
		$cond = $this->db->setCondition('and');
		$cond->add('cc.parent','=','g.id',true);
		$cond->add('cc.parent','=',$id);
		$this->db->groupBy(array('cc.rating'));
		$result = $this->db->select(
			array(
				'cc'=>$this->tablenames['Shops.Comments'],
				'g'=>$this->tablenames['Shops.Goods']
			),
			array(
				'cout'=>'Count(cc.rating)',
				'cc.rating',
				'g.rating_booster'
			)
		);
		if ($result != null) {
			$rating5 = 0;
			$rating4 = 0;
			$rating3 = 0;
			$rating2 = 0;
			$rating1 = 0;
			$rating0 = 0;
			$rating = 0;
			$rating_booster = 0;
			$rating_five = 0;
			while ($comarr = $result->fetch_array(MYSQLI_ASSOC)) {
				$rating_booster = $comarr['rating_booster'];
				if ($comarr != null) {
					switch($comarr['rating']) {
						case "5":
							$rating5 = $comarr['cout'];
							break;
						case "4":
							$rating4 = $comarr['cout'];
							break;
						case "3":
							$rating3 = $comarr['cout'];
							break;
						case "2":
							$rating2 = $comarr['cout'];
							break;
						case "1":
							$rating1 = $comarr['cout'];
							break;
						default:
							$rating0 = $comarr['cout'];
							break;
					}
				}
			}
			$sum = $rating5 + $rating4 + $rating3 + $rating2 + $rating1 + $rating0;
			if ($sum > 0) {
				$rating_five = (5 * $rating5 + 4 * $rating4 + 3 * $rating3 + 2 * $rating2 + $rating1) / $sum;
				$rating = 5 * $rating5 + 4 * $rating4 + 3 * $rating3 + 2 * $rating2 + $rating1 - $rating0 + $rating_booster;
			}
			$this->db->clear();
			$cond = $this->db->setCondition('and');
			$cond->add('id','=',$id);
			$this->db->update($this->tablenames['Shops.Goods'],array(
				"rating"=>$rating,
				"rating_five"=>$rating_five
			));
		}
		$this->db->clear();
		
		// Обновляем рейтинг у магазина
		$cond = $this->db->setCondition('and');
		$cond->add('cc.parent','=','g.id',true);
		$cond->add('g.shop','=','(SELECT shop FROM goods WHERE id = '.$id.')',true);
		$this->db->groupBy(array('rating'));
		$result = $this->db->select(
			array(
				'cc'=>$this->tablenames['Shops.Comments'],
				'g'=>$this->tablenames['Shops.Goods']
			),
			array(
			'cout'=>'Count(cc.rating)',
			'cc.rating',
			'g.shop'
		));
		if ($result != null) {
			$rating5 = 0;
			$rating4 = 0;
			$rating3 = 0;
			$rating2 = 0;
			$rating1 = 0;
			$rating0 = 0;
			$rating = 0;
			$rating_five = 0;
			$shop = 0;
			while ($comarr = $result->fetch_array(MYSQLI_ASSOC)) {
				if ($comarr != null) {
					$shop = $comarr['shop'];
					switch($comarr['rating']) {
						case "5":
							$rating5 = $comarr['cout'];
							break;
						case "4":
							$rating4 = $comarr['cout'];
							break;
						case "3":
							$rating3 = $comarr['cout'];
							break;
						case "2":
							$rating2 = $comarr['cout'];
							break;
						case "1":
							$rating1 = $comarr['cout'];
							break;
						default:
							$rating0 = $comarr['cout'];
							break;
					}
				}
			}
			$sum = $rating5 + $rating4 + $rating3 + $rating2 + $rating1 + $rating0;
			if ($sum > 0) {
				$rating_five = (5 * $rating5 + 4 * $rating4 + 3 * $rating3 + 2 * $rating2 + $rating1) / $sum;
				$rating = 5 * $rating5 + 4 * $rating4 + 3 * $rating3 + 2 * $rating2 + $rating1 - $rating0;
			}
			$this->db->clear();
			$cond = $this->db->setCondition('and');
			$cond->add('id','=',$shop);
			$this->db->update($this->tablenames['Shops'],array(
				"rating"=>$rating,
				"rating_five"=>$rating_five
			));
		}
		$this->db->clear();
	}
	public function setHeader($id,$alias,$active = -1) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if ($alias != null && !is_string($alias)) throw new \InvalidArgumentException('$alias must be string');
		if ($active != -1 && !is_bool($active)) throw new \InvalidArgumentException('$active must be boolean');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($alias != null)
		    $tables['alias'] = $alias;
		if ($active != -1 && $active = true)
		    $tables['active'] = 1;
		else if ($active != -1)
			$tables['active'] = 0;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Header'],$tables);
		$this->db->clear();
	}
	public function set($id,$name,$category = null,$price = null,$discount = null,$rating_booster = null) {
		if (!$this->exists($id)) throw new GoodNotExistsException('Товар с id '.$id.' не найден.');
		if ($name != null && !is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if ($price != null && !is_double($price)) throw new \InvalidArgumentException('$price must be double');
		if ($discount != null && !is_int($discount)) throw new \InvalidArgumentException('$discount must be int');
		if ($category != null && !is_int($category)) throw new \InvalidArgumentException('$category must be int');
		if ($discount!= null && ($discount < 0 || $discount > 100)) throw new \InvalidArgumentException('0 > $discount < 100');
		if ($rating_booster != null && !is_int($rating_booster)) throw new \InvalidArgumentException('$rating_booster must be int');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($name != null)
		    $tables['name'] = $name;
		if ($price != null)
		    $tables['price'] = $price;
		if ($discount != null)
		    $tables['discount'] = $discount;
		if ($category != null)
		    $tables['category'] = $category;
		if ($rating_booster != null)
		    $tables['rating_booster'] = $rating_booster;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Goods'],$tables);
		$this->db->clear();
	}
	public function del($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if (!$this->exists($id)) throw new GoodNotExistsException('Товар с id '.$id.' не найден.');
		$cond = $this->db->setCondition('and');
		$cond->add('parent','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Comments']);
		$this->db->clear();
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Goods']);
		$result = $this->db->delete($this->tablenames['Shops.Header']);
		$this->db->clear();
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
	}
}
