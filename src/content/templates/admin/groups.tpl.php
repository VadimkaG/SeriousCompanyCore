<?//1573549934
class Templategroups {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "groups":return "0";break;case "scripts":return "1";break;case "if_delete":return "2";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?>	<div id="alert_popup" class="overlay-popup">
	    <div class="popup">
	    <p class="message"></p>
	    <button class="close" title="Закрыть" onclick="$('#alert_popup').hide();"></button>
	    </div>
    </div>
	<a class="btn bc_gray" id="btn_add">Добавить группу</a>
	<div id="block_add" class="block" style="display:none">
		<div class="block-head">Добавить группу</div>
		<form method="POST" class="block-body">
			<input type="hidden" name="action" value="createGroup">
			<table>
				<tr>
					<td>Имя:</td>
					<td><input type="text" name="name" required></td>
				</tr>
				<tr>
					<td>Смена логина:</td>
					<td>
					    <!--
						<select name="change_login">
							<option value="0">Запрещена</option>
							<option value="1" selected>Разрешена</option>
						</select>
						-->
						<input type="hidden" name="change_login" readonly value="1">
						<span>Разрешена</span>
					</td>
				</tr>
			</table>
			<input type="submit" value="Создать" class="btn bc_gray">
			<a class="btn bc_gray" id="close_add_block">Отмена</a>
		</form>
	</div>
	<div id="block_perm_add" class="block" style="display:none">
		<div class="block-head">Добавить привилегию группе <a class="title-edit_popup"></a></div>
		<form method="POST" class="block-body">
			<input type="hidden" name="action" value="add_permission">
			<input type="hidden" name="group">
			<input type="text" name="permission" placeholder="Привилегия">
			<br>
			<input type="submit" value="Добавить" class="btn bc_gray">
			<a class="btn bc_gray" id="close_perm_add_block">Отмена</a>
		</form>
	</div>
	<div id="block_perm_list" class="block" style="display:none">
		<div class="block-head">Список привилегий группы <a class="title-edit_popup"></a></div>
		<div class="block-body">
		    <a class="event_add_perm btn bc_gray">Добавить</a><br>
		    <a id="ListPermissions"></a>
			<a class="btn bc_gray" id="close_perm_list_block">Закрыть</a>
		</div>
	</div>
	<div id="block_edit" class="block" style="display:none">
		<div class="block-head">Редактировать группу <a class="title-edit_popup"></a></div>
		<form method="POST" class="block-body">
			<input type="hidden" name="action" value="editGroup">
			<input type="hidden" name="id">
			<table>
				<tr>
					<td>Имя:</td>
					<td><input type="text" name="name" required></td>
				</tr>
				<tr>
					<td>Смена логина:</td>
					<td>
						<select name="change_login">
							<option id="sel_0" value="0">Запрещено</option>
							<option id="sel_1" value="1">Разрешено</option>
						</select>
						<a id="allow_change_login">Разрешено</a>
					</td>
				</tr>
			</table>
			<input type="submit" value="Применить" class="btn bc_gray">
			<a class="btn bc_gray" id="close_edit_block">Отмена</a>
		</form>
	</div>
	<div class="block">
		<div class="block-head">Группы</div>
		<form method="POST">
			<input type="hidden" name="action" value="changeGroup">
			<ul id="table_content" class="block-body">
				<li class="tline">
					<div class="tline-cell">Название</div>
					<div class="tline-cell">Смена логина</div>
					<div class="tline-cell">Удалить</div>
					<div class="tline-cell">Действие</div>
				</li>
				<?$this->c->callBlock($this,"block0","groups",$d);?>
				<li class="tline">
					<div class="tline-cell"><input type="submit" value="Применить" class="btn bc_gray"></div>
					<div class="tline-cell"><a href="/admin/groups/" class="btn bc_gray">Обновить</a></div>
				</li>
			</ul>
		</form>
	</div>
<?$this->c->callBlock($this,"block1","scripts",$d);?>
<?
}
	public function block0($v=array(),&$d=null) {?>

				<li class="tline">
					<div class="tline-cell"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></div>
					<div class="tline-cell"><? if (isset($v["can_ch_login"]))echo $v["can_ch_login"]; else trigger_error("Значение \"can_ch_login\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></div>
					<div class="tline-cell"><?$this->c->callBlock($this,"block2","if_delete",$d);?></div>
					<div class="tline-cell">
						<a
							class="btn bc_gray event_editgroup"
							data-id="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"
							data-name="<? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"
							data-ccl="<? if (isset($v["can_change_login"]))echo $v["can_change_login"]; else trigger_error("Значение \"can_change_login\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"
						>Редактировать</a>
						<a
							class="btn bc_gray event_perm"
							data-group="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"
							data-name="<? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"
						>Привилегии</a>
					</div>
				</li>
				<?
	}
	public function block1($v=array(),&$d=null) {?>

<script>
	$(document).ready(function(){
		var editform = $("#block_edit");
		var group = "";
		$(".event_editgroup").click(function(){
			editform.find(".title-edit_popup").text($(this).attr("data-name"));
			editform.find("input[name=id]").val($(this).attr("data-id"));
			editform.find("input[name=name]").val($(this).attr("data-name"));
			/*if ($(this).attr("data-id")!="1") {
				editform.find("#allow_change_login").hide();
				editform.find("select[name=change_login]").show();
			} else {*/
				editform.find("select[name=change_login]").hide();
				editform.find("#allow_change_login").show();
			//}
			editform.find("select[name=change_login]").find("option").removeAttr("selected");
			editform.find("#sel_"+$(this).attr("data-ccl")).attr("selected","true");
			editform.show();
		});
		$(".event_perm").click(function(){
			var group = $(this).attr("data-group");
		    if (group != 1)
		        $.get("",{"perms":group},function(data){
		            var jdata = JSON.parse(data);
		            if (jdata.length != 0) {
		                html = '';
		                for (var txt in jdata) {
		                    html += '<p>'+jdata[txt]+'<a class="event_remove_perm btn bc_gray" data-perm="'+jdata[txt]+'">Удалить</a></p>'
		                }
		            } else html = '<p>Привилегии отсутствуют</p>';
		            $("#ListPermissions").html(html);
			        $(".event_remove_perm").click(function(){
			            pole = $(this);
				        $.post("",{"action":"removePerm","group":group,"permission":$(this).attr("data-perm")},function(data){
					        if (data == 'Ok.') {
					            $('#alert_popup').find('.message').html("Привилегия "+pole.attr("data-perm")+" успешно удалена.");
					            pole.parent().remove();
					        } else
					            $('#alert_popup').find('.message').html(data);
					        $('#alert_popup').show();
				        });
			        });
		        });
		    else $("#ListPermissions").html('<p>Все привилегии</p>');
			$("#block_perm_add").find("input[name=group]").val(group);
			$("#block_perm_list").find('.title-edit_popup').text($(this).attr("data-name"));
			$("#block_perm_add").find('.title-edit_popup').text($(this).attr("data-name"));
			$("#block_perm_list").show();
		});
		$(".event_add_perm").click(function(){
			$("#block_perm_list").hide();
			$("#block_perm_add").show();
		});
		$('#close_edit_block').click(function(){
		    $('#block_edit').hide();
		});
		$('#btn_add').click(function(){
		    $(this).hide();
		    $('#block_add').show();
		});
		$('#close_add_block').click(function(){
		    $('#block_add').hide();
		    $('#btn_add').show();
		});
		$('#close_perm_list_block').click(function(){
		    $("#block_perm_list").hide();
		});
		$('#close_perm_add_block').click(function(){
		    $('#block_perm_add').hide();
		});
	});
</script>
<?
	}
	public function block2($v=array(),&$d=null) {?>
<input type="checkbox" name="delete[]" value="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><?
	}
}