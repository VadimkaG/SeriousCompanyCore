<?//1573549934
class Templateshops {
	private $c;
	public function __construct($c) {
		if (!($c instanceof \page\Container)) throw \InvalidArgumentException('$c must be \page\Container');
		$this->c = $c;
	}
	public function getBlockName($name){switch($name){case "category":return "0";break;case "category":return "1";break;case "scripts":return "2";break;default:return '';}}
	public function Template_Main($v=array(),&$d=null) {
?><div id="perms_popup" class="overlay-popup" style="display:none">
	<div class="popup">
	<button class="close" title="Закрыть" onclick="$('#perms_popup').hide();"></button>
	<a id="ListPermissions"></a>
	</div>
</div>
<a id="btn_add_admin" class="btn" style="display:none">Добавить администратора</a>
<div class="pages-admins" style="display:none"></div>
<div id="admins" class="block" style="display:none">
	<div class="block-head">Администраторы</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeAdmins">
		<ul id="table_content-admins" class="block-body"></ul>
	</form>
</div>
<div id="edit_block" class="block" style="display:none">
	<div class="block-head">Редактировать магазин <a class="title-edit_popup"></a></div>
	<form method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="editShop">
	    <input type="hidden" name="id">
		<ul class="block-body">
			<li class="tline">
				<div class="tline-cell center">Псевдоним:</div>
				<div class="tline-cell center"><input name="alias" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Наименование:</div>
				<div class="tline-cell center"><input name="name" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Адрес:</div>
				<div class="tline-cell center"><input name="address" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Телефоны:</div>
				<div class="tline-cell center"><input name="phones" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Электронная почта:</div>
				<div class="tline-cell center"><input name="email" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Карта:</div>
				<div class="tline-cell center"><input name="map"></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Активен:</div>
				<div class="tline-cell center"><input name="validate" type="checkbox"></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Изображение</div>
				<div class="tline-cell center"><input type="file" name="image"></div>
			</li>
			<li class="tline" style="text-align:center">
				<input type="submit" class="btn bc_gray" value="Сохранить">
				<a class="btn bc_gray" id="close_edit_block">Отмена</a>
			</li>
		</ul>
    </form>
</div>
<div id="add_block" class="block" style="display:none">
	<div class="block-head">Создать новый магазин</a></div>
	<form method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="addShop">
		<ul class="block-body">
			<li class="tline">
				<div class="tline-cell center">Псевдоним:</div>
				<div class="tline-cell center"><input name="alias" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Наименование:</div>
				<div class="tline-cell center"><input name="name" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Адрес:</div>
				<div class="tline-cell center"><input name="adress" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Телефоны:</div>
				<div class="tline-cell center"><input name="phones" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Электронная почта:</div>
				<div class="tline-cell center"><input name="email" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Карта:</div>
				<div class="tline-cell center"><input name="map"></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Изображение</div>
				<div class="tline-cell center"><input type="file" name="image"></div>
			</li>
			<li class="tline" style="text-align:center">
				<input type="submit" class="btn bc_gray" value="Создать">
				<a class="btn bc_gray" id="close_add_block">Отмена</a>
			</li>
		</ul>
    </form>
</div>
<a id="btn_add_shop" class="btn">Создать новый магазин</a>
<div class="pages-shops"></div>
<div class="block">
	<div class="block-head">Магазины</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeShop">
		<ul id="table_content" class="block-body"></ul>
	</form>
</div>
<div id="info_block" class="block" style="display:none">
	<div class="block-head">Информация о магазине <a class="title-edit_popup"></a></div>
	<ul class="block-body">
	    <li class="tline">
		    <div class="tline-cell center">Идентификатор:</div>
		    <div class="tline-cell center here_id"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Псевдоним:</div>
		    <div class="tline-cell center here_alias"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Наименование:</div>
		    <div class="tline-cell center here_name"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Адрес:</div>
		    <div class="tline-cell center here_adress"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Телефоны:</div>
		    <div class="tline-cell center here_phones"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Электронная почта:</div>
		    <div class="tline-cell center here_email"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Карта:</div>
		    <div class="tline-cell center here_map"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Дата создания:</div>
		    <div class="tline-cell center here_created"></div>
	    </li>
	    <li class="tline">
		    <div class="tline-cell center">Активен:</div>
		    <div class="tline-cell center here_active"></div>
	    </li>
	    <li class="tline" style="text-align:center">
	    	<a class="btn bc_gray" id="close_info_block">Закрыть</a>
	    </li>
    </ul>
</div>
<div id="add_good_block" class="block" style="display:none">
	<div class="block-head">Содздать новый товар</a></div>
	<form method="POST">
		<input type="hidden" name="action" value="addGood">
		<input type="hidden" name="shop" required>
		<ul class="block-body">
			<li class="tline">
				<div class="tline-cell center">Псевдоним:</div>
				<div class="tline-cell center"><input name="alias" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Наименование:</div>
				<div class="tline-cell center"><input name="name" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Категория:</div>
				<div class="tline-cell center">
					<select name="category">
						<option value="">Выберите категорию</option>
						<?$this->c->callBlock($this,"block0","category",$d);?>
					</select>
				</div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Цена:</div>
				<div class="tline-cell center"><input name="price" type="number" step="0.01" min="0" value="0.1" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Скидка:</div>
				<div class="tline-cell center"><input name="discount" type="number" value="0" min="0" max="100" required></div>
			</li>
			<li class="tline" style="text-align:center">
				<input type="submit" class="btn bc_gray" value="Создать">
				<a class="btn bc_gray" id="close_add_good_block">Отмена</a>
			</li>
		</ul>
    </form>
</div>
<div id="edit_good_block" class="block" style="display:none">
	<div class="block-head">Редактировать товар <a class="title-edit_popup"></a></div>
	<form method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" value="editGood">
	    <input type="hidden" name="id">
		<ul class="block-body">
			<li class="tline">
				<div class="tline-cell center">Псевдоним:</div>
				<div class="tline-cell center"><input name="alias" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Наименование:</div>
				<div class="tline-cell center"><input name="name" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Категория:</div>
				<div class="tline-cell center">
					<select name="category">
						<option value="">Выберите категорию</option>
						<?$this->c->callBlock($this,"block1","category",$d);?>
					</select>
				</div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Увеличитель рейтинга:</div>
				<div class="tline-cell center"><input name="rating" type="number" min="0" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Цена:</div>
				<div class="tline-cell center"><input name="price" type="number" step="0.01" min="0" value="0.1" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Скидка:</div>
				<div class="tline-cell center"><input name="discount" type="number" min="0" max="100" required></div>
			</li>
			<li class="tline">
				<div class="tline-cell center">Загрузить изображения</div>
				<div class="tline-cell center">
					<div class="event_images"></div>
					<div class="btn gray event_add_image">+</div>
				</div>
			</li>
			<li class="tline" style="text-align:center">
				<input type="submit" class="btn bc_gray" value="Сохранить">
				<a class="btn bc_gray" id="close_edit_good_block">Отмена</a>
			</li>
		</ul>
    </form>
</div>
<a id="btn_add_good" class="btn" style="display:none">Создать новый товар</a>
<div class="pages-goods" style="display:none"></div>
<div id="goods" class="block" style="display:none">
	<div class="block-head">Товары</div>
	<form method="POST">
		<input type="hidden" name="action" value="changeGoods">
		<ul id="table_content-goods" class="block-body"></ul>
	</form>
</div>
<div class="pages-comments" style="display:none"></div>
<div id="comments_block" class="block" style="display:none">
	<div class="block-head">Список комментириев товара <a class="title-edit_popup"></a></div>
	<form method="POST">
		<input type="hidden" name="action" value="changeComments">
		<ul class="block-body"></ul>
	</form>
</div>
<?$this->c->callBlock($this,"block2","scripts",$d);?>
<?
}
	public function block0($v=array(),&$d=null) {?>

						<option value="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></option>
						<?
	}
	public function block1($v=array(),&$d=null) {?>

						<option value="<? if (isset($v["id"]))echo $v["id"]; else trigger_error("Значение \"id\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>"><? if (isset($v["name"]))echo $v["name"]; else trigger_error("Значение \"name\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?></option>
						<?
	}
	public function block2($v=array(),&$d=null) {?>
<script src="/<? if (isset($v["scripts"]))echo $v["scripts"]; else trigger_error("Значение \"scripts\" не задано в блоке \"".__FUNCTION__."\"", E_USER_WARNING);?>/shops.js"></script><?
	}
}