// Global array of markers for sidebar marker links 
var gmarkers = [];

// Function to create markers
function createMarker(point,html) {
    var marker = new GMarker(point);
    GEvent.addListener(marker, "click", function() {
      marker.openInfoWindowHtml(html);
    });
    return marker;
}

// Function to allow sidebar links to open marker info
function openMarker(i) {
  GEvent.trigger(gmarkers[i], "click");
}

// Prepare modal dialog for 'more info' button
$(document).ready(function() {
var $dialog = $('<div></div>')
	.html('<p><b>What\'s being saved?</b></p><p>Spell checking large numbers of tweets is very slow in PHP - it takes 20-30 seconds to finish analysing the data. In order to make this mashup as quick and easy as possible, the results you see are generated by the last visitor - The data you are viewing now is not \'live\'.</p> <p>While you use the mashup, the app is generating new set of \'live\' results in the background, which the next visitor will see. This way, the page loads instantly and the analysation is seamlessly prepared without the long load time affecting you.</p><p><b>What\' the hypothesis?</b></p><p>Null, nada, no point to make. it\'s just a fun idea! I have a feeling, just from watching the stats change during development, that the \'all time\' averages will tend to 2.5 over time.</p><p><b>How does it work?</b></p><p>The app fetches the last 25 geocoded tweets (ie, from smartphones) from each region on the map. These tweets are stripped of a few things to increase accuracy: @usernames, #hashtags, RT:, URLs and non-alphanumeric characters. These tweets are hashed and the database is checked to see if the tweet has been checked before (to prevent inaccurate results from slow-moving Twitter data). The app then sticks all the Tweets from the region into a big long string and runs a hash based dictionary check on them.</p><p>This data is stored on each run for the reasons above, and also to calculate a better average over time - 25 Tweets is a very small sample size, but after a number of visits this sample size increases to a more acceptable standard. The app currently shows the \'live\' average and the stored average separately, but these may be combined in the future.</p><p>If you have any questions, tweet me <a target="_blank" href="http://twitter.com/home?status=@lewiji ">@lewiji</a>!</p>')
	.dialog({
		autoOpen: false,
		title: 'More Info',
		resizable: false,
		width: 500,
		modal: true
	});

	$('#opener').click(function() {
		$dialog.dialog('open');
	});
});

// Init map
function initialize() {
  if (GBrowserIsCompatible()) {
    var map = new GMap2(document.getElementById("map_canvas"));
    // Zoom map to England
    map.setCenter(new GLatLng(53.371806, -1.475583), 6);
    // Add UI Controls
    map.setUIToDefault();
    
    <?		
	// Index used to store results for each region in array
	$pointIndex = 0;
	
	// Get sample size count from hashes
	$countMisspellings = mysql_result($db->query("SELECT COUNT(hash) FROM tweet_hash"), 0, 'COUNT(hash)');
	
	// Get employment rates / regions
	$employment_stats = $db->query("SELECT * FROM employment_rates");
	
	// Loop through regions
	while ($stat = mysql_fetch_array($employment_stats))
	{   
		// Get average mispellings for this region from db
		$allTimeMisspellings = $db->query("SELECT AVG(rate) FROM literacy_cache WHERE region='".$pointIndex."'");
	    
	    // Get average mispellings for this region
		$avgMisspellings[$pointIndex] = round(mysql_result($allTimeMisspellings, 0, 'AVG(rate)'), 2);
		
		// Store rate and region for use on info bar
		$rate[$pointIndex] = $stat['rate'];
		$region[$pointIndex] = $stat['place'];
			
		// Echo the markers & popup text
		echo "var point".$pointIndex." = new GLatLng(".$stat['lat'].",".$stat['long'].");
			  var marker".$pointIndex." = createMarker(point".$pointIndex.",'<div class=\'balloontext\'><p>Employment Rate in <b>".$stat['place']."</b>: <b>".$rate[$pointIndex]."</b>&#37<br />Avg. misspellings per tweet (all time): <b>".$avgMisspellings[$pointIndex]."</b></p></div>');
			  gmarkers.push(marker".$pointIndex.");
			  map.addOverlay(marker".$pointIndex.");";
			  
		$pointIndex++;
	}
    ?>
  }
  // Open a marker window
  openMarker(8);
}