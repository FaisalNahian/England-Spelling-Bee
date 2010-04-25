<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Tweet literacy vs England employment rates mashup</title>
	
	<meta name="Description" content="Compares average misspellings per tweet to employment rates in each region of England." />
	<meta name="Keywords" content="mashup, employment, spelling, literacy, misspelling, england, uk" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
	<link rel="stylesheet" type="text/css" media="all" href="css/smoothness/jquery-ui-1.8.custom.css" />
	
	<?php  
	// Include & init database class
	include("class/Database.php");
	$db = new Database();
	?>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js" type="text/javascript"></script>
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAA3hTyISDDZYuYYHZbvAzVARRWuHjsI_fKh33HkdL49_lf3IujfxRWDIUf5I51T7ZFH7VSGUhQKVotmQ" type="text/javascript"></script>
	
	<script type="text/javascript">
	
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
		$("#dialog").dialog({
			autoOpen: false,
			title: 'More Info',
			resizable: false,
			width: 500,
			modal: true
		});

		$('#opener').click(function() {
			$("#dialog").dialog('open');
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
    			  var marker".$pointIndex." = createMarker(point".$pointIndex.",'<div class=\'balloontext\'><p>Employment Rate in <b>".$stat['place']."</b>: <b>".$rate[$pointIndex]."</b>&#37<br />Avg. # misspellings in this region (all time): <b>".$avgMisspellings[$pointIndex]."</b></p></div>');
    			  gmarkers.push(marker".$pointIndex.");
    			  map.addOverlay(marker".$pointIndex.");";
    			  
    		$pointIndex++;
    	}
        ?>
      }
      // Open a marker window
      openMarker(8);
    }
    </script>
</head>
<body onload="initialize()" onunload="GUnload()">
	<div id="wrapper" style="width: 920px; margin-left: auto; margin-right:auto;">
		<h1>Employment rates in England vs. misspellings in Twitter posts from that region</h1>
		<div id="map_canvas" style="width: 640px; height: 640px; float:left;"></div>	
		
		<div id="info">
			<h1>Regions:</h1>
			<ul>
			<? 
			$pointIndex = 0;
			// 'Rewind' regions data
			mysql_data_seek ($employment_stats, 0);
			while ($stat = mysql_fetch_array($employment_stats))
			{
				// Output marker sidebar link
				echo "<li><a href=\"#\" onclick=\"openMarker(".$pointIndex.")\">".$stat['place']."</a></li>";
				$pointIndex++;
			}
			?>
			</ul>
			<h1>Stats:</h1>
			<?
			// Calculate stats
			// Highest unemployment
			$hiRate = min($rate);
			$hiRateIndex = array_keys($rate, $hiRate);
			// Lowest unemployment
			$loRate = max($rate);
			$loRateIndex = array_keys($rate, $loRate);
			// Most misspellings (all time)
			$hiAllTimeMisspellings = max($avgMisspellings);
			$hiAllTimeMisspellingsIndex = array_keys($avgMisspellings, $hiAllTimeMisspellings);
			// Least misspellings (all time)
			$loAllTimeMisspellings = min($avgMisspellings);
			$loAllTimeMisspellingsIndex = array_keys($avgMisspellings, $loAllTimeMisspellings);
			?>
			<ul>
				<li>Sample size: <b><?=$countMisspellings?></b> tweets</li>
				<li style="padding-top: 10px;">Highest Unemployment: <b><?=(100-$hiRate)?>&#37;</b> in <b><?=$region[$hiRateIndex[0]]?></b></li>
				<li style="padding-top: 4px;">Lowest Unemployment: <b><?=(100-$loRate)?>&#37;</b> in <b><?=$region[$loRateIndex[0]]?></b></li>
				<li style="padding-top: 10px;">Most literate (all time): <b><?=$region[$loAllTimeMisspellingsIndex[0]]?></b> with <b><?=$loAllTimeMisspellings?></b> misspellings</li>
				<li style="padding-top: 4px;">Least literate (all time): <b><?=$region[$hiAllTimeMisspellingsIndex[0]]?></b> with <b><?=$hiAllTimeMisspellings?></b> misspellings</li>
			</ul>
			<p id="tweetCheckStatus" style="padding-bottom: 0; margin-bottom: 0;">
				<img src="images/ajax-loader.gif" style="vertical-align: middle;" alt="Loading... " width="16" height="16" style="padding-right: 5px;" />
				Your result is currently being saved for the next visitor.
			</p>
			<p style="padding-top: 0; margin-top: 0; text-align: right;"><a href="#" id="opener">More info / Why?</a></p>
			<script type="text/javascript">
			// Call tweet checker
			$.ajax({
			   type: "POST",
			   url: "tweetchecker.php",
			   success: function(msg){
			     $("#tweetCheckStatus").html("<img src=\"images/add_16.png\" style=\"vertical-align: middle;\" alt=\"add_16\" width=\"16\" height=\"16\" /> Result saved.");
			   }
			});
			</script>
			
		</div>
	</div>
	<div id="dialog"><p><b>What's being saved?</b></p><p>Spell checking large numbers of tweets is very slow in PHP - it takes 20-30 seconds to finish analysing the data. In order to make this mashup as quick and easy as possible, the results you see are generated by the last visitor - The data you are viewing now is not 'live'.</p> <p>While you use the mashup, the app is generating new set of 'live' results in the background, which the next visitor will see. This way, the page loads instantly and the analysation is seamlessly prepared without the long load time affecting you.</p><p><b>What' the hypothesis?</b></p><p>Null, nada, no point to make. it's just a fun idea! I have a feeling, just from watching the stats change during development, that the 'all time' averages will tend to the same rate over time.</p><p><b>How does it work?</b></p><p>The app fetches the last 25 geocoded tweets (ie, from smartphones<sup>1</sup>) from each region on the map. These tweets are stripped of a few things to increase accuracy: @usernames, #hashtags, RT:, URLs and non-alphanumeric characters. These tweets are hashed and the database is checked to see if the tweet has been checked before (to prevent inaccurate results from slow-moving Twitter data). The app then sticks all the Tweets from the region into a big long string and runs a hash based dictionary check on them.</p><p>This data is stored on each run for the reasons above, and also to calculate a better average over time - 25 Tweets is a very small sample size, but after a number of visits this sample size increases to a more acceptable standard. The app currently shows the 'live' average and the stored average separately, but these may be combined in the future.</p><p>If you have any questions, tweet me <a target="_blank" href="http://twitter.com/home?status=@lewiji ">@lewiji</a>!</p>
		 <p class="footnote">1. Note that this may skew the data somewhat, as smartphones usually have built in spell-checking or auto-correction. When I can figure out a good way of getting tweets without using geocoding, I'll implement it!</p>
	</div>
	<div id="footer">
		© Copyright 2010 <a href="http://www.lewspollard.com/">Lewis Pollard</a> for <a href="http://www.shu.ac.uk">Sheffield Hallam University</a>.
	</div>
</body>
</html>