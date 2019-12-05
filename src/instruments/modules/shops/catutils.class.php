<?PHP
namespace modules\shops;
if (!class_exists('\modules\shops\index')) die("Ошибка: Используйте \modules\shops\index::getShopUtils()");
class CategoryUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	public function count() {
		$result = $this->db->select(
				$this->tablenames['Shops.Category'],
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
	public function listCat($page = null,$perPage = null) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		$result = $this->db->select($this->tablenames['Shops.Category']);
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$categories = array();
		while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
			$categories[] = $arr;
		}
		$result->close();
		$this->db->clear();
		return $categories;
	}
	public function add($name,$icon = 1) {
		if (!is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_int($icon)) throw new \InvalidArgumentException('$icon must be int');
		if ($icon < 1 || $icon > 12) throw new \InvalidArgumentException('13 < $icon > 0');
		
		$cond = $this->db->setCondition('and');
		$cond->add('name','=',$name);
		$result = $this->db->select($this->tablenames['Shops.Category']);
		if ($result != false) {
			$arr = $result->fetch_array(MYSQLI_ASSOC);
			if ($arr != null) {
				$result->close();
				$this->db->clear();
				throw new ShopNotExistsException('Такая категория уже существует');
			}
			$result->close();
		} else throw new ShopQueryFailedException($this->db->getError());
		
		$this->db->clear();
		if ($this->db->getError() != '') throw new ShopQueryFailedException($this->db->getError());
		$result = $this->db->insert($this->tablenames['Shops.Category'],
			array(
				"'".$name."'",
				$icon
			),
			array(
			    'name',
			    'icon'
			),
			true
		);
		$this->db->clear();
		if ($this->db->getError() != '')
			throw new ShopQueryFailedException($this->db->getError());
		return $result;
	}
	public function set($id,$name,$icon = 1) {
		if ($name != null && !is_string($name)) throw new \InvalidArgumentException('$name must be string');
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		if (!is_int($icon)) throw new \InvalidArgumentException('$icon must be int');
		if ($icon < 1 || $icon > 12) throw new \InvalidArgumentException('13 < $icon > 0');
		
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$tables = array();
		if ($name != null)
		    $tables['name'] = $name;
		if ($icon != null)
		    $tables['icon'] = $icon;
		if (count($tables) > 0)
		    $this->db->update($this->tablenames['Shops.Category'],$tables);
		$this->db->clear();
	}
	public function del($id) {
		if (!is_int($id)) throw new \InvalidArgumentException('$id must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('id','=',$id);
		$result = $this->db->delete($this->tablenames['Shops.Category']);
		$this->db->clear();
		if (!$result)
			throw new ShopQueryFailedException($this->db->getError());
	}
}
