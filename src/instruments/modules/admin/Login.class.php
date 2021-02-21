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
		$event = \Event::call("isAuth");
		if (isset($event[0]) && $event[0]) {
			$event = \Event::call("getUserCurrent");
			if (isset($event[0]) && is_array($event[0])) {
				$this->acc = $event[0];
				$this->auth = true;
			}
		}

		if ($this->auth && isset($_GET["logout"])) {
			\Event::call("logout");
			Redirect("/");
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

		// Проверяем количество попыток входа
		if (isset($_SESSION["wrong_password"]) && $_SESSION["wrong_password"] > 5)
			return "Превышено количество попыток входа. Ждите окончания сессии.";

		// Проверяем существования параметров
		if (!isset($_POST["login"]) || !isset($_POST["password"])) {
			return "Логин или пароль не указан";
		}

		// Проверяем данные авторизации
		$event = \Event::call("login",[ "login" => $_POST["login"], "password" => $_POST["password"] ]);

		// Если авторизация успешна
		if (isset($event[0]) && $event[0]) {
			unset($_SESSION["wrong_password"]);
			Redirect();

		// Если данные не прошли проверку
		} else {
			if (!isset($_SESSION["wrong_password"])) $_SESSION["wrong_password"] = 1;
			else $_SESSION["wrong_password"]++;
			return "Не верный логин или пароль";
		}
	}
}