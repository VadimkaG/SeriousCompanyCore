<?
namespace events\Admin;
class DeleteGroup extends \events\Event {

	public function init() {
	    $this->authModule = getModule('accounts');
		$this->error = false;
		$this->fields['delete'] = $this->getValue('delete');
	}
	
	public function validate() {
		
		if (!checkPerm('admin.group.delete'))
			$this->error = "Вам не разрешено удалять группы.";
		
		if ($this->authModule == null)
            $this->error = 'Не удалось загрузить модуль авторизации';
		
		if ($this->fields['delete'] == '' || count($this->fields['delete']) <= 0)
			$this->error = 'Вы не выбрали группы, которые хотите удалить';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
        $grUtils = $this->authModule->getGroupUtils();
		    foreach ($this->fields['delete'] as $group) {
                try {
                    $grUtils->delGroup((int)$group);
                } catch (\modules\accounts\AuthFailedException $e) {
                    $this->error = $e->getMessage();
                }
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
