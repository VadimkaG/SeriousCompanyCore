<?
namespace events\Admin;
class RemovePerm extends \events\Event {

	public function init() {
	    $this->authModule = getModule('accounts');
		$this->error = false;
		$this->fields['isGroup'] = false;
		if ($this->getValue('group') != '') {
		    $this->fields['isGroup'] = true;
		    $this->fields['id'] = $this->getValue('group');
		} else $this->fields['id'] = $this->getValue('user');
		$this->fields['permission'] = $this->getValue('permission');
	}
	
	public function validate() {
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if (!checkPerm('admin.permission'))
			$this->error = "Вам не разрешено добавлять привилегии.";
			
	    if ($this->fields['isGroup'] == true && $this->fields['id'] == '')
	        $this->error = 'Поле группы пустое';
	    else if ($this->fields['isGroup'] == false && $this->fields['id'] == '')
	        $this->error = 'Поле пользователя пустое';
		
		if ($this->fields['permission'] == '')
			$this->error = 'Поле привилегии пустое';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
		try {
		    if ($this->fields['isGroup'] == true)
		        $utils = $this->authModule->getGroupUtils();
		    else
		        $utils = $this->authModule->getAccountUtils();
            $utils->delPermission((int)$this->fields['id'],$this->fields['permission']);
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		die('Ok.');
		return "";
	}
	
	public function failed() {
		die($this->error);
		return "";
	}
}
