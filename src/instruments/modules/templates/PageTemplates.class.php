<?php
namespace modules\templates;
\modules\Module::load("admin")->loadAdminPage();
use \modules\admin\Block;
use \modules\admin\Table;
use \modules\admin\Form;
use \modules\admin\Pager;
class PageTemplates extends \modules\admin\AdminPage {

	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		return $this->path->getAlias();
	}
	/**
	 * Проверка страницы
	 */
	public function validate() {
		$event = \Event::call("isAuth");
		if (count($event) > 0 && !$event[0])
			return false;
		$event = \Event::call("isSetPermission",[ "perm" => "admin.panel" ]);
		if (count($event) > 0 && !$event[0])
			return false;

		// Определяем какую страницу отображать и редактировать
		$path_str = "";
		$c = 0;
		$parent = $this->path;
		do {
			$c++;
			if ($c > 100) die();
			if ($path_str == "")
				$path_str = $parent->getAlias();
			else
				$path_str = $parent->getAlias()."/".$path_str;
			$parent = $parent->getParent();
		} while( $parent != null && !($parent->executor() instanceof PageTemplatesFront) );
		try {
			$this->page = \Path::getPath($path_str);
		} catch (\Exception $e) {
			return false;
		}

		if ($this->page) return true;
		else return false;
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layout страницы
	 */
	public function prestruct(&$layout) {

		// Форма удаления страницы
		$this->form_del = new Form("form_del");
		$this->form_del->addSubmit("Удалить")->addClass("btn bc_blue");
		$this->form_del->setEvent($this,"event_del");
		if (($message = $this->form_del->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма редактирования страницы
		$this->form_edit = new Form("page_edit");
		$pageRaw = \Path::getPageRaw($this->page->getID());
		$this->form_edit->addInput("alias","Псевданим")->setRequired()->setValue($pageRaw["alias"]);
		$this->form_edit->addInput("executor","Обработчик")->setRequired()->setValue($pageRaw["executor"]);
		$this->form_edit->addInput("params","Параметры")->setValue($pageRaw["params"]);
		$types = array(
			array(
				"value"  => "0",
				"#value" => "Статическая"
			),
			array(
				"value"  => "1",
				"#value" => "Переменная"
			),
			array(
				"value"  => "2",
				"#value" => "Бесконечная"
			)
		);
		if (isset($types[(int)$pageRaw["type"]])) $types[(int)$pageRaw["type"]]["selected"] = "y";
		$this->form_edit->addSelect("type",$types,"Тип страницы");
		$this->form_edit->addSubmit("Изменить")->addClass("btn bc_blue");
		$this->form_edit->setEvent($this,"event_edit");
		if (($message = $this->form_edit->validate()) != false) {
			$layout->setWarning($message);
		}

		// Форма создания страницы
		$this->form_add = new Form("form_add");
		$this->form_add->addInput("alias","Псевданим")->setRequired();
		$this->form_add->addInput("executor","Обработчик")->setRequired();
		$this->form_add->addSelect("type",$types,"Тип страницы");
		$this->form_add->addSubmit("Создать")->addClass("btn bc_blue");
		$this->form_add->setEvent($this,"event_add");
		if (($message = $this->form_add->validate()) != false) {
			$layout->setWarning($message);
		}
	}
	/**
	 * Время для построения конткнта
	 * Переопределяемый метод
	 */
	public function sucture() {

		if ($this->page != null) {
			$block_info = new Block();
			$block_info->setTitle("Страница");
			$block_info->addHeaderButton("Удалить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_del");
			$block_info->setContent($this->form_edit->toTableString());
		}

		$block_list_pager = new Pager("list");
		$block_list_pager->setPagesByCount(\Path::countPages($this->page->getID()));
		
		// Список страниц
		$block_list = new Block();
		$block_list->setTitle("Список вложенных страниц");
		$block_list->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		// Контент блока
			$table = new Table();
			$block_list->setContent($table);
			$line = $table->addLine();
			$line->addCell("Псевданим");
			$line->addCell("Обработчик");
			$line->addCell("Тип страницы");
			if ($this->page != null) {
				$pages = (\Path::listPagesRaw(
						$block_list_pager->current(),
						$block_list_pager->perPage(),
						$this->page->getID()
					));
			} else
				$pages = array();
			foreach ($pages as $page) {
				$line = $table->addLine();
				$line->addCell((new \XMLnode("a",$page["alias"]))->setAttr("href",LOCALE_PREFIX.$this->path."/".$page["alias"]."/") );
				$line->addCell($page["executor"]);
				switch ($page["type"]) {
				default:
					$line->addCell("Статическая");
					break;
				case 1:
					$line->addCell("Переменная");
					break;
				case 2:
					$line->addCell("Бесконечная");
					break;
				}
			}
			$line = $table->addLine();
			$line->addCell($block_list_pager);
		// =============

		// Блок добавления страницы
		$block_add = new Block();
		$block_add->setTitle("Список вложенных страниц");
		$block_add->getAttr()->addClass("hidden")->set("id","block_add");
		$block_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		$block_add->setContent($this->form_add->toTableString());
		// ========================

		// Блок удаления страницы
		$block_del = new Block();
		$block_del->setTitle("Удаление страницы");
		$block_del->getAttr()->addClass("hidden")->set("id","block_del");
		// Контент блока
			$table = new Table();
			$block_del->setContent($table);
			$line = $table->addLine();
			$line->addCell("Вы уверены, что хотите удалить страницу?")->addClass("center");
			$line = $table->addLine();
			$line->addCell($this->form_del)->addClass("center");
			$line->addCell(
				(new \XMLnode("span","Отмена"))->addClass("event_btn_hide btn bc_blue")->setAttr("hide-target","#block_del")
			)->addClass("center");
		// ======================

		echo $block_info
		    .$block_list
		    .$block_add
		    .$block_del;
	}
	/**
	 * Обработка формы редактирования
	 */
	public function event_edit(&$form,&$fields) {
		if (
				!isset($fields["alias"])
				||
				!isset($fields["executor"])
				||
				!isset($fields["type"])
				||
				!isset($fields["params"])
			) {
			return "Некоторые параметры не заданы";
		}

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"page.edit")) return "Вам не разрешено редактировать страницы";

		$fields["type"] = (int)$fields["type"];
		if ($fields["type"] < 0 || $fields["type"] > 2)
			$fields["type"] = 0;
		\Path::editPage($this->page->getID(),$fields["alias"],null,$fields["executor"],$fields["type"],$fields["params"]);
		\Redirect(((string)$this->path->getParent())."/".$fields["alias"]."/");
	}
	/**
	 * Создание новой страницы
	 */
	public function event_add(&$form,&$fields) {
		if (
				!isset($fields["alias"])
				||
				!isset($fields["executor"])
				||
				!isset($fields["type"])
			) {
			return "Некоторые параметры не заданы";
		}

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"page.edit")) return "Вам не разрешено добавлять страницы";

		$fields["type"] = (int)$fields["type"];
		if ($fields["type"] < 0 || $fields["type"] > 2)
			$fields["type"] = 0;
		try {
			\Path::addPage(
					$fields["alias"],
					$this->page->getID(),
					$fields["executor"],
					$fields["type"]
				);
			return "Страница успешно создана.";
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	/**
	 * Удаление страницы
	 */
	public function event_del(&$form,&$fields) {

		$accounts = \modules\Module::load("accounts");
		$id = $accounts->getAuthSession();
		if ($id == NULL) return "Вы не авторизованы";
		$utils_acc = $accounts->getAccountUtils();
		if (!$utils_acc->isSetPermission($id,"page.edit")) return "Вам не разрешено удалять страницы";

		if ($this->page->getParent() == null && $this->page->getAlias() == "front")
			return "Это главная страница сайта. Если не будет ее, то сайт перестанет отображаться. Операция отменена";

		\Path::delPage($this->page->getID());
		\Redirect((string)$this->path->getParent());
	}
}