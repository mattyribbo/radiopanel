<?php
// RadioPanel - Generic Functions
// (C) Matt Ribbins - matt@mattyribbo.co.uk


// API Calls
function api_call() {
	$task = $_GET['task'];
	switch($task) {
		case "html_live_stats":
			stream_getlivestats();
			break;
		case "html_display_date":
			stats_search_display();
			break;
		default:
			echo "Nope.avi";
			
	}
}


?>