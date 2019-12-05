<?
namespace events\Admin;
class ChangeSettings extends \events\Event {

	public function init() {
		
		loadInstuments("settings");
		$this->error = false;
		
		$this->fields['Title'] = $this->getValue('Title');
		$this->fields['About'] = $this->getValue('About');
		$this->fields['KeyWords'] = $this->getValue('KeyWords');
		$this->fields['ClosedReason'] = $this->getValue('ClosedReason');
		$this->fields['template'] = $this->getValue('template');
		
		if ($this->getValue('SiteClosed') != "") $this->fields['SiteClosed'] = "True";
		else $this->fields['SiteClosed'] = "False";
		if ($this->getValue('authEnabled') != "") $this->fields['authEnabled'] = "True";
		else $this->fields['authEnabled'] = "False";
		// ==============
	}
	
	public function validate() {
		
		if (!checkPerm('admin.settings.edit'))
			$this->error = "Вам запрещено редактировать настройки сайта";
		
		if ($this->error) return false;
		else return true;
	}
	
	public function action() {
		foreach ($this->fields as $key => $value) {
			saveSetting($key,$value);
		}
		return true;
	}
	
	public function success() {
		Redirect('/admin/settings/');
		return "";
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
