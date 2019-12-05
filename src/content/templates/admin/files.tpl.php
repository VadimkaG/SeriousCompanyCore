<?//1566892942
class Templatefiles {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "scripts":return "0";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div class="block">
	<div class="block-head">Загрузить файл</div>
	<div class="block-body"><div>
		<form enctype="multipart/form-data" method="POST">
			<input type="hidden" name="action" value="downloadFile">
			<input type="hidden" name="MAX_FILE_SIZE" value="5242800">
			<input type="file" name="file"><br>
			<input class="btn bc_gray" type="submit" value="Загрузить файл">
		</form>
	</div></div>
</div>
<div class="pages"></div>
<div class="block">
	<div class="block-head">Сервера</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeServer">
		<ul id="table_content" class="block-body">
			<li class="tline el_title">
				<div class="tline-cell">Имя</div>
				<div class="tline-cell">Ссылка</div>
				<div class="tline-cell">Размер</div>
				<div class="tline-cell">Действие</div>
			</li>
		</ul>
	</form>
</div>
<?$this->c->callBlock($this,"block0","scripts",$d);?>

<?}
	public function block0($v=array(),&$d=null) {?>

<script>
	$(document).ready(function(){
		window.page=1;
		function updateContent() {
			$.get("",{"count":"true"},function(data){
				con="<a class=\"btn\">1</a>";
				col=1;
				p=0;
				for(i=1;i<=data;i++) {
					if (p==20){
						p=0;
						col++;
						con += "<a class=\"btn\">"+col+"</a>";
					}
					p++;
				}
				$(".pages").html(con);
				$(".pages").find("a").click(function(){
					window.page=$(this).text();
					updateContent();
				});
			});
			
			$.get("",{"json":"true","page":window.page},function(data){
				jdata = JSON.parse(data);
				con="";
				title = $(".el_title").html();
				for(i in jdata) {
					con += "<li class=\"tline\"><div class=\"tline-cell\">"+
					jdata[i]["name"]+"</div><div class=\"tline-cell\">"+
					"<a href=\"/"+jdata[i]["path"]+"\" target=\"_blank\">/"+
					jdata[i]["path"]+"</a></div><div class=\"tline-cell\">"+
					jdata[i]["size"]+" Мб.</div><div class=\"tline-cell\">"+
					"<form method=\"POST\">"+
					"<input type=\"hidden\" name=\"action\" value=\"deleteFile\">"+
					"<input type=\"hidden\" name=\"filename\" value=\""+jdata[i]["name"]+"\">"+
					"<input type=\"submit\" class=\"btn bc_gray\" value=\"Удалить\">"+
					"<form>"+
					"</div></li>"
				}
				$("#table_content").html("<li class=\"tline el_title\">"+title+"</li>"+con);
			});
		}
		updateContent();
	});
</script>

<?	}
}