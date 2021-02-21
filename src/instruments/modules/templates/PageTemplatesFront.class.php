<?php
namespace modules\templates;
\modules\Module::load("admin")->loadAdminPage();
use \modules\admin\Block;
use \modules\admin\Table;
use \modules\admin\Form;
class PageTemplatesFront extends \modules\admin\AdminPage {
	
	/**
	 * Получить заголовок страницы
	 * Переопределяемый метод
	 * @return string
	 */
	public function getTitle() {
		return "Страницы";
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
		return true;
	}
	/**
	 * Подготовка к инициализации лайаута и шаблона страницы
	 * Переопределяемый метод
	 * @param $layout - Layout страницы
	 */
	public function prestruct(&$layout) {

		// Форма создания страницы
		$this->form_add = new Form("form_add");
		$types = array(
			array(
				"value"  => "not_found",
				"#value" => '"Страница не найдена"'
			),
		);
		$this->form_add->addSelect("alias",$types,"Страница");
		$this->form_add->addInput("executor","Обработчик")->setRequired();
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
		
		// Список страниц
		$block_list = new Block();
		$block_list->setTitle("Список корневых страниц");
		$block_list->addHeaderButton("Добавить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		// Контент блока
			$table = new Table();
			$block_list->setContent($table);
			$line = $table->addLine();
			$line->addCell("Назначение");
			$line->addCell("Обработчик");
			$line->addCell("Тип страницы");
			$pages = \Path::listPagesRaw(1,20,0);
			foreach ($pages as $page) {
				$line = $table->addLine();
				switch ($page["alias"]) {
					case "front":
						$name = "Главная страница";
						break;
					case "not_found":
						$name = "Страница не найдена";
						break;
					default:
						$name = $page["alias"];
				}
				$line->addCell( (new \XMLnode("a",$name))->setAttr("href",LOCALE_PREFIX.$this->path."/".$page["alias"]."/") );
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
		// =============

		// Блок добавления страницы
		$block_add = new Block();
		$block_add->setTitle("Добавить страницу");
		$block_add->getAttr()->addClass("hidden")->set("id","block_add");
		$block_add->addHeaderButton("Отменить")->getAttr()->addClass("event_btn_hide")->set("hide-target","#block_add");
		$block_add->setContent($this->form_add->toTableString());
		// ========================

		echo $block_list
		    .$block_add;
	}
	/**
	 * Создание новой страницы
	 */
	public function event_add(&$form,&$fields) {
		if (!isset($fields["executor"]))
			return "Обработчик не задан";
		if (isset($fields["alias"])) {
			switch ($fields["alias"]) {
				case "not_found":
					try {
						\Path::addPage(
								"not_found",
								0,
								$fields["executor"],
								0
							);
						return "Страница успешно создана.";
					} catch (\Exception $e) {
						return $e->getMessage();
					}
					break;
				default:
					return "Тип страницы не опознан";
			}
		} else 
			return "Не указан тип страницы";
	}
}