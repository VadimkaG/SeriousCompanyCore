<?php
namespace modules\accounts;
\modules\Module::load("admin")->loadAdminPage();
use \modules\admin\Block;
use \modules\admin\Table;
use \modules\admin\Form;
use \modules\admin\DataTransfer;
use \modules\admin\ContentAjax;
use \modules\admin\Pager;
class PageGroups extends \modules\admin\AdminPage {

	private $form_del;
	private $form_del_input;
	private $form_edit;
	private $perms_list;
	private $form_perms_add;
	private $form_perms_del;
	private $form_perms_del_input;
	
	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		return "Управление группами аккаунтов";
	}
	/**
	 * Проверка страницы
	 */
	public function validate() {
		$accounts = \modules\Module::load("accounts");
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

		// Форма создания группы
		$this->form_add = new Form("gr_add");
		$this->form_add->addInput("name","Название")->setRequired();
		$this->form_add->addSubmit("Создать")->addClass("btn bc_blue");
		$this->form_add->setEvent($this,"event_add");
		if (($message = $this->form_add->validate()) != false) {
			$layout->setWarning($message);
		}

		// Скрипт, который передаст данные с кнопки в форму редактирования
		$this->dt_edit = new DataTransfer("gr_edit");

		// Скрипт, который передаст ID с кнопки привилегий в форму добавления привилегии
		$this->dt_perms_add = new DataTransfer("dt_perms_add");

		// Форма редактирования аккаунта
		$this->form_edit = new Form("gr_edit");
		$this->dt_edit->addField(
			$this->form_edit->addInput("id",null,"hidden")->setRequired(),
			"id"
		);
		$this->dt_edit->addField(
			$this->form_edit->addInput("name","Название")->setRequired(),
			"name"
		);
		$this->form_edit->addSubmit("Сохранить")->addClass("btn bc_blue");
		$this->form_edit->setEvent($this,"event_edit");
		if (($message = $this->form_edit->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма удаления аккаунта
		$this->form_del = new Form("acc_del");
		$this->form_del->setAttr("style","display:inline-block");
		$this->form_del->addSubmit("Удалить")->addClass("btn bc_blue");
		$this->form_del_input = $this->form_del->addInput("id",null,"hidden");
		$this->form_del->setEvent($this,"event_del");
		if (($message = $this->form_del->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма создания привилегии для группы
		$this->form_perms_add = new Form("perm_add");
		$this->dt_perms_add->addField(
			$this->form_perms_add->addInput("group",null,"hidden")->setRequired(),
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

		$module = \modules\Module::load("accounts");
		$utils = $module->getGroupUtils();

		$block_list_pager = new Pager("list",20);
		$block_list_pager->setPagesByCount($utils->countGroups());

		$block_list = new Block();
		$block_list->setTitle("Список групп");
		$block_list->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		// Контент блока
			$table = new Table();
			$groups = $utils->listGroups(
				$block_list_pager->current(),
				$block_list_pager->perPage()
			);
			$line = $table->addLine();
			$line->addCell("Идентификатор");
			$line->addCell("Название");
			$line->addCell("Действие");
			foreach ($groups as $group) {
				$line = $table->addLine();
				$line->addCell($group["id"]);
				$line->addCell($group["name"]);
				$btn_perms = (new \XMLnode("a","Привилегии"))->addClass("btn bc_blue event_btn_hide")->setAttr("hide-target","#block_perms");
				$this->dt_perms_add->addButton(
					$btn_perms,
					array(
						"id" => $group["id"]
					)
				);
				$this->perms_list->addButton(
					$btn_perms,
					array(
						"id" => $group["id"]
					)
				);
				$btn_edit = (new \XMLnode("a","Изменить"))->addClass("btn bc_blue event_btn_hide")->setAttr("hide-target","#block_edit");
				$this->dt_edit->addButton(
					$btn_edit,
					array(
						"id" => $group["id"],
						"name" => $group["name"]
					)
				);
				if ($group["id"] != 1 && $group["id"] != 2) {
					$this->form_del_input->setValue($group["id"]);
					$line->addCell($btn_edit . $btn_perms . $this->form_del);
				} else if ($group["id"] != 1)
					$line->addCell($btn_edit . $btn_perms);
				else
					$line->addCell($btn_edit);
			}
			$line = $table->addLine();
			$line->addCell($block_list_pager);
			$block_list->setContent($table);
		// =============

		// Блок редактирования
		$block_edit = new Block();
		$block_edit->setTitle("Редактировать группу");
		$block_edit->getAttr()->addClass("hidden")->set("id","block_edit");
		$block_edit->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_edit");
		$block_edit->setContent($this->form_edit->toTableString());

		$block_add = new Block();
		$block_add->setTitle("Добавить группу");
		$block_add->getAttr()->addClass("hidden")->set("id","block_add");
		$block_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		$block_add->setContent($this->form_add->toTableString());
		// =============================

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
		$block_perms_add->setTitle("Добалвение привилегии");
		$block_perms_add->getAttr()->addClass("hidden")->set("id","block_perms_add");
		$block_perms_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_perms_add");
		$block_perms_add->setContent($this->form_perms_add->toTableString());
		// ==========================

		echo $block_list
		    .$block_edit
		    .$this->dt_edit
		    .$this->dt_perms_add
		    .$block_add
		    .$block_perms
		    .$block_perms_add;
	}
	/**
	 * Обработка формы редактирования
	 */
	public function event_edit(&$form,&$fields) {
		if (!isset($fields["id"]) || !isset($fields["name"]))
			return "Один или несколько параметров не указаны";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.groups.edit")) return "Вам не разрешено редактировать группы";

		$module = \modules\Module::load("accounts");
		$util = $module->getGroupUtils();
		try {
			$util->setGroup((int)$fields["id"],$fields["name"]);
			return "Группа отредактирована";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}
	/**
	 * Обработка формы создания группы
	 */
	public function event_add(&$form,&$fields) {
		if (!isset($fields["name"]))
			return "Имя не указано";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.groups.edit")) return "Вам не разрешено добавлять группы";

		$module = \modules\Module::load("accounts");
		$util = $module->getGroupUtils();
		try {
			$util->addGroup($fields["name"]);
			return "Группа создана";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}
	/**
	 * Обработка формы удаления
	 */
	public function event_del(&$form,&$fields) {
		if (!isset($fields["id"]))
			return "Идентификатор не указан";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.groups.edit")) return "Вам не разрешено удалять группы";

		$module = \modules\Module::load("accounts");
		$util = $module->getGroupUtils();
		try {
			$util->delGroup((int)$fields["id"]);
			return "Группа удалена";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
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
		$util = $module->getGroupUtils();
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
		if (!isset($fields['group']) || !isset($fields['perm']))
			return "Один или несколько параметров не заданы";

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.permissions.edit")) return "Вам не разрешено добавлять привилегии";

		$util = $accounts->getGroupUtils();
		try {
			$util->addPermission((int)$fields["group"],$fields["perm"]);
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
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"accounts.permissions.edit")) return "Вам не разрешено удалять привилегии";

		$util = $accounts->getGroupUtils();
		try {
			$util->delPermission((int)$fields["id"]);
			return "Привилегия удалена";
		} catch (\Exception $e) {
			return "Ошибка: ". $e->getMessage();
		}
	}

}