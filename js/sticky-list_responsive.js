jQuery(document).ready(function($) {
	$('.sticky-list td.sticky-action').attr('data-th', $('.sticky-list th.sticky-action').text());
	$('.sticky-list th').each(function(index, el) {
		data = $(this).data('sort');
		if (typeof data !== "undefined") {
			$('.' + data).attr('data-th', $(this).text());
		}
	});
});