<?php
class PageEditor extends PathExecutor {
	public function getTitle() {
		return "Страницы";
	}
	public function response() {
		if (!isset($_GET['event'])) {
			$this->header();
			$this->page();
			$this->footer();
			return null;
		}
		switch ($_GET['event']) {
			case "edit":
				$this->edit();
				break;
			case "add":
				$this->add();
				break;
			case "del":
				$this->del();
				break;
			default:
				$this->header();
				$this->page();
				$this->footer();
		}
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
			<button class="event_add">Создать страницу</button>
<?php
			$this->poup_form_add();
	}
	public function footer() {
?>		</center>
<?php
		$this->scripts();?>
	</body>
</html><?php
	}
	public function poup_form_add() {
?>			<div class="popup_add" style="display: none">
				<h2>Создать страницу</h2>
				<div class="create_form">
					<input name="alias" placeholder="Псевданим" />
					<br />
					<label for="add_parent">Находится в </label>
					<select id="add_parent" name="parent" class="select_of_page"></select>
					<br />
					<input name="executor" placeholder="Обработчик" />
					<br />
					<label for="add_type">Тип: </label>
					<select id="add_type" name="type">
						<option value="0">Статическая</option>
						<option value="1">Переменная</option>
						<option value="2">Бесконечная</option>
					</select>
					<br />
					<input name="params" placeholder="Параметры" />
					<br />
					<button type="submit">Создать</button>
				</div class="form">
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
						<th>Псевданим</th>
						<th>Местоположение</th>
						<th>Обработчик</th>
						<th>Тип страницы</th>
						<th>Параметры</th>
						<th>Действие</th>
					</tr>
				</thead>
				<tbody class="pages">
					<?php
						$pages = \Path::listPagesRaw(1,300);
						foreach($pages as $page) { ?>
					<tr id="page_<?=$page['id'];?>" class="page object_page">
						<td class="page_id"><?=$page['id'];?></td>
						<td><input name="alias" value="<?=$page['alias'];?>" /></td>
						<td>
							<!--
							<input type="number" min="0" name="parent" value="<?=$page['parent'];?>" />
							-->
							<select name="parent" class="select_of_page" v="<?=$page['parent'];?>">
								<option value="<?=$page['parent'];?>" selected></option>
							</select>
						</td>
						<td><input name="executor" value="<?=$page['executor'];?>" /></td>
						<td>
							<select name="type">
								<option value="0"<?php if ($page['type'] == 0) { ?> selected<?php }?>>Статическая</option>
								<option value="1"<?php if ($page['type'] == 1) { ?> selected<?php }?>>Переменная</option>
								<option value="2"<?php if ($page['type'] == 2) { ?> selected<?php }?>>Бесконечная</option>
							</select>
						</td>
						<td><input name="params" value="<?=$page['params'];?>" /></td>
						<td>
							<button class="event_delete">Удалить</button>
							<br>
							<button class="event_save">Сохранить</button>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
<?php
	}
public function scripts() {
?>		<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
		<script>
			$(document).ready(function(){
				update_selects_of_pages();
				add_events();
				$('.event_add').click(function(){
					if ($('.popup_add').is(":hidden"))
						$('.popup_add').show();
					else
						$('.popup_add').hide();
				});
				$('.create_form button[type=submit]').click(function(){
					page = $(this).closest(".create_form");
					alias = page.find("input[name=alias]").val();
					parent = page.find("select[name=parent]").val();
					executor = page.find("input[name=executor]").val();
					type = page.find("select[name=type]").val();
					params = page.find("input[name=params]").val();
					$.get("",{
						"event": "add",
						"alias": alias,
						"parent": parent,
						"executor": executor,
						"type": type,
						"params": params
					},function(data){
						jdata = JSON.parse(data);
						if (jdata.result == "Ok.") {
							val0_suffix = "";
							if (type == 0) val0_suffix = " selected";
							val1_suffix = "";
							if (type == 1) val1_suffix = " selected";
							val2_suffix = "";
							if (type == 2) val2_suffix = " selected";
							$('.pages').append(""
								+"<tr id=\"page_"+jdata.id+"\" class=\"page object_page\">"
									+"<td class=\"page_id\">"+jdata.id+"</td>"
									+"<td><input name=\"alias\" value=\""+alias+"\" /></td>"
									+"<td><select name=\"parent\" class=\"select_of_page\"><option value=\""+parent+"\"></option></select></td>"
									+"<td><input name=\"executor\" value=\""+executor+"\" /></td>"
									+"<td>"
										+"<select name=\"type\">"
											+"<option value=\"0\""+val0_suffix+">Статическая</option>"
											+"<option value=\"1\""+val1_suffix+">Переменная</option>"
											+"<option value=\"2\""+val2_suffix+">Бесконечная</option>"
										+"</select>"
									+"</td>"
									+"<td><input name=\"params\" value=\""+params+"\" /></td>"
									+"<td><button class=\"event_delete\">Удалить</button><br><button class=\"event_save\">Сохранить</button></td>"
								+"</tr>"
							);
							update_selects_of_pages();
							add_events();
						} else {
							alert("Создание не удалось. Причина: " + jdata.cause);
						}
					});
				});
			});
			function update_selects_of_pages() {
				pages = {
					0:{
						"alias":"::root::",
						"parent":0
					}
				};
				$(".object_page").each(function(){
					id = $(this).find(".page_id").text();
					pages[id] = {
						"parent":$(this).find("select[name=parent]").val(),
						"alias":$(this).find("input[name=alias]").val()
					};
					if (pages[id]["parent"] == "0" && pages[id]["alias"] == "front") pages[id]["alias"] = "/";
				});
				$("select.select_of_page").empty();
				for (page in pages) {
					$('select.select_of_page').append("<option value=\""+page+"\">"+getPageName(page,pages)+"</option>");
				}
				for (page in pages) {
					$("#page_"+page+" select.select_of_page option[value="+page+"]").remove();
					$("#page_"+page+" select.select_of_page option[value="+pages[page]["parent"]+"]").attr("selected","selected");
				}
			}
			function getPageName(page,pages,name = "") {
				if (page == 0 && name == "") return pages[page]["alias"];
				else if (page == 0) return name;
				//else if (page == 0) return pages[page]["alias"]+"/"+name;
				if (name == "")
					return getPageName(pages[page]["parent"],pages,pages[page]["alias"]);
				else if (pages[page]["alias"] == "/")
					return getPageName(pages[page]["parent"],pages,pages[page]["alias"]+name);
				else
					return getPageName(pages[page]["parent"],pages,pages[page]["alias"]+"/"+name);
				return "asd";
			}
			function add_events() {
				$('.event_save').click(function(){
					page = $(this).closest(".page");
					$.get("",{
						"event": "edit",
						"id": page.find(".page_id").text(),
						"alias": page.find("input[name=alias]").val(),
						"parent": page.find("select[name=parent]").val(),
						"executor": page.find("input[name=executor]").val(),
						"type": page.find("select[name=type]").val(),
						"params": page.find("input[name=params]").val()
					},function(data){
						jdata = JSON.parse(data);
						if (jdata.result == "Ok.") {
							update_selects_of_pages();
							alert("Успешно сохранено.");
						}
						else alert("Сохранение не удалось. Причина: " + jdata.cause);
					});
				});
				$('.event_delete').click(function(){
					page = $(this).closest(".page");
					$.get("",{
						"event": "del",
						"id": page.find(".page_id").text()
					},function(data){
						if (data == "Ok.") {
							page.remove();
							update_selects_of_pages();
						}
						else alert(data);
					});
				});
			}
		</script>
<?php
}
	/**
	 * редактировать страницу
	 */
	public function edit() {
		if (!isset($_GET['id']) || !isset($_GET['alias']) || !isset($_GET['parent']) || !isset($_GET['executor'])) {
			echo json_encode(array(
				"result" => "Error.",
				"cause"  => "Один или несколько параметров не найдены"
			));
			return null;
		}
		
		$params = array();
		if (isset($_GET['id'])) $_GET['id'] = (int)$_GET['id'];
		if (isset($_GET['parent'])) $_GET['parent'] = (int)$_GET['parent'];
		if (isset($_GET['type'])) $_GET['type'] = (int)$_GET['type'];
		try {
			\Path::editPage($_GET['id'],$_GET['alias'],$_GET['parent'],$_GET['executor'],$_GET['type'],$_GET['params']);
			echo json_encode(array(
				"result" => "Ok."
			));
		} catch (\Exception $e) {
			echo json_encode(array(
				"result" => "Error.",
				"cause"  => $e->getMessage()
			));
		}
	}
	/**
	 * Добавить страницу
	 */
	public function add() {
		if (!isset($_GET['alias']) || !isset($_GET['parent']) || !isset($_GET['executor'])) {
			echo json_encode(array(
				"result" => "Error.",
				"cause"  => "Один или несколько параметров не найдены"
			));
			return null;
		}
		$params = array();
		if (isset($_GET['parent'])) $_GET['parent'] = (int)$_GET['parent'];
		if (isset($_GET['type'])) $_GET['type'] = (int)$_GET['type'];
		try {
			$result = \Path::addPage($_GET['alias'],$_GET['parent'],$_GET['executor'],$_GET['type'],$_GET['params']);
			echo json_encode(array(
				"result" => "Ok.",
				"id"     => $result
			));
		} catch (\Exception $e) {
			echo json_encode(array(
				"result" => "Error.",
				"cause"  => $e->getMessage()
			));
		}
	}
	/**
	 * Удалить страницу
	 */
	public function del() {
		try {
			\Path::delPage((int)$_GET['id']);
			echo 'Ok.';
		} catch (\Exception $e) {
			echo 'Error. '.$e->getMessage();
		}
	}
}
