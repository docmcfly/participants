$(function() {
	$.fn.datepicker.dates['de-DE'] = {
		days: ["Sonnta", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
		daysShort: ["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam"],
		daysMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
		months: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
		monthsShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
		today: "Heute",
		clear: "Leer"
	};

	$('.datepicker').datepicker({
		clearBtn: false,
		format: "dd.mm.yyyy",
		language: 'de-DE',
		weekStart: 1,
	});

	$('#timeOutManagement-addTimeOut-from').change(function() {
		let f = $(this).val()
		let u = $('#timeOutManagement-addTimeOut-until').val()
		if (u) {
			let from = f.split('.')
			let until = u.split('.')
			let fromDate = new Date(from[2], from[1] - 1, from[0])
			let untilDate = new Date(until[2], until[1] - 1, until[0])
			if (fromDate > untilDate) {
				$('#timeOutManagement-addTimeOut-until').val(f)
			}
		} else {
			$('#timeOutManagement-addTimeOut-until').val(f)
		}
	})

	$('#timeOutManagement-addTimeOut-until').change(function() {
		let f = $('#timeOutManagement-addTimeOut-from').val()
		let u = $(this).val()
		if (f) {
			let from = f.split('.')
			let until = u.split('.')
			let fromDate = new Date(from[2], from[1] - 1, from[0])
			let untilDate = new Date(until[2], until[1] - 1, until[0])
			if (fromDate > untilDate) {
				$('#timeOutManagement-addTimeOut-from').val(u)
			}
		} else {
			$('#timeOutManagement-addTimeOut-until').val(u)
		}
	})

});