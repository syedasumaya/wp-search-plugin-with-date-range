<?php
/*--------------------------------------------*/
/*--------- Auto completion function ---------*/
/*--------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Adding auto completion of the conditioning system
function Fancy_Search_addAutoCompletion() {
	global $wpdb, $table_Fancy_Search, $link;
	
	// Selecting data in the database
	$select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));

	// Useful variable instantiation
	$selector		= $select->autoCompleteSelector;
	$dbName			= $select->db;
	$tableName		= $select->autoCompleteTable;
	$tableColumn	= $select->autoCompleteColumn;
	$limitDisplay	= $select->autoCompleteNumber;
	$multiple		= $select->autoCompleteTypeSuggest;
	$type			= $select->autoCompleteType;
	$autoFocus		= $select->autoCompleteAutofocus;
	$create			= false; // Is allowed to false because the table is created otherwise
	$encoding		= $select->encoding;
	
	// Starting the auto completion feature if activated ...
	if($select->autoCompleteActive == 1) {
		include_once('class.inc/moteur-php5.5.class-inc.php');
		$autocompletion = new autoCompletion($wpdb, FANCY_URL."/includes/class.inc/autocompletion/autocompletion-PHP5.5.php", $selector, $tableName, $tableColumn, $multiple, $limitDisplay, $type, $autoFocus, $create, $encoding);
	}
}
add_action('wp_footer', 'Fancy_Search_addAutoCompletion');

// Adding conditioning of auto completion file
function Fancy_Search_AutoCompletion() {
	$urlstyle = FANCY_URL.'/includes/class.inc/autocompletion/jquery.autocomplete.css';
	wp_enqueue_style('autocomplete', $urlstyle, false, '1.0');
        wp_enqueue_style('datepicker', "https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css", false, '1.12.1');
	$url = FANCY_URL.'/includes/class.inc/autocompletion/jquery.autocomplete.js';
	wp_enqueue_script('autocomplete', $url, array('jquery'), '1.0');
        wp_enqueue_script( 'datepicker', "https://code.jquery.com/ui/1.12.1/jquery-ui.js" , array('jquery'), '1.12.1' );
}
add_action('wp_enqueue_scripts', 'Fancy_Search_AutoCompletion');


/*--------------------------------------------*/
/*-------- Trigger and scroll function --------*/
/*--------------------------------------------*/

add_action('wp_footer', 'FS_Trigger_Scroll');
function FS_Trigger_Scroll() {
	global $wpdb, $table_Fancy_Search,$select;

    	$scriptData = array(
            'paginationActive'  =>  $select->paginationActive,
            'paginationType'    =>  $select->paginationType
	    );
    wp_localize_script( 'fs-custom', 'Trigger_Scroll', $scriptData ) ;

}

?>