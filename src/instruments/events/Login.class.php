<?
namespace events;
class Login extends Event {

    private $authModule;

	public function init() {
        $this->authModule = getModule('accounts');
		$this->error = "";
		$this->fields['login'] = $this->getValue('login');
		$this->fields['password'] = $this->getValue('password');
		if ($this->getValue('remember') != '') $this->fields['remember'] = true;
		else $this->fields['remember'] = false;
	}
	
	public function validate() {
	    
        if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
        
		if ($this->fields['login'] == '')
			$this->error = 'Логин пустой';
		
		if ($this->fields['password'] == '')
			$this->error = 'Пароль пустой';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
        
        try {
            $authUtils = $this->authModule->getAccountUtils();
            $user = $authUtils->getAccountByLogin($this->fields['login'],$this->fields['password']);
            $_SESSION['user_id'] = $user['id'];
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		Redirect('/');
		return true;
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
