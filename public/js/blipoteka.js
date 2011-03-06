(function() {
	if (!("console" in window) || !("firebug" in console)) {
		var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml", "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
		window.console = {};
		for (var i = 0; i < names.length; ++i) window.console[names[i]] = function() { };
	}
})();

$(function() {
	// Auto-hide informational messages
	$('.message.autohide').delay(2000).fadeOut('slow', function() { $(this).remove() });
});