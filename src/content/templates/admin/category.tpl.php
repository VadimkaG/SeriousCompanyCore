<?//1568804388
class Templatecategory {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "scripts":return "0";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?>
<style>
	@font-face {
	  font-family: 'icomoon';
	  src:  url('/resources/ogami/fonts/icomoon.eot?ytr100');
	  src:  url('/resources/ogami/fonts/icomoon.eot?ytr100#iefix') format('embedded-opentype'),
		url('/resources/ogami/fonts/icomoon.ttf?ytr100') format('truetype'),
		url('/resources/ogami/fonts/icomoon.woff?ytr100') format('woff'),
		url('/resources/ogami/fonts/icomoon.svg?ytr100#icomoon') format('svg');
	  font-weight: normal;
	  font-style: normal;
	}

	[class^="icon-"], [class*=" icon-"] {
	  /* use !important to prevent issues with browser extensions that change fonts */
	  font-family: 'icomoon' !important;
	  speak: none;
	  font-style: normal;
	  font-weight: normal;
	  font-variant: normal;
	  text-transform: none;
	  line-height: 1;

	  /* Better Font Rendering =========== */
	  -webkit-font-smoothing: antialiased;
	  -moz-osx-font-smoothing: grayscale;
	}

	.icon-1:before {
	  content: "\e900";
	}
	.icon-2:before {
	  content: "\e901";
	}
	.icon-3:before {
	  content: "\e902";
	}
	.icon-4:before {
	  content: "\e903";
	}
	.icon-5:before {
	  content: "\e904";
	}
	.icon-6:before {
	  content: "\e905";
	}
	.icon-7:before {
	  content: "\e906";
	}
	.icon-8:before {
	  content: "\e907";
	}
	.icon-9:before {
	  content: "\e908";
	}
	.icon-10:before {
	  content: "\e909";
	}
	.icon-11:before {
	  content: "\e90a";
	}
	.icon-12:before {
	  content: "\e90b";
	}
	.custom-select .display {
		font-size: 30px;
	}
	.custom-select:hover > .list {
		display: block;
	}
	.custom-select > .list {
	  position: absolute;
	  background: white;
	  display: none;
	}
	.custom-select > .list > .list-item {
	  list-style: none;
	  min-width: 150px;
	  text-align: center;
	}
	.custom-select > .list > .list-item:hover {
	  background:#bdc6ff;
	}
</style>
<div id="add_block" class="block" style="display:none">
	<div class="block-head">Редактировать категорию <a class="title-edit_popup"></a></div>
	<form id="edit_popup_form" method="POST" class="block-body" enctype="multipart/form-data">
	    <input type="hidden" name="action" value="addCategory">
	    <table>
		    <tr>
			    <td>Наименование:</td>
			    <td><input type="text" name="name" required></td>
		    </tr>
		    <tr>
			    <td>Иконка:</td>
			    <td>
					<div class="custom-select">
						<input type="hidden" name="icon" value="1" class="value">
						<div class="display">Выберите значение</div>
						<ul class="list">
							<li value="1" class="list-item icon-1"></li>
							<li value="2" class="list-item icon-2"></li>
							<li value="3" class="list-item icon-3"></li>
							<li value="4" class="list-item icon-4"></li>
							<li value="5" class="list-item icon-5"></li>
							<li value="6" class="list-item icon-6"></li>
							<li value="7" class="list-item icon-7"></li>
							<li value="8" class="list-item icon-8"></li>
							<li value="9" class="list-item icon-9"></li>
							<li value="10" class="list-item icon-10"></li>
							<li value="11" class="list-item icon-11"></li>
							<li value="12" class="list-item icon-12"></li>
						</ul>
					</div>
			    </td>
		    </tr>
		    <tr>
			    <td>Картинка:</td>
			    <td><input type="file" name="image"></td>
		    </tr>
	    </table>
	    <input type="submit" value="Создать" class="btn bc_gray">
	    <a class="btn bc_gray" id="close_add_block">Отмена</a>
    </form>
</div>
<div id="edit_block" class="block" style="display:none">
	<div class="block-head">Редактировать категорию <a class="title-edit_popup"></a></div>
	<form id="edit_popup_form" method="POST" class="block-body" enctype="multipart/form-data">
	    <input type="hidden" name="action" value="editCategory">
	    <input type="hidden" name="id">
	    <table>
		    <tr>
			    <td>Наименование:</td>
			    <td><input type="text" name="name" required></td>
		    </tr>
		    <tr>
			    <td>Иконка:</td>
			    <td>
					<div class="custom-select">
						<input type="hidden" name="icon" value="1" class="value">
						<div class="display">Выберите значение</div>
						<ul class="list">
							<li value="1" class="list-item icon-1"></li>
							<li value="2" class="list-item icon-2"></li>
							<li value="3" class="list-item icon-3"></li>
							<li value="4" class="list-item icon-4"></li>
							<li value="5" class="list-item icon-5"></li>
							<li value="6" class="list-item icon-6"></li>
							<li value="7" class="list-item icon-7"></li>
							<li value="8" class="list-item icon-8"></li>
							<li value="9" class="list-item icon-9"></li>
							<li value="10" class="list-item icon-10"></li>
							<li value="11" class="list-item icon-11"></li>
							<li value="12" class="list-item icon-12"></li>
						</ul>
					</div>
			    </td>
		    </tr>
		    <tr>
			    <td>Картинка:</td>
			    <td><input type="file" name="image"></td>
		    </tr>
	    </table>
	    <input type="submit" value="Применить" class="btn bc_gray">
	    <a class="btn bc_gray" id="close_edit_block">Отмена</a>
    </form>
</div>
<div class="btn gray event_add_category">Добавить категорию</div>
<div class="pages"></div>
<div class="block">
	<div class="block-head">Категории</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeCategories">
		<ul id="table_content" class="block-body"></ul>
	</form>
</div>
<?$this->c->callBlock($this,"block0","scripts",$d);?>
<?
}
	public function block0($v=array(),&$d=null) {?>

<script>
			// Функция обновления контента
	function updateContent() {
			
			// Подгрузка кнопок страниц
		$.get("",{"count":"true"},function(data){
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
			var con = "<li class=\"tline\"><div class=\"tline-cell\">Наименование</div><div class=\"tline-cell\">Удалить</div><div class=\"tline-cell\">Действие</div></li>";
			for (var i in jdata) {
				con += "<li class=\"tline\">\
				<div class=\"tline-cell\">"+jdata[i]["name"]+"</div>\
				<div class=\"tline-cell\"><input type=\'checkbox\' name=\'delete[]\' value=\'"+jdata[i]["id"]+"\'></div>\
				<div class=\"tline-cell\"><a \
					class=\'btn bc_gray event_edit\' \
					data-id=\'"+jdata[i]["id"]+"\' \
					data-name=\'"+jdata[i]["name"]+"\' \
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
				editform.find("input[name=name]").val($(this).attr("data-name"));
				editform.show();
			});
		});
	}
		
		// При загрузке страницы
	$(function($){
	    $('#close_edit_block').click(function(){
	        $('#edit_block').hide();
	    });
	    $('.event_add_category').click(function(){
	    	$('#add_block').show();
	    });
	    $('#close_add_block').click(function(){
	    	$('#add_block').hide();
	    });
		window.page = 1;
		updateContent();
		
		$('.custom-select .list-item').click(function(){
			console.log($(this).parent().parent().find('.display').attr('class'));
			$(this).parent().parent().find('.display').html('<span class="icon-'+$(this).attr('value')+'"></span>');
			$(this).parent().parent().find('.value').attr('value',$(this).attr('value'));
		});
	});
</script>
<?
	}
}