function initPNavigator(perPage,bodyPages,bodyContent) {
	bodyContent = $('.'+bodyContent);
	bodyPages = $('.'+bodyPages);
	maxPages = colPages(perPage,bodyContent.length);
	if (maxPages > 1) {
		for (i=1;i<=maxPages;i++) {
			bodyPages.append('<a class="btn">'+i+'</a>');
		}
	}
	bodyPages.find("a").click(function(){
		filtrade($(this).text(),perPage,bodyContent);
	});
	filtrade(1,perPage,bodyContent);
}
function filtrade(currentPage, perPage, bodyContent) {
	bodyContent.hide();
	end = Number(currentPage) * perPage;
	start = end - perPage;
	bodyContent.each(function(index){
		if (index>=start && index<end) $(this).show();
	});
}
function colPages(perPage,maxPage) {
	col = 1;
	p = 0;
	for (i=1;i<=maxPage;i++) {
		if (p==perPage) {
			p=0;
			col++;
			continue;
		}
		p++;
	}
	return col;
}