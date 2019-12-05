<?
namespace events;
class Register extends Event {

    private $authModule;

	public function init() {
	    $this->authModule = getModule('accounts');
		$this->error = "";
		$this->fields['login'] = $this->getValue('login');
		$this->fields['password'] = $this->getValue('password');
		$this->fields['password_confirm'] = $this->getValue('password_confirm');
	}
	
	public function validate() {
		global $config;
		
		if ($config['authEnabled']!='True' && !checkPerm('register.bypass')) {
			$this->error = "Регистрация выключена администратором.";
			return false;
		}
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->fields['login'] == '')
			$this->error = 'Логин пустой';
		
		if ($this->fields['password'] == '')
			$this->error = 'Пароль пустой';
		
		if ($this->fields['password'] != $this->fields['password_confirm'])
			$this->error = 'Пароли не совпадают';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
	    try {
            $authUtils = $this->authModule->getAccountUtils();
            $authUtils->addAccount($this->fields['login'],$this->fields['password']);
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		if (isset($_GET['registerShop'])) Redirect('/register-shop/');
		return array('warning'=>'Вы успешно зарегистрировались, можете авторизоваться');
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
