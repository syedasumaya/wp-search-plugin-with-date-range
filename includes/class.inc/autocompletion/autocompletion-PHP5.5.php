<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(isset($_GET['q']) && !empty($_GET['q'])) {
	$query = htmlspecialchars(stripslashes($_GET['q']));

	// Recovery on the fly on information provided by the script autocompletion
	$table	 = htmlspecialchars($_GET['t']);
	$field	 = htmlspecialchars($_GET['f']);
	$type	 = htmlspecialchars($_GET['type']);
	$encode	 = htmlspecialchars($_GET['e']);

	if(is_numeric($_GET['l'])) {
		$limitS  = htmlspecialchars($_GET['l']);
	} else {
		$limitS = 5;	
	}
	
	if($type == 0 || $type > 1) {
		$arg = "";
	} else {
		$arg = "%";	
	}

	// Connecting to the database PHP 5.5 -> Required !!!
	// Works if data is received in the search field
	include_once('../../../../../wp-load.php');
	$link = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	// In case of error
	if (mysqli_connect_error()) {
		die('Can not connect to MySQL ('.mysqli_connect_errno().') : '.mysqli_connect_error());
	}
	
    // Search query in the inverted index (basic self-generated keywords)
    $requeteSQL = "SELECT DISTINCT ".$field." FROM ".$table." WHERE ".$field." LIKE '".$arg.$link->real_escape_string($query)."%' ORDER BY ".$field." ASC, idindex DESC LIMIT 0 , ".$limitS."";
    
	// Launch the application
    $results = $link->query($requeteSQL) or die("Error : ".$link->error);
    
	// Return results with the auto completion system
    while($donnees = mysqli_fetch_assoc($results)) {
		$mots = $donnees[$field];
		if(preg_match("#([ ]+)#", $mots)) {
			$mots = '"'.$mots.'"';
		}
	
        if($encode == "utf-8" || $encode == "utf8" || $encode == "UTF-8" || $encode == "UTF8") {
			echo utf8_encode($mots)."\n";
		} else {
			echo $mots."\n";	
		}
    }
}
?>