function setEvents(data) {
	$(data).find('.event_button_hide').click(function(){
		$($(this).data("element")).hide();
	});
	$(data).find('.event_button_show').click(function(){
		$($(this).data("element")).show();
	});
	$(data).find('.event_btn_collapse').click(function(){
		block = $($(this).attr("collapce-target"));
		if (block.hasClass('collapsed')) {
			block.removeClass('collapsed');
		} else {
			block.addClass('collapsed');
		}
	});
	$(data).find('.event_btn_hide').click(function(){
		block = $($(this).attr("hide-target"));
		if (block.hasClass('hidden')) {
			block.removeClass('hidden');
		} else {
			block.addClass('hidden');
		}
	});
	$(data).find('.event__data_transfer').click(function(){
		alias = $(this).attr("alias");
		datakey = $(this).attr("data-key");
		$(".data_transfer__"+alias+"__"+datakey).each(function(index,element){
			target = $(".field__data_transfer__"+alias+"__"+$(element).attr("alias"));
			if (target.is("input") && target.attr("type") == "checkbox") {
				if ($(element).text() == "checked" || $(element).text() == "true")
					target.attr("checked","y");
				else
					target.removeAttr("checked");
				target.removeAttr("value");
			} else
				target.val($(element).html());
		});
	});
	$(data).find('.event__content_ajax').click(function(){
		alias = $(this).attr("content_ajax__alias");
		data = {
			"event":$(this).attr("content_ajax__alias"),
			"param":$(this).attr("content_ajax__param")
		}
		href = $(this).attr("content_ajax__href");
		if (href == undefined) href = "";
		$.post(href,data,function(data){
			$("#content_ajax__"+alias).html(data);
			setEvents($("#content_ajax__"+alias));
		});
	});
	$(data).find('.event__data_list').on("input",function(){
		data = {};
		data[$(this).attr("list")] = $(this).val();
		list = $('#'+$(this).attr("list"));
		list.empty();
		$.ajax({
			url: "",
			type: "POST",
			data: data,
			async: false,
			success : function(data) {
				jdata = JSON.parse(data);
				$.each(jdata,function(i,item){
					list.append('<option>'+ item +'</option>');
				});
			}
		});
	});
}
$(function(){
	setEvents(document);
});
