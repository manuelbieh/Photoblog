$(window).on('popstate', function(event) {

	$('ul#menu li ul li').removeClass('active');

	$.ajax({

		url: window.location,
		type: 'get',
		data: 'ajax=true',
		success: function(content) {

			$('#content').html(content).css({opacity: 1});
			$('ul#menu li ul li').removeClass('active');
			$('#' + event.originalEvent.state.active).addClass('active');
			$('.contentspinner').remove();

		},

		error: function() {
			$('#content').css({opacity: 1});
			$('.contentspinner').remove();
		}

	});

});

$(function() {

	// fold inactive menu items
	$('ul#menu li:not(.active) ul').hide();

	// prevent links whose parent lis have nested lists
	$('ul#menu > li:has(> ul) > a').bind('click', function(e) {
		e.preventDefault();
		$(this).parent().find('ul').slideToggle('fast');
	});

	$('#sidebar .resizebar').bind('click', function() {

		if($(this).parent().hasClass('closed')) {
			$('#content').stop().animate({left: '300px'});
			$('#sidebar').stop().animate({width: '300px'});
		} else {
			$('#sidebar').stop().animate({width: '8px'});
			$('#content').stop().animate({left: '8px'});
		}

		$(this).parent().toggleClass('closed');

	});

	// Awesome ajax menu load stuff. Currently doesnt work with forms as good as it could
	$('ul#menu li ul li a').bind('click', function(e) {

		e.preventDefault();

		$('#content').css({opacity: 0.3});

		$('body').append('<div class="contentspinner"></div>');

		var $$ = $(this);
		$.ajax({
			url: $$.attr('href'),
			type: 'get',
			data: 'ajax=true',
			success: function(content) {

				$('#content').html(content).css({opacity: 1});
				$('ul#menu li ul li').removeClass('active');
				$$.parent('li').addClass('active');

				var generateTempId = function() {
					return 'item_' + Math.ceil(Math.random() * 12000);
				}

				var activeItem = $('ul#menu li ul li.active');

				if(!activeItem.attr('id')) {

					var tempId = generateTempId();

					while($('#' + tempId).length) {
						tempId = generateTempId();
					}
					activeItem.attr('id', tempId);

				}
				history.pushState({active: tempId, text: activeItem.text()}, null, $$.attr('href'));

				$('.contentspinner').remove();
				console.log(history);
			},
			error: function() {
				$('#content').css({opacity: 1});
				$('.contentspinner').remove();
			}
		});

		return false;

	});

});

function ajaxLoad(link) {

	$.ajax({
		url: link,
		type: 'get',
		data: 'ajax=true',
		success: function(content) {

			$('#content').html(content).css({opacity: 1});

			var generateTempId = function() {
				return 'item_' + Math.ceil(Math.random() * 12000);
			}

			var activeItem = $('ul#menu li ul li.active');

			if(!activeItem.attr('id')) {

				var tempId = generateTempId();

				while($('#' + tempId).length) {
					tempId = generateTempId();
				}
				activeItem.attr('id', tempId);

			}

			history.pushState({active: tempId, text: activeItem.text()}, null, link);

		},

		error: function() {
			$('#content').css({opacity: 1});
			$('.contentspinner').remove();
		}

	});

}
