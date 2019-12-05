<?
namespace events\Admin;
class EditGroup extends \events\Event {

	public function init() {
	    $this->authModule = getModule('accounts');
		loadInstuments('groups');
		$this->error = false;
		$this->fields['id'] = (int)$this->getValue('id');
		$this->fields['name'] = $this->getValue('name');
	}
	
	public function validate() {
		
		if (!checkPerm('admin.group.edit'))
			$this->error = "Вам не разрешено редактировать группы.";
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->fields['id'] == '')
			$this->error = 'Пустое id';
		
		if ($this->fields['name'] == '')
			$this->error = 'Пустое название';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
		try {
		    $grUtils = $this->authModule->getGroupUtils();
            $grUtils->setGroup((int)$this->fields['id'],$this->fields['name']);
        } catch (\modules\accounts\AuthFailedException $e) {
            $this->error = $e->getMessage();
        }
		if ($this->error)
			return false;
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
