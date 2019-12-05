<?//1573549934
class Templateusers {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "groups":return "0";break;case "scripts":return "1";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div id="perms_popup" class="overlay-popup" style="display:none">
	<div class="popup">
	<button class="close" title="Закрыть" onclick="$('#perms_popup').hide();"></button>
	<a id="ListPermissions"></a>
	</div>
</div>
<div id="edit_block" class="block" style="display:none">
	<div class="block-head">Редактировать пользователя <a class="title-edit_popup"></a></div>
	<form id="edit_popup_form" method="POST" class="block-body">
	    <input type="hidden" name="action" value="editUser">
	    <input type="hidden" name="id">
	    <table>
		    <tr>
			    <td>Логин:</td>
			    <td><input type="text" name="login" autocomplete="off"></td>
		    </tr>
		    <tr>
			    <td>Новый пароль:</td>
			    <td><input type="password" name="password" autocomplete="off"></td>
		    </tr>
		    <tr>
			    <td>Группа:</td>
			    <td>
				    <select name="group">
					    <?$this->c->callBlock($this,"block0","groups",$d);?>
				    </select>
			    </td>
		    </tr>
	    </table>
	    <input type="submit" value="Применить" class="btn bc_gray">
	    <a class="btn bc_gray" id="close_edit_block">Отмена</a>
    </form>
</div>
<div id="add_block" class="block" style="display:none">
	<div class="block-head">Зарегистрировать нового пользователя</div>
	<form id="edit_popup_form" method="POST" class="block-body">
	    <input type="hidden" name="action" value="addUser">
	    <table>
		    <tr>
			    <td>Логин:</td>
			    <td><input type="text" name="login" autocomplete="off"></td>
		    </tr>
		    <tr>
			    <td>Пароль:</td>
			    <td><input type="password" name="password" autocomplete="off"></td>
		    </tr>
		    <tr>
			    <td>Повторить пароль:</td>
			    <td><input type="password" name="password_confirm" autocomplete="off"></td>
		    </tr>
	    </table>
	    <input type="submit" value="Зарегистрировать" class="btn bc_gray">
	    <a class="btn bc_gray" id="close_add_block">Отмена</a>
    </form>
</div>
<span id="btn_add" class="btn bc_gray">Зарегистрировать нового</span>
<div class="pages"></div>
<div class="block">
	<div class="block-head">Пользователи</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeUsersByAdmin">
		<ul id="table_content" class="block-body"></ul>
	</form>
</div>
<?$this->c->callBlock($this,"block1","scripts",$d);?>
<?
}
	public function block0($v=array(),&$d=null) {?>
<option id="group_<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>" value="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></option><?
	}
	public function block1($v=array(),&$d=null) {?>

<script>
			// Функция обновления контента
	function updateContent() {
			
			// Подгрузка кнопок страниц
		$.get("",{"countUsers":"true"},function(data){
			var con = "<a class=\'btn\'>1</a>";
			col=1;
			p=0;
			for(i=1;i<=data;i++){
				if(p==20){
					p=0;
					col++;
					con += "<a class=\'btn\'>"+col+"</a>";
				}
				p++;
			}
			$(".pages").html(con);
			$(".pages").find("a").click(function(){window.page = $(this).text();updateContent();});
		});
			
			// Подгрузка основного контента
		$.get("",{"json":"true","page":window.page},function(data){
			var jdata = JSON.parse(data);
			var con = "<li class=\"tline\"><div class=\"tline-cell\">Логин</div><div class=\"tline-cell\">Группа</div><div class=\"tline-cell\">Удалить</div><div class=\"tline-cell\">Действие</div></li>";
			for (var i in jdata) {
				con += "<li class=\"tline\">\
				<div class=\"tline-cell\">"+jdata[i]["login"]+"</div>\
				<div class=\"tline-cell\">"+jdata[i]["group"]+"</div>\
				<div class=\"tline-cell\"><input type=\'checkbox\' name=\'delete[]\' value=\'"+i+"\'></div>\
				<div class=\"tline-cell\"><a \
					class=\'btn bc_gray event_edit\' \
					data-id=\'"+i+"\' \
					data-name=\'"+jdata[i]["login"]+"\' \
					data-group=\'"+jdata[i]["group-id"]+"\'\
				>Редактировать</a></div>\
				</li>";
			}
			con += "<input type=\"submit\" value=\"Применить\" class=\"btn bc_gray\">";
			con += "		<a class=\"btn bc_gray\" onclick=\"updateContent();\" >Обновить</a>";
			$("#table_content").html(con);
			var editform = $("#edit_block");
	
			// Обработка эвентов
		    $(".event_edit").click(function(){
			    editform.find(".title-edit_popup").text($(this).attr("data-name"));
				editform.find("input[name=id]").val($(this).attr("data-id"));
				editform.find("input[name=login]").val($(this).attr("data-name"));
				editform.find("input[name=password]").val("");
				editform.find("select[name=group]").find("option").removeAttr("selected");
				editform.find("#group_"+$(this).attr("data-group")).attr("selected","true");
				editform.show();
			});
			$(".event_perm").click(function(){
				$("#ListPermissions").html($(this).attr("data-perms"));
				$("#perms_popup").show();
			});
		});
	}
			
			// При загрузке страницы
		$(function($){
			$('#btn_add').click(function(){
				$(this).hide();
				$('#add_block').show();
			});
			$('#close_add_block').click(function(){
				$('#add_block').hide();
				$('#btn_add').show();
			});
		    $('#close_edit_block').click(function(){
		        $('#edit_block').hide();
		    });
			window.page = 1;
			updateContent();
		});
	</script>
<?
	}
}