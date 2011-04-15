(function() {
	if (!("console" in window) || !("firebug" in console)) {
		var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
		window.console = {};
		for (var i = 0; i < names.length; ++i) window.console[names[i]] = function() { };
	}
})();

$(function() {
	// Auto-hide informational messages
	$('.message.autohide').delay(2000).fadeOut('slow', function() { $(this).remove(); });

	// Show user's location on Google Maps in account view
	$('#account-form-map').each(function() {
		var self = $(this);
	    var position = new google.maps.LatLng(self.data('lat'), self.data('lng'));
	    var options = {
	    	zoom: 10,
	    	center: position,
	    	mapTypeId: google.maps.MapTypeId.ROADMAP
	    };
	  	var map = new google.maps.Map(this, options);
	  	var marker = new google.maps.Marker({
	        position: position, 
	        map: map,
	        title: $('#city').val()
	    });
	});

});