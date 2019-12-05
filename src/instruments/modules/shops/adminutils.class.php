<?PHP
namespace modules\shops;
if (!class_exists('\modules\shops\index')) die("Ошибка: Используйте \modules\shops\index::getAdminUtils()");
class AdminUtils {
	private $db;
	private $tablenames;
	public function __construct(&$database,&$tablenames){
		$this->db = $database;
		$this->tablenames = $tablenames;
	}
	/**
	 * Является ли указанный пользователь админом магазина
	 * @param $user - Идентификатор пользователя
	 * @param $shop - Идентификатор магазина (не обязательно)
	 * @return boolean
	 */
	public function isAdmin($user, $shop = null) {
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		$cond = $this->db->setCondition('and');
		if ($shop != null) {
			if (!is_int($shop)) throw new \InvalidArgumentException('$shop must be int');
			$cond->add('shop','=',$shop);
		}
		$cond->add('user','=',$user);
		$result = $this->db->select($this->tablenames['Shops.Admins'],array("c"=>'Count(*)'));
		$this->db->clear();
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		if ($arr['c'] > 0) {
			$result->close();
			return true;
		}
		$result->close();
		return false;
	}
	/**
	 * Получить список магазинов, в которых пользователь администратор
	 * @param $user - Идентификатор пользователя
	 * @return array()
	 */
	public function getUserShops($user) {
		if (!is_int($user)) throw new \InvalidArgumentException('$user must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('a.user','=',$user);
		$cond->add('a.shop','=','h.id');
		$cond->add('s.shop','=','h.id',true);
		$this->db->sort('s.rating', false);
		$result = $this->db->select(
				array(
					"s"=>$this->tablenames['Shops'],
					"h"=>$this->tablenames['Shops.Header'],
					"a"=>$this->tablenames['Shops.Admins']
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
		$this->db->clear();
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
		return $shops;
	}
	/**
	 * Получить список админов магазина
	 * @param $shop - id магазина
	 * @return array()
	 */
	public function getShopAdmins($page = null,$perPage = null,$shop) {
		if ($page != null && $perPage != null) {
			if (!is_int($page)) throw new \InvalidArgumentException('$page must be int');
			if (!is_int($perPage)) throw new \InvalidArgumentException('$perPage must be int');
			$this->db->setPage($page,$perPage);
		}
		if (!is_int($shop)) throw new \InvalidArgumentException('$shop must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('shop','=',$shop);
		$result = $this->db->select($this->tablenames['Shops.Admins']);
		$this->db->clear();
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$admins = array();
		while ($arr = $result->fetch_array(MYSQLI_ASSOC)) {
			$arr["id"] = (int)$arr["id"];
			$arr["shop"] = (int)$arr["shop"];
			$arr["user"] = (int)$arr["user"];
			$arr["permission"] = (int)$arr["permission"];
			$admins[] = $arr;
		}
		$result->close();
		return $admins;
	}
	/**
	 * Количество админов в магазине
	 * @param $shop - Идентификарот магазина
	 */
	public function countShopAdmins($shop) {
		if (!is_int($shop)) throw new \InvalidArgumentException('$shop must be int');
		$cond = $this->db->setCondition('and');
		$cond->add('shop','=',$shop);
		$result = $this->db->select(
		$this->tablenames['Shops.Admins'],
				array(
					"c"=>'Count(*)'
				)
			);
		$this->db->clear();
		if ($result == false) throw new ShopQueryFailedException($this->db->getError());
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		$result->close();
		return (int)$arr['c'];
	}
	/**
	 * Добавить админа в магазин
	 * @param $user - Идентификатор пользователя
	 * @param $shop - Идентификатор магазина
	 * @param $permission - Привилегия
	 */
	public function addAdmin($user, $shop, $permission) {
	}
}
