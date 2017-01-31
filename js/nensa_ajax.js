jQuery(document).ready(function($) {
//

	$('#import_button').click(function() {

		event_select = $('#event_select').val();
		file = $('#results_file').prop('files')[0]; 

		var data = {
			'action': 'import_results',
			'season': ajax_object.season,
			'event_select': event_select,
			'file': file,
		};
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
			alert('Season: ' + response);
		});
	});

});