<?php
namespace modules\admin;
use \XMLnode;
\modules\Module::load("admin")->loadAdminPage();
class Main extends AdminPage {
	
	private $PATH_ARRAY = array();
	private $version;
	private $core_version;
	
	private $form_menus_add;
	private $form_menus_edit;
	private $form_menus_del;
	private $form_menus_del_input;
	private $dt_edit;

	private $locale = null;
	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		if ($this->locale == null) {
			if (file_exists(__dir__."/template/locales/".LOCALE.".json"))
				$this->locale = json_decode(file_get_contents(__dir__."/template/locales/".LOCALE.".json"),true);
			else $this->locale = json_decode(file_get_contents(__dir__."/template/locales/ru.json"),true);
		}
		return $this->locale["PAGE_ADMIN"];
	}
	/**
	 * Проверка страницы
	 * Переопределяемый метод
	 * @param $path - Путь страницы
	 * @return boolean
	 */
	public function validate() {
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL)
			throw new \PathNotValidatedException("Вы не авторизованы",\Path::getPath("admin_login"));
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"admin.panel"))
			throw new \PathNotValidatedException("Вы не авторизованы",\Path::getPath("admin_login"));

		if (file_exists(__dir__."/template/locales/".LOCALE.".json"))
			$this->locale = json_decode(file_get_contents(__dir__."/template/locales/".LOCALE.".json"),true);
		else $this->locale = json_decode(file_get_contents(__dir__."/template/locales/ru.json"),true);
		return true;
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layaout страницы
	 */
	public function prestruct(&$layout) {
		// Форма добавления ссылки в меню
		$this->form_menus_add = new Form("menu_add");
		$this->form_menus_add->addInput("name",$this->locale["NAME"])->setRequired();
		$this->form_menus_add->addInput("link",$this->locale["LINK"]);
		$this->form_menus_add->addInput("weight",$this->locale["WEIGHT"],"number")
			->setAttr("min","0")
			->setValue("0")
			->setRequired();
		$this->form_menus_add->addSubmit($this->locale["ADD"])->addClass("btn bc_blue");
		$this->form_menus_add->setEvent($this,"menu_add_event");
		if (($message = $this->form_menus_add->validate()) != false) {
			$layout->setWarning($message);
		}

		// Скрипт, который передаст данные с кнопки в форму редактирования
		$this->dt_edit = new DataTransfer("menu_edit");
		
		// Форма редактирования ссылки в меню
		$this->form_menus_edit = new Form("menu_edit");
		$this->dt_edit->addField(
			$this->form_menus_edit->addInput("id",null,"hidden")->setRequired(),
			"id"
		);
		$this->dt_edit->addField(
			$this->form_menus_edit->addInput("name",$this->locale["NAME"])->setRequired(),
			"name"
		);
		$this->dt_edit->addField(
			$this->form_menus_edit->addInput("link",$this->locale["LINK"]),
			"link"
		);
		$this->dt_edit->addField(
			$this->form_menus_edit->addInput("weight",$this->locale["WEIGHT"],"number")
				->setAttr("min","0")
				->setRequired(),
			"weight"
		);
		$this->form_menus_edit->addSubmit($this->locale["SAVE"])->addClass("btn bc_blue");
		$this->form_menus_edit->setEvent($this,"menu_edit_event");
		if (($message = $this->form_menus_edit->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма удаления ссылка из меню
		$this->form_menus_del = new Form("menu_del");
		$this->form_menus_del->setAttr("style","display:inline-block");
		$this->form_menus_del->addSubmit($this->locale["DELETE"])->addClass("btn bc_blue");
		$this->form_menus_del_input = $this->form_menus_del->addInput("id",null,"hidden");
		$this->form_menus_del->setEvent($this,"menu_del_event");
		if (($message = $this->form_menus_del->validate()) != false) {
			$layout->setWarning($message);
		}
	}
	/**
	 * Время для построения конткнта
	 * Переопределяемый метод
	 */
	public function sucture() {
		$block = new Block();
		$block->setTitle($this->locale["INFO"]);
		$block->setContent($this->content());
		echo $block;
		
		$block_menu = new Block();
		$block_menu->setTitle($this->locale["MENU_LINKS"]);
		$block_menu->getAttr()->addClass("collapsed")->set("id","block_menu");
		$block_menu->addHeaderButton($this->locale["ADD"])->getAttr()
			->addClass("event_btn_hide")
			->set("hide-target","#block_menu_add");
		$block_menu->addHeaderButton($this->locale["EXPAND"])->getAttr()
			->addClass("event_btn_collapse")
			->set("collapce-target","#block_menu");
		$block_menu->setContent($this->menu());
		echo $block_menu;
		echo $this->dt_edit;
		
		$block_menu_add = new Block();
		$block_menu_add->setTitle($this->locale["LINK_ADD"]);
		$block_menu_add->getAttr()->addClass("hidden")->set("id","block_menu_add");
		$block_menu_add->addHeaderButton($this->locale["CANCEL"])->getAttr()
			->addClass("event_btn_hide")
			->set("hide-target","#block_menu_add");
		$block_menu_add->setContent($this->form_menus_add->toTableString());
		echo $block_menu_add;

		$block_menu_edit = new Block();
		$block_menu_edit->setTitle($this->locale["LINK_EDIT"]);
		$block_menu_edit->getAttr()->addClass("hidden")->set("id","block_menu_edit");
		$block_menu_edit->addHeaderButton($this->locale["CANCEL"])->getAttr()
			->addClass("event_btn_hide")
			->set("hide-target","#block_menu_edit");
		$block_menu_edit->setContent($this->form_menus_edit->toTableString());
		echo $block_menu_edit;
	}
	/**
	 * Конткнт главного блока
	 */
	public function content() {
		$content = new XMLnode("div");
		$content->addChild(new XMLnode("p",$this->locale["MSG1"]));
		$content->addChild(new XMLnode("p",$this->locale["MSG2"]));
		$content->addChild(new XMLnode("br"));
		$content->addChild(new XMLnode("p",$this->locale["MSG3"].": ". \modules\Module::load("admin")->getVersion()));
		$content->addChild(new XMLnode("p",$this->locale["MSG4"].": ". core_version));
		return $content;
	}
	/**
	 * Блок меню
	 */
	public function menu() {
		$table = new Table();
		$module = \modules\Module::load("admin");
		$menu = $module->listMenu();
		$line = $table->addLine();
		$line->addCell($this->locale["NAME"]);
		$line->addCell($this->locale["LINK"]);
		$line->addCell($this->locale["WEIGHT"]);
		$line->addCell($this->locale["ACTION"]);
		foreach ($menu as $item) {
			$this->form_menus_del_input->setValue($item["id"]);
			$line = $table->addLine();
			$line->addCell($item["name"]);
			$line->addCell((new XMLnode("a",$item["link"]))->setAttr("href",$item["link"]));
			$line->addCell($item["weight"]);
			$btn_edit = (new XMLnode("a",$this->locale["EDIT"]))->addClass("btn bc_blue event_btn_hide")->setAttr("hide-target","#block_menu_edit");
			$this->dt_edit->addButton(
				$btn_edit,
				array(
					"id" => $item["id"],
					"name" => $item["name"],
					"link" => $item["link"],
					"weight" => $item["weight"]
				)
			);
			$line->addCell($this->form_menus_del . $btn_edit);
		}
		return $table;
	}
	/**
	 * Обработка формы добавления ссылки в меню
	 */
	public function menu_add_event(&$form,&$fields) {
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"admin.menu")) return "Вам не разрешено добавлять ссылки в меню";

		if (!isset($fields["name"]) || !isset($fields["link"]) || !isset($fields["weight"]))
			return "Один или несколько параметров не существует";

		$fields["weight"] = (int)$fields["weight"];

		if (!is_string($fields["name"]) || $fields["name"] == "")
			return "Имя не должэно быть пустым";

		if (($fields["weight"]) < 0)
			return "Вес не может быть меньше 0";

		$module = \modules\Module::load("admin");
		try {
			$module->addMenuLink($fields["name"],$fields["link"],$fields["weight"]);
			return "Ссылка успешно создана.";
		} catch (\Exception $e) {
			return "Ошибка при создании ссылки: " . $e->getMessage();
		}
	}
	/**
	 * Обработка формы удаления ссылки из меню
	 */
	public function menu_del_event(&$form,&$fields) {
		if (!isset($fields["id"])) return "ID не найдено";
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"admin.menu")) return "Вам не разрешено удалять ссылки из меню";

		$module = \modules\Module::load("admin");
		try {
			$module->delMenuLink((int)$fields["id"]);
			return "Ссылка успешно удалена.";
		} catch (\Exception $e) {
			return "Ошибка при удалении ссылки: " . $e->getMessage();
		}
	}
	/**
	 * Блок добавление ссылки в меню
	 */
	public function menu_edit_event(&$form,&$fields) {
		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"admin.menu")) return "Вам не разрешено изменять ссылки в меню";

		if (!isset($fields["id"]) || !isset($fields["name"]) || !isset($fields["link"]) || !isset($fields["weight"]))
			return "Один или несколько параметров не существует";

		$fields["id"] = (int)$fields["id"];
		$fields["weight"] = (int)$fields["weight"];

		if (!is_string($fields["name"]) || $fields["name"] == "")
			return "Имя не должэно быть пустым";

		if (($fields["weight"]) < 0)
			return "Вес не может быть меньше 0";

		$module = \modules\Module::load("admin");
		try {
			$module->editMenuLink($fields["id"],$fields["name"],$fields["link"],$fields["weight"]);
			return "Ссылка успешно отредактирована.";
		} catch (\Exception $e) {
			return "Ошибка при редактировании ссылки: " . $e->getMessage();
		}
	}
}
