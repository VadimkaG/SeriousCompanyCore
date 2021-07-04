<?php
class PageEditor extends PathExecutor {

	protected $message = null;

	public function getTitle() {
		return "Страницы";
	}
	public function response() {
		if (isset($_POST['event']))
			switch ($_POST['event']) {
				case "edit":
					$this->edit();
					break;
				case "add":
					$this->add();
					break;
			}
		$this->header();
		$this->page();
		$this->footer();
	}
	public function header() {
?><html>
	<head>
		<meta charset="utf-8">
		<title>SeriousCompany Core</title>
	</head>
	<body>
		<center>
			<h1>Страницы сайта</h1>
<?php
			$this->poup_form_add();
	if (isset($this->message)):
	?>
	<div><?php echo $this->message;?></div>
	<?php endif;
	}
	public function footer() {
?>		</center>
	</body>
</html><?php
	}
	public function poup_form_add() {
?>			<div class="popup_add">
				<h2>Создать страницу</h2>
				<form method="POST" class="create_form">
					<input type="hidden" name="event" value="add">
					<input name="url" placeholder="URL" />
					<br />
					<input name="executor" placeholder="Обработчик" />
					<br />
					<input name="params" placeholder="Параметры" />
					<br />
					<input name="variable" type="checkbox" />Переменная
					<br />
					<button type="submit">Создать</button>
				</form>
				<br />
				<br />
				<br />
			</div>
<?php
	}
	public function page() {
?>			<table>
				<thead>
					<tr>
						<th>Идентификатор</th>
						<th>URL</th>
						<th>Промежуточная</th>
						<th>Обработчик</th>
						<th>Параметры</th>
						<th>Действие</th>
					</tr>
				</thead>
				<tbody class="pages">
					<?php
						$pages = \Path::loadByCondition(null,20,1);
						foreach($pages as $page) { ?>
					<tr id="page_<?php echo $page->id();?>" class="page object_page">
						<form method="POST">
							<input type="hidden" name="event" value="edit">
							<input type="hidden" name="id" value="<?php echo $page->id();?>">
							<td class="page_id"><?php echo $page->id();?></td>
							<td><input name="url" value="<?php echo $page->get("url");?>" /></td>
							<td><input name="variable" type="checkbox" <?php if ($page->get("variable")): ?>checked <?php endif;?> /></td>
							<td><input name="executor" value="<?php echo $page->get("executor");?>" /></td>
							<td><input name="params" value="<?php echo $page->get("params");?>" /></td>
							<td>
								<button class="event_delete" name="delete">Удалить</button>
								<br>
								<button class="event_save">Сохранить</button>
							</td>
						</form>
					</tr>
					<?php } ?>
				</tbody>
			</table>
<?php
	}
	/**
	 * редактировать страницу
	 */
	public function edit() {

		if (isset($_POST['delete']) && isset($_POST['id'])) {
			$path = \Path::load($_POST['id']);
			$path->delete();
			$this->message = "Страница '".$_POST['id']."' успешно удалена!";
			return;
		}

		if (!isset($_POST['id']) || !isset($_POST['url']) || !isset($_POST['executor'])) {
			$this->message = "Один или несколько параметров не найдены";
			return;
		}
		
		$params = array();
		if (isset($_POST['id'])) $_POST['id'] = (int)$_POST['id'];
		try {
			$path = \Path::load($_POST['id']);
			if ($path === null) {
				$this->message = "Страница не найдена";
				return;
			}
			$path->set("url",$_POST['url']);

			$executor_path = root.str_replace(".","/",$_POST['executor']).".class.php";
			if (file_exists($executor_path)) {
				try {
					include_once($executor_path);
				} catch (\Exception $e) {
					$this->message = "Во время загрузки исполнителя '".$executor_name."' произошла ошибка: ".$e->getMessage();
					return;
				}
				$executor_path = explode("/",$_POST['executor']);
				$executor_name = "\\".str_replace(".","\\",end($executor_path));

				if (!class_exists($executor_name)) {
					$this->message = "Класс исполнителя '".$executor_name."' не найден";
					return;
				}

				$path->set("executor",$_POST['executor']);

			} else {
				$this->message = "Исполнитель по пути '".$executor_path."' не найден";
				return;
			}

			if (isset($_POST['params']))
				$path->set("params",$_POST['params']);

			$path->set("variable",isset($_POST['variable'])?true:false);
			$path->save();
			$this->message = "Страница '".$_POST['id']."' успешно обновлена!";
		} catch (\Exception $e) {
			$this->message = $e->getMessage();
		}
	}
	/**
	 * Добавить страницу
	 */
	public function add() {
		if (!isset($_POST['url']) || !isset($_POST['executor'])) {
			$this->message = "Один или несколько параметров не найдены";
			return;
		}

			$executor_path = root.str_replace(".","/",$_POST['executor']).".class.php";
		if (!file_exists($executor_path)) {
			$this->message = "Исполнитель по пути '".$executor_path."' не найден";
			return;
		}
		try {
			include_once($executor_path);
		} catch (\Exception $e) {
			$this->message = "Во время загрузки исполнителя '".$executor_name."' произошла ошибка: ".$e->getMessage();
			return;
		}
		$executor_path = explode("/",$_POST['executor']);
		$executor_name = "\\".str_replace(".","\\",end($executor_path));

		if (!class_exists($executor_name)) {
			$this->message = "Класс исполнителя '".$executor_name."' не найден";
			return;
		}

		if (!isset($_POST['params']))
			$_POST['params'] = "";
		try {
			$path = new \Path([
				"url" => $_POST['url'],
				"variable" => isset($_POST['variable'])?true:false,
				"executor" => $_POST['executor'],
				"params" => $_POST['params']
			]);
			$path->save();
			$this->message = "Новая страница успешно добавлена!";
		} catch (\Exception $e) {
			$this->message = $e->getMessage();
			return;
		}
	}
}
