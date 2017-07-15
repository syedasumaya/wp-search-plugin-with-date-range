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

	// Works if data is received in the search field
	include_once('../../../../wp-load.php');
	if (!$BDDquery = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
		echo 'Can not connect to MySQL';
		exit;
	}
	if (!mysql_select_db(DB_NAME, $BDDquery)) {
		echo 'Database Selection impossible!';
	}

    // Search query in the inverted index (basic self-generated keywords)
    $requeteSQL = "SELECT DISTINCT ".$field." FROM ".$table." WHERE ".$field." LIKE '".$arg.mysql_real_escape_string($query)."%' ORDER BY ".$field." ASC, idindex DESC LIMIT 0 , ".$limitS."";
	
	// Launch the application
    $results = mysql_query($requeteSQL) or die("Error : ".mysql_error());
    
	// Return results with the auto completion system
    while($donnees = mysql_fetch_assoc($results)) {
        if($encode == "utf-8" || $encode == "utf8" || $encode == "UTF-8" || $encode == "UTF8") {
			echo $donnees[$field]."\n";
		} else {
			echo $donnees[$field]."\n";	
		}
    }
}
?>