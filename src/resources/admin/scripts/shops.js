function updateContent() {
	$("#info_block").hide();
	$('#goods').hide();
	$('.pages-goods').hide();
	$('#btn_add_good').hide();
	$('#add_good_block').hide();
	$('#edit_good_block').hide();
	$('.pages-comments').hide();
	$('#comments_block').hide();
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
		$(".pages-shops").html(con);
		$(".pages-shops").find("a").click(function(){window.page = $(this).text();updateContent();});
	});
	$.get("",{"json":"true","page":window.page},function(data){
		var jdata = JSON.parse(data);
		var con = "<li class=\"tline\">\
			<div class=\"tline-cell\">Псевдоним</div>\
			<div class=\"tline-cell\">Наименование</div>\
			<div class=\"tline-cell\">Активен</div>\
			<div class=\"tline-cell\">Когда создан</div>\
			<div class=\"tline-cell\">Рейтинг</div>\
			<div class=\"tline-cell\">Удалить</div>\
			<div class=\"tline-cell\">Действие</div>\
		</li>";
		for (var i in jdata) {
			con += "<li class=\"tline event_shop_info\" data-id=\'"+jdata[i]["id"]+"\'>\
				<div class=\"tline-cell\">"+jdata[i]["alias"]+"</div>\
				<div class=\"tline-cell\">"+jdata[i]["name"]+"</div>\
				<div class=\"tline-cell\">"+boolToString(jdata[i]["active"])+"</div>\
				<div class=\"tline-cell\">"+jdata[i]["created"]+"</div>\
				<div class=\"tline-cell\">"+jdata[i]["rating"]+"</div>\
				<div class=\"tline-cell\"><input type=\'checkbox\' name=\'delete[]\' value=\'"+jdata[i]["id"]+"\'></div>\
				<div class=\"tline-cell center\">\
					<a \
						class=\'btn bc_gray event_edit\' \
						data-id=\'"+jdata[i]["id"]+"\' \
					>Редактировать</a>\
					<a \
						class=\'btn bc_gray event_admins\' \
						data-id=\'"+jdata[i]["id"]+"\' \
					>Администраторы</a>\
				</div>\
			</li>";
		}
		con += "<input type=\"submit\" value=\"Применить\" class=\"btn bc_gray\">";
		con += "		<a class=\"btn bc_gray\" onclick=\"updateContent();\" >Обновить</a>";
		$("#table_content").html(con);
		var editblock = $("#edit_block");
	    var infoblock = $("#info_block");

		// Обработка эвентов
	    $(".event_edit").click(function(){
	    	$.get("",{"shop":$(this).data("id")},function(infodata){
	    		jinfodata = JSON.parse(infodata);
	    		editblock.find(".title-edit_popup").text(jinfodata["name"]);
				editblock.find("input[name=id]").val(jinfodata["id"]);
	    		editblock.find("input[name=alias]").val(jinfodata["alias"]);
	    		editblock.find("input[name=name]").val(jinfodata["name"]);
	    		editblock.find("input[name=address]").val(jinfodata["adress"]);
	    		editblock.find("input[name=phones]").val(jinfodata["phones"]);
	    		editblock.find("input[name=email]").val(jinfodata["email"]);
	    		editblock.find("input[name=map]").val(jinfodata["map"]);
	    		if (jinfodata["active"] == true)
	    			editblock.find("input[name=validate]").attr("checked",1);
	    		else
	    			editblock.find("input[name=validate]").removeAttr("checked");
				editblock.show();
	    	});
		});
	    $(".event_info").click(function(){
	    	$.get("",{"shop":$(this).data("id")},function(infodata){
	    		jinfodata = JSON.parse(infodata);
	    		infoblock.find(".title-edit_popup").text(jinfodata["name"]);
	    		infoblock.find(".here_id").text(jinfodata["id"]);
	    		infoblock.find(".here_alias").text(jinfodata["alias"]);
	    		infoblock.find(".here_name").text(jinfodata["name"]);
	    		infoblock.find(".here_rating").text(jinfodata["rating"]);
	    		infoblock.find(".here_adress").text(jinfodata["adress"]);
	    		infoblock.find(".here_phones").text(jinfodata["phones"]);
	    		infoblock.find(".here_email").text(jinfodata["email"]);
	    		infoblock.find(".here_map").text(jinfodata["map"]);
	    		infoblock.find(".here_created").text(jinfodata["created"]);
	    		infoblock.show();
	    	});
	    });
	    $(".event_shop_info").click(function(){
	    	if ($(this).attr("selected")) return true;
			$('.pages-comments').hide();
			$('#comments_block').hide();
	    	$(".event_shop_info[selected = selected]").removeAttr("selected");
	    	$(this).attr("selected","selected");
	    	$(".event_shop_info").each(function(){
	    		$(this)[0].style.background = "white";
	    	});
	    	$(this)[0].style.background = "gray";
	    	$.get("",{"shop":$(this).data("id")},function(infodata){
	    		jinfodata = JSON.parse(infodata);
	    		infoblock.find(".title-edit_popup").text(jinfodata["name"]);
	    		infoblock.find(".here_id").text(jinfodata["id"]);
	    		infoblock.find(".here_alias").text(jinfodata["alias"]);
	    		infoblock.find(".here_name").text(jinfodata["name"]);
	    		infoblock.find(".here_rating").text(jinfodata["rating"]);
	    		infoblock.find(".here_adress").text(jinfodata["adress"]);
	    		infoblock.find(".here_phones").text(jinfodata["phones"]);
	    		infoblock.find(".here_email").text(jinfodata["email"]);
	    		infoblock.find(".here_map").text(jinfodata["map"]);
	    		infoblock.find(".here_active").text(boolToString(jinfodata["active"]));
	    		infoblock.find(".here_created").text(jinfodata["created"]);
	    		infoblock.show();
	    	});
	    	setGoods($(this).data("id"));
	    });
	    $('.event_admins').click(function(){
	    	setAdmins($(this).data("id"));
	    });
	});
}
function setAdmins(id) {
	$.get("",{"countAdmins":id},function(data){
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
		$(".pages-admins").html(con);
		$(".pages-admins").find("a").click(function(){window.pageadmins = $(this).text();setAdmins(id);});
	});
	$.get("",{"admins":id,"page":window.pageadmins},function(data){
		jdata = JSON.parse(data);
		console.log(jdata);
		con = "<li class=\"tline\">\
			<div class=\"tline-cell\">Логин</div>\
			<div class=\"tline-cell\">Привилегия</div>\
		</li>";
		for (var i in jdata) {
			switch (jdata[i]['permission']) {
				case 0: jdata[i]['permission'] = "Полные привилегии"; break;
				case 1: jdata[i]['permission'] = "Редактор магазина"; break;
				case 2: jdata[i]['permission'] = "Редактор товаров"; break;
			}
			con += "<li class=\"tline\">\
				<div class=\"tline-cell\">"+jdata[i]['user']+"</div>\
				<div class=\"tline-cell\">"+jdata[i]['permission']+"</div>\
			</li>";
		}
		con += "<a class=\"btn bc_gray event_admin_close\" >Закрыть</a>";
		$("#table_content-admins").html(con);
		$(".event_admin_close").click(function(){
			$("#admins").hide();
			$("#btn_add_admin").hide();
			$(".pages-admins").hide();
		});
		$("#admins").show();
		$(".pages-admins").show();
	});
}
function setGoods(id) {
	$.get("",{"countGoods":id},function(data){
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
		$(".pages-goods").html(con);
		$(".pages-goods").find("a").click(function(){window.pagegoods = $(this).text();setGoods($('.event_shop_info[selected=selected]').data('id'));});
	});
	$.get("",{"goods":id,"page":window.pagegoods},function(data){
		jgoodsdata = JSON.parse(data);
		con = "<li class=\"tline\">\
			<div class=\"tline-cell\">Псевдоним</div>\
			<div class=\"tline-cell\">Наименование</div>\
			<div class=\"tline-cell\">Категория</div>\
			<div class=\"tline-cell\">Активен</div>\
			<div class=\"tline-cell\">Когда создан</div>\
			<div class=\"tline-cell\">Рейтинг</div>\
			<div class=\"tline-cell\">Увеличитель рейтинга</div>\
			<div class=\"tline-cell\">Цена</div>\
			<div class=\"tline-cell\">Скидка</div>\
			<div class=\"tline-cell\">Действие</div>\
		</li>";
		for (var i in jgoodsdata) {
			con += "<li class=\"tline event_shop_info\" data-id=\'"+jgoodsdata[i]["id"]+"\'>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["alias"]+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["name"]+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["category"]+"</div>\
				<div class=\"tline-cell\">"+boolToString(jgoodsdata[i]["active"])+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["created"]+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["rating"]+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["rating_booster"]+"</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["price"]+" р.</div>\
				<div class=\"tline-cell\">"+jgoodsdata[i]["discount"]+"%</div>\
				<div class=\"tline-cell center\">\
					<a \
						class=\'btn bc_gray event_edit_good\' \
						data-id=\'"+jgoodsdata[i]["id"]+"\' \
					>Редактировать</a>\
					<a \
						class=\'btn bc_gray event_comment_good\' \
						data-id=\'"+jgoodsdata[i]["id"]+"\' \
					>Комментирии</a>\
					<br>Удалить: <input type=\'checkbox\' name=\'delete[]\' value=\'"+jgoodsdata[i]["id"]+"\'>\
				</div>\
			</li>";
		}
		con += "<input type=\"submit\" value=\"Применить\" class=\"btn bc_gray\">";
		$("#table_content-goods").html(con);
		var editblock = $('#edit_good_block');
		$('.event_edit_good').click(function(){
			$.get("",{"good":id,"good":$(this).data('id')},function(data){
				jgooddata = JSON.parse(data);
				editblock.find('input[name=id]').val(jgooddata['id']);
				editblock.find('input[name=alias]').val(jgooddata['alias']);
				editblock.find('input[name=name]').val(jgooddata['name']);
				editblock.find('input[name=rating]').val(jgooddata['rating_booster']);
				editblock.find('input[name=price]').val(jgooddata['price']);
				editblock.find('input[name=discount]').val(jgooddata['discount']);
				editblock.find("select[name=category]").find("option").removeAttr("selected");
				editblock.find("select[name=category]").find("option[value="+jgooddata['category_id']+"]").attr("selected","true");
	    		editblock.find('.event_images').html('<input type="file" name="images[]">');
				editblock.show();
			});
		});
		$('.event_comment_good').click(function(){
			setComments($(this).data('id'));
		});
		$('#goods').show();
		$('.pages-goods').show();
		$('#btn_add_good').show();
	});
}
function setComments(id) {
	var commentsblock = $('#comments_block');
	$.get("",{"countComments":id},function(data){
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
		$(".pages-comments").html(con);
		$(".pages-comments").find("a").click(function(){window.pagegoods = $(this).text();setComments(id);});
	});
	$.get("",{"comments":id,"page":window.pagecoment},function(data){
		jcomdata = JSON.parse(data);
		var con = "<li class=\"tline\">\
			<div class=\"tline-cell\">Пользователь</div>\
			<div class=\"tline-cell\">Рейтинг</div>\
			<div class=\"tline-cell\">Комментарий</div>\
			<div class=\"tline-cell\">Удалить</div>\
		</li>";
		for (var i in jcomdata) {
			con += "<li class=\"tline\">\
				<div class=\"tline-cell\">"+jcomdata[i]["user"]+"</div>\
				<div class=\"tline-cell\">"+jcomdata[i]["rating"]+"</div>\
				<div class=\"tline-cell\">"+jcomdata[i]["comment"]+"</div>\
				<div class=\"tline-cell\"><input type=\'checkbox\' name=\'delete[]\' value=\'"+jcomdata[i]["id"]+"\'></div>\
			</li>'";
		}
		con += "<input type=\"submit\" value=\"Применить\" class=\"btn bc_gray\">";
		con += "<a class=\"btn bc_gray event_comment_close\" >Закрыть</a>";
		commentsblock.find(".block-body").html(con);
		$('.event_comment_close').click(function(){
			commentsblock.hide();
			$('.pages-comments').hide();
		});
		commentsblock.show();
		$('.pages-comments').show();
	});
}
function boolToString(val) {
	if (val == true) return "Да";
	else return "Нет";
}
$(function($){
	$('#btn_add_shop').click(function(){
		$(this).hide();
		$('#add_block').show();
	});
	$('#close_add_block').click(function(){
		$('#add_block').hide();
		$('#btn_add_shop').show();
	});
	$('#close_edit_block').click(function(){
		$('#edit_block').hide();
	});
	$("#close_info_block").click(function(){
		$(".event_shop_info").each(function(){
			$(this)[0].style.background = "white";
		});
		$(".event_shop_info[selected = selected]").removeAttr("selected");
		$("#info_block").hide();
		$('#goods').hide();
		$('.pages-goods').hide();
		$('#btn_add_good').hide();
		$('#add_good_block').hide();
		$('#edit_good_block').hide();
	});
	$('#btn_add_good').click(function(){
		$('#add_good_block').find('input[name=shop]').val($('.event_shop_info[selected=selected]').data('id'));
		$('#btn_add_good').hide();
		$('#add_good_block').show();
	});
	$('#close_add_good_block').click(function(){
		$('#add_good_block').hide();
		$('#btn_add_good').show();
	});
	$('#close_edit_good_block').click(function(){
		$('#edit_good_block').hide();
	});
	$('.event_add_image').click(function(){
		$(this).parent().find('.event_images').append('<br><input type="file" name="images[]">');
	});
	window.pagecoment = 1;
	window.pagegoods = 1;
	window.pageadmins = 1;
	window.page = 1;
	updateContent();
});
