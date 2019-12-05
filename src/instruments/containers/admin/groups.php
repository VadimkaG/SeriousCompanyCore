<?
namespace page\containers\Admin;
class Groups extends \page\Container {

    private $groups;
    
	public function init() {
		if (isset($_GET['perms'])) {
		    $this->printPerms($_GET['perms']);
		    die();
		}
		$authModule = getModule('accounts');
		if ($authModule == null)
		    fatalError('Не удалось загрузить модуль "accounts"');
		$grutils = $authModule->getGroupUtils();
		try{
		    $this->groups = $grutils->listGroups();
		} catch (\modules\accounts\AuthFailedException $e) {
		    fatalError('Ошибка подгрузки групп: '.$e->getMessage());
		}
		require_once(containers.'/admin/layout.php');
		$page = new Layout('layout');
		$page->addFunction($this,'proc');
		$page->addScripts($this->template,'scripts',array("pubres"=>pubres));
		$page->setTitle("Группы");
		$page->init();
	}
	function Block_groups($func,&$c,&$data) {
		foreach ($this->groups as $group) {
			$c->$func(array(
			    'can_ch_login'=>"Разрешена",
			    'can_change_login'=>"1",
			    'id'=>$group['id'],
			    'name'=>$group['name']
			),$group);
		}
	}
	function Block_if_delete($func,&$c,&$data) {
		if ($data['id']!=1 and $data['id']!=2)
			$c->$func(array('id'=>$data['id']));
	}
	function printPerms($id) {
        $authModule = getModule('accounts');
	    if ($authModule == null) die('Не удалось загрузить модуль "accounts"');
        $grutils = $authModule->getGroupUtils();
	    try {
		    $perms = $grutils->listPermission((int)$id);
		    echo '[';
		    $first = true;
		    foreach ($perms as $perm) {
		        if ($first != true) echo ',';
		        else $first = false;
		        echo '"'.$perm.'"';
		    }
		    echo ']';
        } catch (\modules\accounts\AuthFailedException $e) {
            echo $e->getMessage();
        }
	}
}
