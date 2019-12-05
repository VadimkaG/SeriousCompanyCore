<?
namespace events;
class LoginByGet extends Event {

	public function init() {
		loadInstuments("auth");
		$this->error = "";
		$this->fields['session'] = $this->getValue('s');
	}
	
	public function validate() {
	
		if (isAuth())
			logout();
		
		if ($this->fields['session'] == "")
			$this->error = 'Ошибка полей';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
		$this->error = loginBySession($this->fields['session']);
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
