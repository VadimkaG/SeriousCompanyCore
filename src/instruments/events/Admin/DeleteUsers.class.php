<?
namespace events\Admin;
class DeleteUsers extends \events\Event {

    private $authModule;

	public function init() {
	    $this->authModule = getModule('accounts');
		$this->error = false;
		$this->fields['delete'] = $this->getValue('delete');
	}
	
	public function validate() {
            
        if (!isAuth())
			$this->error = "Вы не авторизованы.";
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->fields['delete'] == '' || count($this->fields['delete']) <= 0)
			$this->error = 'Вы не выбрали пользователей, которые хотите удалить';
        
        if ((count($this->fields['delete']) > 1 || $this->fields['delete'][array_key_first($this->fields['delete'])] != $_SESSION['user_id']) && !checkPerm('admin.user.delete'))
		    $this->error = "Вам не разрешено удалять других пользователей.";
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
	    $authUtils = $this->authModule->getAccountUtils();
		foreach ($this->fields['delete'] as $user) {
		    try {
                $authUtils->delAccount((int)$user);
            } catch (\modules\accounts\AuthFailedException $e) {
                $this->error = $e->getMessage();
            }
		}
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		return array('warning'=>'Успешно удалено');
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
