<?
namespace page\containers\Admin;
class Users extends \page\Container {

    private $authModule;
    
	public function init() {
	    $this->authModule = getModule('accounts');
		if (isset($_GET['countUsers'])) {
		    if ($this->authModule == null) die('0');
		    $accutils = $this->authModule->getAccountUtils();
		    echo $accutils->countAccounts();
			die();
		}
		if (isset($_GET['json'])) die($this->users_JSON());
		require_once(containers.'/admin/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc');
		$page->addScripts($this->template,'scripts');
		$page->setTitle("Пользователи");
		$page->init();
	}
	function Block_groups($func,&$c) {
	    $accutils = $this->authModule->getGroupUtils();
		foreach ($accutils->listGroups() as $group) {
			$c->$func($group);
		}
	}
	function users_JSON() {
		$authModule = getModule('accounts');
		if ($authModule == null) die('{}');
		$accutils = $authModule->getAccountUtils();
		$grutils = $authModule->getGroupUtils();
		echo '{';
		if (isset($_GET['page'])) $users = $accutils->listAccounts((int)trim($_GET['page']),20);
		else $users = $accutils->listAccounts(1,20);
		foreach ($users as $user) {
		    $group = $grutils->getGroupByUserId($user['id']);
			echo '"'.$user['id'].'":{';
			echo '"id":"'.$user['id'].'",';
			echo '"login":"'.$user['login'].'",';
			echo '"group-id":"'.$user['group'].'",';
			echo '"group":"'.$group['name'].'",';
		
			$permsCon = "";
			$perms = $accutils->listPermission($user['id']);
			if ($user['group']==1) $permsCon = 'Все привилегии';
			else if (count($perms)) {
				foreach ($perms as $perm) {
					$permsCon .= $perm;
					if ($perm != end($perms)) $permsCon .= '<br>';
				}
			} else {
				$permsCon = "Привилегии отстуствуют";
			}
		
			echo '"perms":"'.$permsCon.'"';
			echo '}';
			if ($user != end($users)) echo ',';
		}
		echo '}';
		die();
	}
}
