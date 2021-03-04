//Fix form toggle
Nette.toggle = function (id, visible) {
	var el = $('#' + id);
	if (visible) {
		el.show();
	} else {
		el.hide();
	}
};

Nette.showFormErrors = function(form, errors){
	for (let id = 0; id < errors.length; id++){
		let element = errors[id].element;
		let message = errors[id].message;

		$(element).addClass('is-invalid');
		//$(element).parent('div.form-group').children('div.invalid-feedback').html(message);
	}

	return !1;
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