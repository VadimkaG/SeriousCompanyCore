<?
namespace events\Admin;
class AddGroup extends \events\Event {

	public function init() {
	    $this->authModule = getModule('accounts');
		$this->error = false;
		$this->fields['name'] = $this->getValue('name');
	}
	
	public function validate() {
		
		if (!checkPerm('admin.group.add'))
			$this->error = "Вам не разрешено добавлять группы.";
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->fields['name'] == '')
			$this->error = 'Вы не ввели название';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
	    try {
            $grUtils = $this->authModule->getGroupUtils();
            $grUtils->addGroup($this->fields['name']);
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		Redirect('/admin/groups/');
		return "";
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
