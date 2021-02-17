//Fix form toggle
Nette.toggle = function (id, visible) {
	var el = $('#' + id);
	if (visible) {
		el.show();
	} else {
		el.hide();
	}
};

//Form elements
$(function () {
	/*$('.select2').select2({
		theme: 'bootstrap4'
	});

	$('.datepicker').datepicker({
		format: 'dd.mm.yyyy',
		autoclose: true,
		language: 'cs',
	});

	$('.datepickerSmall').datepicker({
		format: 'dd.mm.',
		autoclose: true,
		language: 'cs',
	});*/

	//Nette ajax init
	$.nette.init();
});