jQuery(document).ready(function($) {
	var data = {
		'action': 'jn_select',
		'season': ajax_object.season      // We pass php values differently!
	};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		alert('Season: ' + response);
	});
});