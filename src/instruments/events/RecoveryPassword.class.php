<?
namespace events;
class RecoveryPassword extends Event {

	public function init() {
		global $config;
		loadInstuments(array('auth','PHPMailer/init'));
		$this->error = "";
		$this->fields['email'] = $this->getValue('email');
		
		$out = $config['database']->select(
			'accounts',
			'Count(*),'.$config['database']->getNameTable('accounts_login').','.$config['database']->getNameTable('accounts_password'),
			array($config['database']->getNameTable('accounts_email')."='".$this->fields['email']."'")
		);
		$this->error = $config['database']->getError();
		if (!$this->error) {
			$row = $out->fetch_assoc();
			$this->fields['Count'] = $row['Count(*)'];
			$this->fields['login'] = $row[$config['database']->getNameTable('accounts_login')];
			$this->fields['password'] = $row[$config['database']->getNameTable('accounts_password')];
		}
	}
	
	public function validate() {
		if ($this->error) return false;
		
		if ($this->fields['email'] == '')
			$this->error = 'Email пустой';
		
		else if ($this->fields['Count'] <= 0)
			$this->error = 'Этот email не привязан ни к одному аккаунту';
		
		if ($this->error)
			return false;
		else
			return true;
	}
	
	public function action() {
		$this->error = sendMail(
			"Восстановление доступа к аккаунту",
			$this->fields['login']." запросил восстановление доступа к аккаунту.<br>"
				. "Перейдя по следующей ссылке, вы автоматически авторизуетесь на сайте:<br>"
				. "http://".$_SERVER["HTTP_HOST"]."/?action=loginByGet&s=".genSession($this->fields['login'],$this->fields['password'])
				. "<br><br>Изменить авторизационные данные вы можете в своем профиле.",
			$this->fields['email']
		);
		
		if ($this->error) return false;
		return true;
	}
	
	public function success() {
		return array('warning'=>"На почту ".$this->fields['email']." отправлено письмо с дальнейшими указаниями.");
	}
	
	public function failed() {
		return array('warning'=>$this->error);
	}
}
