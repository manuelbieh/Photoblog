function ajaxLoad(link) {

	$.ajax({
		url: link,
		type: 'get',
		data: 'ajax=true',
		success: function(content) {
			$('#content').html(content).css({opacity: 1});
			history.pushState({}, null, link);
		},
		error: function() {
			$('#content').css({opacity: 1});
			$('.contentspinner').remove();
		}

	});

}