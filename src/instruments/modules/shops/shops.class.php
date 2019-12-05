<?PHP
namespace modules\shops;
if (!function_exists('loadModule') || !function_exists('loadInstuments')) die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) loadInstuments('module');
if (!class_exists('\modules\Module')) die("Ошибка: Система модулей не инициализирована");
require_once(__DIR__.'/Exceptions.php');
class index extends \modules\Module {
	const VERSION = '1.0';
	public function install(&$db,&$tn) {
		
		$db->row_query("drop table if exists ".$tn['Shops.Comments']);
		$db->row_query("drop table if exists ".$tn['Shops.Goods']);
		$db->row_query("drop table if exists ".$tn['Shops.Admins']);
		$db->row_query("drop table if exists ".$tn['Shops']);
		$db->row_query("drop table if exists ".$tn['Shops.Category']);
		$db->row_query("drop table if exists ".$tn['Shops.Header']);
		
		$db->row_query("create table ".$tn['Shops.Header']." ("
				."id bigint primary key auto_increment,"
				."alias varchar(50) not null,"
				."time_created datetime not null,"
				."active tinyint default 1"
			.")");
		
		$db->row_query("create table ".$tn['Shops']." ("
				."id bigint not null,"
				."name text not null,"
				."adress text,"
				."phones text,"
				."email text,"
				."map text,"
				."rating_five DECIMAL(10,4) default 0,"
				."rating BIGINT default 0,"
				."foreign key (id) references ".$tn['Shops.Header']."(id)"
			.")");
		/**
		 * Значения permission:
		 * 0 - Полные привилегии
		 * 1 - Может модерировать удалять магазин и изменять его название и список администрации
		 * 2 - Может редактировать список товаров
		 */
		$db->row_query("create table ".$tn['Shops.Admins']." ("
				."id bigint primary key auto_increment,"
				."shop bigint,"
				."user bigint,"
				."permission TINYINT,"
				."foreign key (shop) references ".$tn['Shops']."(id)"
			.")");
		
		$db->row_query("create table ".$tn['Shops.Category']." ("
				."id bigint primary key auto_increment,"
				."name text,"
				."icon tinyint default 1"
			.")");
			
		$db->row_query("create table ".$tn['Shops.Goods']." ("
				."id bigint not null,"
				."shop bigint not null,"
				."category bigint not null,"
				."rating_booster bigint default 0,"
				."rating_five DECIMAL(10,4) default 0,"
				."rating BIGINT default 0,"
				."name varchar(50) not null,"
				."price DECIMAL(10,2) not null,"
				."discount TINYINT default 0,"
				."foreign key (id) references ".$tn['Shops.Header']."(id),"
				."foreign key (shop) references ".$tn['Shops']."(id),"
				."foreign key (category) references ".$tn['Shops.Category']."(id)"
			.")");
		
		$db->row_query("create table ".$tn['Shops.Comments']." ("
				."id bigint primary key auto_increment,"
				."parent bigint not null,"
				."user bigint not null,"
				."comment text,"
				."rating TINYINT default 0,"
				."time_created datetime not null,"
				."foreign key (parent) references ".$tn['Shops.Header']."(id)"
			.")");
	}
	function init() {
		if (
			!isset($this->config['data']) ||
			!isset($this->config['tablenames']) ||
			!isset($this->config['tablenames']['Shops']) ||
			!isset($this->config['tablenames']['Shops.Admins']) ||
			!isset($this->config['tablenames']['Shops.Goods']) ||
			!isset($this->config['tablenames']['Shops.Comments'])
		) return false;
		return true;
    }
    private $shopUtils = null;
    function getShopUtils() {
		if ($this->shopUtils == null) {
			require_once(__DIR__.'/shoputils.class.php');
			$this->shopUtils = new ShopUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->shopUtils;
    }
    private $goodUtils = null;
    function getGoodUtils() {
		if ($this->goodUtils == null) {
			require_once(__DIR__.'/goodutils.class.php');
			$this->goodUtils = new GoodUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->goodUtils;
    }
    private $categoryUtils = null;
    function getCategoryUtils() {
		if ($this->categoryUtils == null) {
			require_once(__DIR__.'/catutils.class.php');
			$this->categoryUtils = new CategoryUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->categoryUtils;
    }
    private $commentUtils = null;
    function getCommentUtils() {
		if ($this->commentUtils == null) {
			require_once(__DIR__.'/comutils.class.php');
			$this->commentUtils = new CommentUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->commentUtils;
    }
    private $adminUtils = null;
    function getAdminUtils() {
		if ($this->adminUtils == null) {
			require_once(__DIR__.'/adminutils.class.php');
			$this->adminUtils = new AdminUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->adminUtils;
    }
}
