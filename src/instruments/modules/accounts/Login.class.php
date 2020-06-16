<?
namespace modules\accounts;
$template = \modules\Module::load("templates");
$template->setCustomTemplate("accounts","/". instruments ."modules/accounts/template/");
class Login extends \modules\templates\Handler {
	
	private $MSG_STATUS = null;
	private $MSG_MESSAGE = "";
	
	// Путь к шаблону страницы
	const TEMPLATE = "accounts.login";
	
	/**
	 * Получить заголовок страницы
	 * @return string - Заголовок страницы
	 */
	public function getTitle() {
		return "Авторизация";
	}
	/**
	 * Обработка страницы
	 */
	public function response() {
		if (isset($_GET['logout'])) {
			$accounts = \modules\Module::load("accounts");
			if ($accounts->getAuthSession() != null) {
				$accounts->delAuthSession();
				$this->setMessage("Вы деавторизовались");
			}
		}
		if (isset($_POST['event'])) {
			switch ($_POST['event']) {
			
			case "login":
				$this->login();
				break;
			
			case "register":
				$this->register();
				break;
			
			}
		}
		
		$this->proc(array(
			"version"      => $this->template_config['version'],
			"core_version" => core_version
		));
	}
	public function Block_warning($func,&$c,&$data){
		if ($this->MSG_STATUS != null)
			$c->$func(array(
				"status"  => $this->MSG_STATUS,
				"message" => $this->MSG_MESSAGE
			));
	}
	public function setMessage($message,$status = "er") {
		$this->MSG_STATUS = $status;
		$this->MSG_MESSAGE = $message;
	}
	public function login() {
		$accounts = \modules\Module::load("accounts");
		
		if (!isset($_POST["login"]) || !isset($_POST["password"])) {
			$this->setMessage("Логин или пароль не указан");
			return null;
		}
		
		$util = $accounts->getAccountUtils();
		
		try {
			$user = $util->getAccountByLogin($_POST["login"],$_POST["password"]);
			$this->setMessage("Успешная авторизация: " . $user["login"],"ok");
			$accounts->setAuthSession($user["id"]);
		} catch (AccountNotFoundException $e) {
			$this->setMessage($e->getMessage());
		}
		
	}
	public function register() {
		$accounts = \modules\Module::load("accounts");
		
		if (!isset($_POST['login']) || !isset($_POST['password']) || !isset($_POST['password_confirm'])) {
			$this->setMessage("Логин или пароль не указан");
			return null;
		}
		
		if ($_POST['password'] != $_POST['password_confirm']) {
			$this->setMessage("Пароли не совпадают");
			return null;
		}
		
		$util = $accounts->getAccountUtils();
		
		try {
			$util->addAccount($_POST['login'],$_POST['password']);
			$this->setMessage("Успешная регистрация","ok");
		} catch (AccountExistsException $e) {
			$this->setMessage($e->getMessage());
		}
	}
}
