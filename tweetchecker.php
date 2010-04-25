<?php
include("class/Database.php");
include("class/dictionary_class.php");
// Init classes
$spellChecker = new dataReader();
$db = new Database();

// Get rates/regions
$employment_stats = $db->query("SELECT * FROM employment_rates");
$pointIndex = 0;

// Loop through regions
while ($stat = mysql_fetch_array($employment_stats))
{
	// Get twitter feed from region (within 50km of centre)
	$twitter = simplexml_load_file('http://search.twitter.com/search.atom?geocode='.$stat['lat'].'%2C'.$stat['long'].'%2C50km');
	
	$regionTweets = "";
	$numTweets = 0;
	
	// Loop through each tweet from region
	foreach ($twitter->entry as $tweet) {
		$newTweet = $tweet->title;
		// Remove @ usernames
	  	$newTweet = preg_replace('/(^|\s)@(\w+)/', '', $newTweet);
	  	// Remove #tags
	  	$newTweet = preg_replace('/(^|\s)#(\w+)/', '', $newTweet);
	  	// Remove numbers
	  	$newTweet = preg_replace('/[0-9]*/', '', $newTweet);
	  	// Remove URLs
	  	$newTweet = preg_replace('((https?|ftp|gopher|telnet|file|notes|ms-help):((//)|(\\\\))+[\w\d:#@%/;$()~_?\+-=\\\.&]*)', '', $newTweet);
	  	// Remove special characters
	  	$newTweet = preg_replace('/[^A-Za-z0-9_\s]/', '', $newTweet);
	  	// Remove RT
	  	$newTweet = preg_replace('/RT./', '', $newTweet);
	  	
	  	// Convert tweet to hash for comparison
	  	$tweetHash = crc32($newTweet);
	  	// Check database for hash
	  	$hashCheck = $db->query("SELECT * FROM tweet_hash WHERE hash LIKE '".$tweetHash."'");
	  	
	  	// If no hash found, tweet has not been checked before
	  	if (mysql_num_rows($hashCheck) == 0)
		{
		  	// Store hash in database
		  	$db->query("INSERT INTO tweet_hash VALUES('','".$tweetHash."')");
		  	// Increase number of checked tweets to calculate average later
		  	$numTweets++;
		  	// Add tweet to string to be spellchecked
		  	$regionTweets = $regionTweets." ".$newTweet;
		}
	}
	
	// If numtweets = 0, all tweets were duplicates so no average should be stored
	if($numTweets > 0)
	{
		// Check spelling and calculate avg number of misspellings
		$misspellings[$pointIndex] = ($spellChecker->reader($regionTweets, false, false)/$numTweets);
		// Add rate into DB
		$query = "INSERT INTO literacy_cache VALUES('','".$pointIndex."','".$misspellings[$pointIndex]."')";
		$db->query($query);
	}
	$pointIndex++;
}
?>