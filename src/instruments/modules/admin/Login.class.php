<?php
namespace modules\admin;
\modules\Module::load("admin")->loadAdminPage();
use \modules\admin\Block;
use \modules\admin\Table;
use \modules\admin\Form;
class Login extends \modules\admin\AdminPage {

	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		return "Авторизация";
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layaout страницы
	 */
	public function prestruct(&$layout) {

		$this->auth = false;
		$accounts = \modules\Module::load("accounts");

		if (isset($_GET["logout"])) {
			$accounts->delAuthSession();
			Redirect("/");
		}

		$id = $accounts->getAuthSession();
		if ($id != NULL) {
			try {
				$utils = $accounts->getAccountUtils();
				$this->acc = $utils->getAccount($id);
				$this->auth = true;
			} catch (\Exception $e) {}
		}

		$this->form_auth = new Form("auth");
		$this->form_auth->addInput("login","Логин")->setRequired();
		$this->form_auth->addInput("password","Пароль","password")->setRequired();
		$this->form_auth->addSubmit("Авторизоваться")->addClass("btn bc_blue");
		$this->form_auth->setEvent($this,"event_auth");
		if (($message = $this->form_auth->validate()) != false) {
			$layout->setWarning($message);
		}
	}
	/**
	 * Время для построения конткнта 	
	 * Переопределяемый метод
	 */
	public function sucture() {
		$block = new Block();
		$block->setTitle("Авторизация");
		if ($this->auth) {
			$div = new \XMLnode("div");
			$div->addChild(new \XMLnode("br"));
			$div->addChild("Вы уже авторизованы как ".$this->acc["login"]);
			$div->addChild(new \XMLnode("br"));
			$div->addChild(new \XMLnode("br"));
			$div->addChild( (new \XMLnode("a","Деавторизоваться"))->addClass("btn bc_blue")->setAttr("href","?logout") );
			$block->setContent($div);
		} else
			$block->setContent($this->form_auth->toTableString());
		

		echo $block;
	}
	/**
	 * Авторизация
	 */
	public function event_auth(&$form,&$fields) {
		try {
			$accounts = \modules\Module::load("accounts");
		} catch (\Exception $e) {
			return "Для авторизации необходим модуль accounts";
		}

		if (isset($_SESSION["wrong_password"]) && $_SESSION["wrong_password"] > 5)
			return "Превышено количество попыток входа. Ждите окончания сессии.";
		
		if (!isset($_POST["login"]) || !isset($_POST["password"])) {
			return "Логин или пароль не указан";
		}
		
		$util = $accounts->getAccountUtils();
		
		try {
			$user = $util->getAccountByLogin($_POST["login"],$_POST["password"]);
			$accounts->setAuthSession($user["id"]);
			unset($_SESSION["wrong_password"]);
			Redirect();
		} catch (\modules\accounts\AccountNotFoundException $e) {
			if (!isset($_SESSION["wrong_password"])) $_SESSION["wrong_password"] = 1;
			else $_SESSION["wrong_password"]++;
			return $e->getMessage();
		}
	}
}