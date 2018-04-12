$(document).ready(function() {

	var $window = $(window)

	// make code pretty
	window.prettyPrint && prettyPrint();

	$('.js-copy').click(function(e) {
		e.preventDefault();
		var el = $(this);
		var text = el.closest('.card').find('.data-to-copy');
		dataToCopy = (typeof text == 'undefined') ? text.val() : text.text();
		copyToClipboard(dataToCopy, el);
	});

	$('#file-headers').on('change', function () {
		if($('.alert-danger').length > 0) {
			$('.alert-danger').slideUp();
		}
		$('.custom-file-label').text(this.value.replace(/.*[\/\\]/, ''));
	});
});