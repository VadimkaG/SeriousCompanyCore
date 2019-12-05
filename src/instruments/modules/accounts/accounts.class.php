<?PHP
namespace modules\accounts;
if (!function_exists('loadModule') || !function_exists('loadInstuments')) die("Ошибка: Ядро не импортировано");
if (!class_exists('\modules\Module')) loadInstuments('module');
if (!class_exists('\modules\Module')) die("Ошибка: Система модулей не инициализирована");
require_once(__DIR__.'/Exceptions.php');
class index extends \modules\Module {
	const VERSION = '1.0';
	public function install(&$db,&$tn) {
	    if (!isset($tn['accounts']) || !isset($tn['PermissionGroups'])) return false;
		$db->row_query("drop table if exists ".$tn['accounts']);
		$db->row_query("create table ".$tn['accounts']." (id integer primary key auto_increment, login varchar(50), password TINYTEXT, ugroup integer, email TINYTEXT)");
		$db->row_query("insert into ".$tn['accounts']." (login,password,ugroup) values ('admin','".index::cryptString('admin')."',1)");
		
		$db->row_query("drop table if exists ".$tn['PermissionGroups']);
		$db->row_query("create table ".$tn['PermissionGroups']." (id integer primary key auto_increment, name varchar(50) )");
		$db->row_query("insert into ".$tn['PermissionGroups']." (id, name) values (1, 'Администратор')");
		$db->row_query("insert into ".$tn['PermissionGroups']." (id, name) values (2, 'Пользователь')");
		
		/**
		 * Значения parent:
		 * 0 = user
		 * 1 = group
		 */
		$db->row_query("drop table if exists ".$tn['Permissions']);
		$db->row_query("create table ".$tn['Permissions']." (parent integer, type BIT(1), perm text)");
	}
	function init() {
		if (
			!isset($this->config['data']) ||
			!isset($this->config['tablenames']) ||
			!isset($this->config['tablenames']['accounts']) ||
			!isset($this->config['tablenames']['PermissionGroups']) ||
			!isset($this->config['tablenames']['Permissions'])
		) return false;
		return true;
	}
	public static function cryptString($str) {
		if (!is_string($str)) throw new \InvalidArgumentException();
		return md5($str);
	}
	
    public static function genSession($login,$password) {
	    return genMD("SES00135002log00".$login."001122pass00".$password."huia125");
    }
	private $accutils = null;
	public function getAccountUtils() {
		if ($this->accutils == null) {
			require_once(__DIR__.'/accutils.class.php');
			$this->accutils = new AccountUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->accutils;
	}
	private $grutils = null;
	public function getGroupUtils() {
		if ($this->grutils == null) {
			require_once(__DIR__.'/grutils.class.php');
			$this->grutils =  new GroupUtils($this->config['data'],$this->config['tablenames']);
		}
		return $this->grutils;
	}
}
