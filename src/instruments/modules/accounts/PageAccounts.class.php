<?php
namespace modules\accounts;
\modules\Module::load("admin")->loadAdminPage();
use \modules\admin\Block;
use \modules\admin\Table;
use \modules\admin\Form;
use \modules\admin\DataTransfer;
use \modules\admin\AjaxDatalist;
use \modules\admin\ContentAjax;
class PageAccounts extends \modules\admin\AdminPage {

	private $form_del;
	private $form_del_input;
	private $form_edit;
	private $datalist;
	private $account_fields;
	private $form_fields_add;
	private $form_fields_del;
	private $form_fields_del_input;
	
	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		return "Управление аккаунтами";
	}
	/**
	 * Проверка страницы
	 */
	public function validate() {
		$accounts = \modules\Module::load("accounts");

		if (isset($_GET["logout"])) {
			$accounts->delAuthSession();
			Redirect("/");
		}

		$id = $accounts->getAuthSession();
		if ($id == NULL) return false;
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"admin.panel")) return false;
		return true;
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layout страницы
	 */
	public function prestruct(&$layout) {
		$accounts = \modules\Module::load("accounts");
		$util = $accounts->getAccountUtils();

		// Форма создания дополнительного поля
		$this->form_fields_add = new Form("fields_add");
		$this->form_fields_add->addInput("alias","Псевдоним")->setRequired();
		$this->form_fields_add->addInput("name","Имя")->setRequired();
		$this->form_fields_add->addSelect(
				"type",
				array(
					array(
						"value"    => "",
						"disabled" => "y",
						"selected" => "y",
						"#value"   => "Выберите тип"
					),
					array(
						"value"    => "text",
						"#value"   => "Текст"
					),
					array(
						"value"    => "varchar",
						"#value"   => "Строка"
					),
					array(
						"value"    => "int",
						"#value"   => "Целочисленное"
					),
					array(
						"value"    => "tinyint",
						"#value"   => "Малое число"
					),
					array(
						"value"    => "bigint",
						"#value"   => "Большое число"
					),
					array(
						"value"    => "float",
						"#value"   => "Дробное"
					),
					array(
						"value"    => "double",
						"#value"   => "Большое дробное"
					),
					array(
						"value"    => "decimal",
						"#value"   => "Фикс дробное"
					),
					array(
						"value"    => "date",
						"#value"   => "Дата"
					),
					array(
						"value"    => "time",
						"#value"   => "Время"
					),
					array(
						"value"    => "datetime",
						"#value"   => "Дата и время"
					),
					array(
						"value"    => "timestamp",
						"#value"   => "Timestamp"
					)
				),
				"Тип"
			)->setRequired();
		$this->form_fields_add->addInput("length","Длина","number")->setRequired()->setAttr("min","0")->setValue(0);
		$this->form_fields_add->addSubmit("Создать")->addClass("btn bc_blue");
		$this->form_fields_add->setEvent($this,"event_fields_add");
		if (($message = $this->form_fields_add->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма удаления дополнительного поля
		$this->form_fields_del = new Form("fields_del");
		$this->form_fields_del->setAttr("style","display:inline-block");
		$this->form_fields_del->addSubmit("Удалить")->addClass("btn bc_blue");
		$this->form_fields_del_input = $this->form_fields_del->addInput("id",null,"hidden");
		$this->form_fields_del->setEvent($this,"event_fields_del");
		if (($message = $this->form_fields_del->validate()) != false) {
			$layout->setWarning($message);
		}

		// Кастомные поля пользователей
		$this->account_fields = $util->listCustomFields();

		// Форма удаления привилегии
		$this->form_perms_del = new Form("perm_del");
		$this->form_perms_del->setAttr("style","display:inline-block");
		$this->form_perms_del->addSubmit("Удалить")->addClass("btn bc_blue");
		$this->form_perms_del_input = $this->form_perms_del->addInput("id",null,"hidden");
		$this->form_perms_del->setEvent($this,"event_perms_del");
		if (($message = $this->form_perms_del->validate()) != false) {
			$layout->setWarning($message);
		}

		// Список привилегий
		$this->perms_list = new ContentAjax("perms","span");
		$this->perms_list->setEvent($this,"event_perms_list");
		if ($this->perms_list->validate()) {
			$this->perms_list->event();
			die();
		}

		// Скрипт, который передаст ID с кнопки привилегий в форму добавления привилегии
		$this->dt_perms_add = new DataTransfer("dt_perms_add");

		// Форма удаления аккаунта
		$this->form_del = new Form("acc_del");
		$this->form_del->setAttr("style","display:inline-block");
		$this->form_del->addSubmit("Удалить")->addClass("btn bc_blue");
		$this->form_del_input = $this->form_del->addInput("id",null,"hidden");
		$this->form_del->setEvent($this,"event_del");
		if (($message = $this->form_del->validate()) != false) {
			$layout->setWarning($message);
		}

		// Скрипт, который передаст данные с кнопки в форму редактирования
		$this->dt_edit = new DataTransfer("acc_edit");

		// Доплняшка группы
		$this->datalist = new AjaxDatalist("groups");
		$this->datalist->setEvent($this,"event_autocomplete_group");
		$module = \modules\Module::load("accounts");
		$utils = $module->getGroupUtils();
		$groups = $utils->listGroups(1,10);
		foreach ($groups as $group) {
			$this->datalist->addChild(new \XMLnode("option",$group['name']." (".$group['id'].")"));
		}

		// Форма редактирования аккаунта
		$this->form_edit = new Form("acc_edit");
		$this->dt_edit->addField(
			$this->form_edit->addInput("id",null,"hidden")->setRequired(),
			"id"
		);
		$this->dt_edit->addField(
			$this->form_edit->addInput("login","Логин")->setRequired(),
			"login"
		);
		$field_group = $this->form_edit->addInput("group","Группа");
		$this->datalist->setField($field_group);
		$this->dt_edit->addField(
			$field_group,
			"group"
		);
		$this->form_edit->addInput("password","Новый пароль","password");
		$this->form_edit->addInput("password_","Повторить пароль","password");
		// Кастомные поля
		foreach ($this->account_fields as &$acc_field) {
			$acc_field["#input"] = $this->form_edit->addInput("field_".$acc_field["alias"],$acc_field["name"]);
			$this->dt_edit->addField(
				$acc_field["#input"],
				"field_".$acc_field["alias"]
			);
		}

		$this->form_edit->addSubmit("Сохранить")->addClass("btn bc_blue");
		$this->form_edit->setEvent($this,"event_edit");
		if (($message = $this->form_edit->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма создания аккаунта
		$this->form_add = new Form("acc_add");
		$this->form_add->addInput("login","Логин")->setRequired();
		$field = $this->form_add->addInput("group","Группа");
		$this->datalist->setField($field);
		$this->form_add->addInput("password","Пароль","password");
		$this->form_add->addInput("password_","Повторите пароль","password");
		foreach ($this->account_fields as $field) {
		$this->form_add->addInput("field_".$field["alias"],$field["name"]);
		}
		$this->datalist->setField($field);
		$this->form_add->addSubmit("Создать")->addClass("btn bc_blue"); 
		$this->form_add->setEvent($this,"event_add");
		if (($message = $this->form_add->validate()) != false) {
			$layout->setWarning($message);
		}

		if (is_array($data = $this->datalist->validate())) {
			echo json_encode($data);
			die();
		}

		// Форма создания привилегии
		$this->form_perms_add = new Form("perm_add");
		$this->dt_perms_add->addField(
			$this->form_perms_add->addInput("account",null,"hidden")->setRequired(),
			"id"
		);
		$this->form_perms_add->addInput("perm","Привилегия")->setRequired();
		$this->form_perms_add->addSubmit("Создать")->addClass("btn bc_blue");
		$this->form_perms_add->setEvent($this,"event_perms_add");
		if (($message = $this->form_perms_add->validate()) != false) {
			$layout->setWarning($message);
		}

	}
	/**
	 * Время для построения конткнта
	 * Переопределяемый метод
	 */
	public function sucture() {

		$accounts = \modules\Module::load("accounts");
		$util = $accounts->getAccountUtils();

		$block_list_pager = new \modules\admin\Pager("list",20);
		$block_list_pager->setPagesByCount($util->countAccounts());

		// Блок списка
		$block_list = new Block();
		$block_list->setTitle("Список аккаунтов");
		$block_list->addHeaderButton("Деавторизоваться")->setElement("a")->setAttr("href","?logout");
		$block_list->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		// Контент блока
			$table = new Table();
			$block_list->setContent($table);
			$line = $table->addLine();
			$line->addCell("Логин");
			$line->addCell("Группа");
			$line->addCell("Последняя авторизация");
			$line->addCell("Регистрация");
			$line->addCell("Действие");

			$users = $util->listAccounts(
				$block_list_pager->current(),
				$block_list_pager->perPage()
			);
			foreach ($users as $user) {
				$line = $table->addLine();
				$line->addCell($user["login"]);
				$line->addCell($user["group_name"]." (".$user["group"].")");
				if ($user["lastlogin"] == null) $user["lastlogin"] = "Не авторизовывался";
				$line->addCell($user["lastlogin"]);
				$line->addCell($user["registered"]);
				$btn_perms = (new \XMLnode("a","Привилегии"))->addClass("btn bc_blue event_btn_hide")->setAttr("hide-target","#block_perms");
				$this->perms_list->addButton(
					$btn_perms,
					array(
						"id" => $user["id"]
					)
				);
				$this->dt_perms_add->addButton(
					$btn_perms,
					array(
						"id" => $user["id"]
					)
				);
				$btn_edit = (new \XMLnode("a","Изменить"))->addClass("btn bc_blue event_btn_hide")->setAttr("hide-target","#block_edit");

				$fields_edit = array(
					"id"    => $user["id"],
					"login" => $user["login"],
					"group" => $user["group_name"]." (".$user["group"].")"
				);
				foreach ($this->account_fields as $field) {
					$fields_edit["field_".$field["alias"]] = $user["field_".$field["alias"]];
				}

				$this->dt_edit->addButton($btn_edit,$fields_edit);
				$this->form_del_input->setValue($user["id"]);
				$line->addCell($this->form_del . $btn_edit . $btn_perms);
			}
			$line = $table->addLine();
			$line->addCell($block_list_pager);
		// =================

		// Блок списка кастомных полей
		$block_list_fields = new Block();
		$block_list_fields->setTitle("Список дополнительных полей");
		$block_list_fields->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_fields_add");
		// Контент блока
			$table = new Table();
			$block_list_fields->setContent($table);
			$line = $table->addLine();
			$line->addCell("Псевдоним");
			$line->addCell("Наименование");
			$line->addCell("Тип");
			$line->addCell("Длина");
			$line->addCell("Действие");
			foreach ($this->account_fields as $field) {
				$line = $table->addLine();
				$line->addCell("field_".$field["alias"]);
				$line->addCell($field["name"]);
				$line->addCell($field["type"]);
				$line->addCell((string)$field["length"]);
				$this->form_fields_del_input->setValue($field["id"]);
				$line->addCell((string)$this->form_fields_del);
			}
		// ============================

		// Блок добавления дополнительного поля
		$block_fields_add = new Block();
		$block_fields_add->setTitle("Добавление дополнительного поля");
		$block_fields_add->getAttr()->addClass("hidden")->set("id","block_fields_add");
		$block_fields_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_fields_add");
		$block_fields_add->setContent($this->form_fields_add->toTableString());
		// ==========================

		// Блок редактирования
		$block_edit = new Block();
		$block_edit->setTitle("Редактировать аккаунт");
		$block_edit->getAttr()->addClass("hidden")->set("id","block_edit");
		$block_edit->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_edit");
		$block_edit->setContent($this->form_edit->toTableString());
		// =========================

		// Создать новый аккаунт
		$block_add = new Block();
		$block_add->setTitle("Создать новый аккаунт");
		$block_add->getAttr()->addClass("hidden")->set("id","block_add");
		$block_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		$block_add->setContent($this->form_add->toTableString());
		// =========================

		// Блок преимуществ
		$block_perms = new Block();
		$block_perms->setTitle("Список привилегий");
		$block_perms->getAttr()->addClass("hidden")->set("id","block_perms");
		$block_perms->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_perms_add");
		$block_perms->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_perms");
		$block_perms->setContent($this->perms_list);
		// =========================

		// Блок добавления привилегии
		$block_perms_add = new Block();
		$block_perms_add->setTitle("Добавление привилегии");
		$block_perms_add->getAttr()->addClass("hidden")->set("id","block_perms_add");
		$block_perms_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_perms_add");
		$block_perms_add->setContent($this->form_perms_add->toTableString());
		// ==========================

		echo $block_list
		    .$block_list_fields
		    .$this->dt_edit
		    .$this->dt_perms_add
		    .$block_fields_add
		    .$block_edit
		    .$block_add
		    .$block_perms
		    .$block_perms_add
		    .$this->datalist;
	}
	/**
	 * Обработка формы удаления аккаунта
	 */
	public function event_del(&$form,&$fields) {
		if (!isset($fields["id"])) return "ID не найдено";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.edit")) return "Вам не разрешено удалять аккаунты";

		$module = \modules\Module::load("accounts");
		$util = $module->getAccountUtils();
		try {
			$util->delAccount((int)$fields["id"]);
			return "Аккаунт успешно удален.";
		} catch (\Exception $e) {
			return "Ошибка при удалении аккаунта: " . $e->getMessage();
		}
	}
	/**
	 * Обработка формы редактирования аккаунта
	 */
	public function event_edit(&$form,&$fields) {
		if (!isset($fields["id"]) || !isset($fields["login"]) || !isset($fields["group"]))
			return "Один или несколько параметров не указаны";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.edit")) return "Вам не разрешено редактировать аккаунты";

		if (!isset($fields["password"]) || $fields["password"] == "") $fields["password"] = null;

		if (
				(isset($fields["password"]) && !isset($fields["password_"]))
				||
				(isset($fields["password"]) && $fields["password"] != $fields["password_"])
			) {
			return "Пароли не совпадают";
		}

		$module = \modules\Module::load("accounts");
		$util = $module->getAccountUtils();
		$GroupUtil = $module->getGroupUtils();

		$custom_fields = array();
		foreach ($this->account_fields as $field) {
			$field_alias = "field_".$field["alias"];
			if (isset($fields[$field_alias]))
				$custom_fields[$field_alias] = &$fields[$field_alias];
		}

		try {
			$ind_start = strripos($fields["group"]," (");
			if ($ind_start) {
				$ind_end = strpos($fields["group"],")",$ind_start);
				if ($ind_end) {
					$group = (int)substr($fields["group"],$ind_start+2,$ind_end-$ind_start-1);
					$util->setAccount((int)$fields["id"],$fields["login"],$fields["password"],$group,$custom_fields);
					return "Аккаунт успешно отредактирован.";
				}
			}
			return "Не верная группа.";
		} catch (\Exception $e) {
			return "Ошибка: " . $e->getMessage();
		}
	}
	/**
	 * Обработка формы добавления аккаунта
	 */
	public function event_add(&$form,&$fields) {
		if (!isset($fields["login"]) || !isset($fields["group"]))
			return "Один или несколько параметров не указаны";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.edit")) return "Вам не разрешено добавлять аккаунты";

		if (!isset($fields["password"]) || $fields["password"] == "") $fields["password"] = null;

		if (
				(isset($fields["password"]) && !isset($fields["password_"]))
				||
				(isset($fields["password"]) && $fields["password"] != $fields["password_"])
			) {
			return "Пароли не совпадают";
		}

		$module = \modules\Module::load("accounts");
		$util = $module->getAccountUtils();
		$GroupUtil = $module->getGroupUtils();

		$custom_fields = array();
		foreach ($this->account_fields as $field) {
			$field_alias = "field_".$field["alias"];
			if (isset($fields[$field_alias]))
				$custom_fields[$field_alias] = $fields[$field_alias];
		}

		try {
			$ind_start = strripos($fields["group"]," (");
			if ($ind_start) {
				$ind_end = strpos($fields["group"],")",$ind_start);
				if ($ind_end) {
					$group = (int)substr($fields["group"],$ind_start+2,$ind_end-$ind_start-1);
					$util->addAccount($fields["login"],$fields["password"],$group,$custom_fields);
					return "Аккаунт успешно добавлен.";
				}
			}
			return "Не верная группа.";
		} catch (\Exception $e) {
			$db = \database::getInstance();
			var_dump($db->getLastQuery());
			return "Ошибка: " . $e->getMessage();
		}
	}
	/**
	 * Дополнить имя группы
	 */
	public function event_autocomplete_group(&$value) {
		$module = \modules\Module::load("accounts");
		$utils = $module->getGroupUtils();

		$ind_start = strripos($value," (");
		if ($ind_start) {
			$ind_end = strpos($value,")",$ind_start);
			if ($ind_end) {
				$i = (int)substr($value,$ind_start+2,$ind_end-$ind_start-1);
				if ($i > 0)
					$value = substr($value,0,$ind_start);
			}
		}

		$groups = $utils->autocompleteGroupName($value);
		$arr = array();
		foreach ($groups as $group) {
			$arr[] = $group['name']." (".$group['id'].")";
		}
		return $arr;
	}
	/**
	 * Контент блока ивилегий
	 */
	public function event_perms_list(&$node,&$fields) {
		if (!isset($fields["id"])) {
			echo "Не указан id";
			return null;
		}
		$table = new Table();
		$line = $table->addLine();
		$line->addCell("Привилегия");
		$line->addCell("Действие");
		$module = \modules\Module::load("accounts");
		$util = $module->getAccountUtils();
		$list = $util->listPermission((int)$fields["id"]);
		foreach ($list as $perm) {
			$this->form_perms_del_input->setValue($perm["id"]);
			$line = $table->addLine();
			$line->addCell($perm["perm"]);
			$line->addCell($this->form_perms_del);
		}
		echo $table;
	}
	/**
	 * Обработка формы добавления привилегии
	 */
	public function event_perms_add(&$form,$fields) {
		if (!isset($fields['account']) || !isset($fields['perm']))
			return "Один или несколько параметров не заданы";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$util = $accounts->getAccountUtils();
		if (!$util->isSetPermission($id,"accounts.permissions.edit")) return "Вам не разрешено добавлять привилегии";

		try {
			$util->addPermission((int)$fields["account"],$fields["perm"]);
			return "Привилегия добавлена";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}

	}
	/**
	 * Удаление привилегии
	 */
	public function event_perms_del(&$form,$fields) {
		if (!isset($fields["id"]))
			return "Не указан id привилегии";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$util = $accounts->getAccountUtils();
		if (!$util->isSetPermission($id,"accounts.permissions.edit")) return "Вам не разрешено удалять привилегии";

		try {
			$util->delPermission((int)$fields["id"]);
			return "Привилегия удалена";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}
	/**
	 * Добавление дополнительного поля
	 */
	public function event_fields_add(&$form,$fields) {
		if (!isset($fields["alias"]) || !isset($fields["name"]) || !isset($fields["type"]) || !isset($fields["length"]))
			return "Один или несколько параметров не указаны";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$util = $accounts->getAccountUtils();
		if (!$util->isSetPermission($id,"accounts.fields.edit")) return "Вам не разрешено добавлять дополнительные поля";

		try {
			$util->addCustomField($fields["alias"],$fields["name"],$fields["type"],(int)$fields["length"]);
			return "Дополнительное поле добавлено";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}
	/**
	 * Удаление дополнительных полей
	 */
	public function event_fields_del(&$form,$fields) {
		if (!isset($fields["id"]))
			return "ID не указан";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$util = $accounts->getAccountUtils();
		if (!$util->isSetPermission($id,"accounts.fields.edit")) return "Вам не разрешено удалять дополнительные поля";

		try {
			$util->delCustomField((int)$fields["id"]);
			return "Дополнительное поле удалено";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}
}