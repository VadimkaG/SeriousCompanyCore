<?
namespace events;
class EditUser extends Event {

    private $authModule;

	public function init() {
		$this->authModule = getModule('accounts');
		$this->error = "";
		
		if ($this->getValue('id') == '') $this->fields['id'] = $_SESSION['user_id'];
		else $this->fields['id'] = (int)$this->getValue('id');
		
		if ((int)$this->fields['id'] == (int)$_SESSION['user_id']) $this->selfEdit = true;
		else $this->selfEdit= false;
		
		$this->fields['login'] = $this->getValue('login');
		if ($this->fields['login'] == '') $this->fields['login'] = null;
		$this->fields['password'] = $this->getValue('password');
		if ($this->fields['password'] == '') $this->fields['password'] = null;
		$this->fields['group'] = (int)$this->getValue('group');
		if ($this->fields['group'] == '') $this->fields['group'] = null;
		$this->fields['email'] = $this->getValue('email');
		if ($this->fields['email'] == '') $this->fields['email'] = null;
	}
	
	public function validate() {
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->selfEdit == false && !checkPerm('admin.user.edit')) {
			$this->error = "Вам не разрешено редактировать других пользователей.";
			return false;
		}
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
	    
	    if ($this->selfEdit == true) $this->fields['group'] = null;
	    
	    try {
            $authUtils = $this->authModule->getAccountUtils();
            $authUtils->setAccount($this->fields['id'],$this->fields['login'],$this->fields['password'],$this->fields['email'],$this->fields['group']);
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		return true;
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
