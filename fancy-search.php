<?php
/*
  Plugin Name: Fancy-Search
  Plugin URI: https://www.templaza.com/tz_membership/download/8-wordpress-plugin.html
  Description: Fancy search engine for WordPress instead of the original engine (highlighted three search types, styles, pagination, optional relevancy algorithm ...). (<em> plugin adds a fancy search engine for WordPress with a lot of options (three types of search, bloded request, three method for pagination, relevancy algorithm ... </ em>).
  Author: vanduy99, Templaza, tuyennv
  Version: 1.0.0
  Author URI: http://www.templaza.com/
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

    
// Instantiation global variables
global $wpdb, $table_Fancy_Search, $Fancy_Search_Version;
$table_Fancy_Search = $wpdb->prefix . 'fancysearch';


// Version plugin
$Fancy_Search_Version = "1.0.0";

// Prefix
define('FANCY_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('FANCY_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
// Inclusion of setting options
include('includes/fancy-search-options.php');
// Inclusion of style options
//include_once('Fancy-Search-Styles.php');
// Inclusion of the options for auto completion
include_once('includes/fancy-search-addons.php');
// Inclusion of documentation options
include_once('includes/fancy-search-documentation.php');
// Inclusion of the final function
include_once('includes/fancy-search-function.php');
// Inclusion of useful files (trigger, infinite scroll and auto completion)
include('includes/fancy-search-includes.php');

// Check add-ons install
$dir = '' . FANCY_PATH . '/add-ons';
$folders = scandir($dir);
$countdir = count($folders);
if ($countdir > 2) {
    $i = 1;
    foreach ($folders as $folder) {
        if ($i > 2) {
            $addons_file = FANCY_PATH . '/add-ons/' . $folder . '/fancy-search-' . $folder . '.php';
            if (file_exists($addons_file)) {
                include( 'add-ons/' . $folder . '/fancy-search-' . $folder . '.php');
            }
        }
        $i++;
    }
}

/* 	Text Domain	 */

function Fancy_Search_Lang() {
    load_plugin_textdomain('fancy-search', false, false);
}

add_action('plugins_loaded', 'Fancy_Search_Lang');

// Function launched during activation or deactivation of the extension
register_activation_hook(__FILE__, 'Fancy_Search_install');
register_activation_hook(__FILE__, 'Fancy_Search_install_data');
register_deactivation_hook(__FILE__, 'Fancy_Search_desinstall');

function Fancy_Search_install() {
    global $wpdb, $table_Fancy_Search, $Fancy_Search_Version;

    // Creation of the database table
    $sql = "CREATE TABLE IF NOT EXISTS $table_Fancy_Search (
		id INT(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		db VARCHAR(50) NOT NULL,
		tablesPost VARCHAR(30) NOT NULL,
		tablesPage VARCHAR(30) NOT NULL,
		nameField VARCHAR(30) NOT NULL,
		colonnesWherePost TEXT NOT NULL,
		colonnesWherePage TEXT NOT NULL,
		typeSearch VARCHAR(8) NOT NULL,
		encoding VARCHAR(25) NOT NULL,
		typeResults VARCHAR(12) NOT NULL,
		addonsActive VARCHAR(30) NOT NULL,
		exactSearch BOOLEAN NOT NULL,
		accents BOOLEAN NOT NULL,
		exclusionWords TEXT,
		nbResultsOK BOOLEAN NOT NULL,
		NumberOK BOOLEAN NOT NULL,
		NumberPerPage INT(2),
		idform VARCHAR(200),
		placeholder VARCHAR(200),
		Style VARCHAR(10) NOT NULL,
		formatageDate VARCHAR(25),
		DateOK BOOLEAN NOT NULL,
		AuthorOK BOOLEAN NOT NULL,
		CategoryOK BOOLEAN NOT NULL,
		TitleOK BOOLEAN NOT NULL,
		ArticleOK VARCHAR(12) NOT NULL,
		CommentOK BOOLEAN NOT NULL,
		ImageOK BOOLEAN NOT NULL,
		BlocOrder VARCHAR(10) NOT NULL,
		strongWords VARCHAR(10) NOT NULL,
		OrderOKPost BOOLEAN NOT NULL,
		OrderOKPage BOOLEAN NOT NULL,
		OrderColumnPost VARCHAR(25) NOT NULL,
		OrderColumnPage VARCHAR(25) NOT NULL,
		AscDescPost VARCHAR(4) NOT NULL,
		AscDescPage VARCHAR(4) NOT NULL,
		AlgoOK BOOLEAN NOT NULL,
		paginationActive BOOLEAN NOT NULL,
		paginationStyle VARCHAR(30) NOT NULL,
		paginationFirstLast BOOLEAN NOT NULL,
		paginationPrevNext BOOLEAN NOT NULL,
		paginationFirstPage VARCHAR(50) NOT NULL,
		paginationLastPage VARCHAR(50) NOT NULL,
		paginationPrevText VARCHAR(50) NOT NULL,
		paginationNextText VARCHAR(50) NOT NULL,
		paginationType VARCHAR(50) NOT NULL,
		paginationText VARCHAR(250) NOT NULL,
		postType VARCHAR(8) NOT NULL,
		categories TEXT,
		ResultText TEXT,
		ErrorText TEXT,
		autoCompleteActive BOOLEAN NOT NULL,
		autoCompleteSelector VARCHAR(50) NOT NULL,
		autoCompleteAutofocus BOOLEAN NOT NULL,
		autoCompleteType TINYINT,
		autoCompleteNumber TINYINT,
		autoCompleteTypeSuggest BOOLEAN NOT NULL,
		autoCompleteCreate BOOLEAN NOT NULL,
		autoCompleteTable VARCHAR(50) NOT NULL,
		autoCompleteColumn VARCHAR(50) NOT NULL,
		autoCompleteGenerate BOOLEAN NOT NULL,
		autoCompleteSizeMin TINYINT,
		autoCorrectActive BOOLEAN NOT NULL,
		autoCorrectType TINYINT,
		autoCorrectMethod BOOLEAN NOT NULL,
		autoCorrectString TEXT NOT NULL,
		autoCorrectCreate BOOLEAN NOT NULL
		) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Taking account of the current version
    add_option("Fancy_Search_version", $Fancy_Search_Version);
}

function Fancy_Search_install_data() {
    global $wpdb, $table_Fancy_Search;
    // ASR name of the database
    $databaseNameSearch = $wpdb->get_results($wpdb->prepare("SELECT DATABASE()", 'foo'));
    foreach ($databaseNameSearch[0] as $databaseSearch) {
        // Inserting default values (first registration)
        $defaut = array(
            "db" => $databaseSearch,
            "tablesPost" => $wpdb->posts,
            "tablesPage" => $wpdb->posts,
            "nameField" => 's',
            "colonnesWherePost" => 'post_title, post_content, post_excerpt, post_date',
            "colonnesWherePage" => 'post_title, post_content, post_excerpt,post_date', 
            "typeSearch" => "REGEXP",
            "encoding" => "utf-8",
            "typeResults" => "searchpage",
            "addonsActive" => "addonpost",
            "exactSearch" => true,
            "accents" => false,
            "exclusionWords" => 1,
            "nbResultsOK" => true,
            "NumberOK" => true,
            "NumberPerPage" => 12,
            "idform" => 's',
            "placeholder" => '',
            "Style" => 'yes',
            "formatageDate" => 'j F Y',
            "DateOK" => true,
            "AuthorOK" => true,
            "CategoryOK" => true,
            "TitleOK" => true,
            "ArticleOK" => 'excerpt',
            "CommentOK" => true,
            "ImageOK" => true,
            "BlocOrder" => "D-A-C",
            "strongWords" => "exact",
            "OrderOKPost" => true,
            "OrderOKPage" => true,
            "OrderColumnPost" => 'post_date',
            "OrderColumnPage" => 'post_date',
            "AscDescPost" => 'DESC',
            "AscDescPage" => 'DESC',
            "AlgoOK" => false,
            "paginationActive" => true,
            "paginationStyle" => "no",
            "paginationFirstLast" => true,
            "paginationPrevNext" => true,
            "paginationFirstPage" => "First page",
            "paginationLastPage" => "Last page",
            "paginationPrevText" => "&laquo; Prev",
            "paginationNextText" => "Next &raquo;",
            "paginationType" => "trigger",
            "paginationText" => __('Load More', 'fancy-search'),
            "postType" => 'post',
            "categories" => serialize(array('all')),
            "ResultText" => __('Search Results :', 'fancy-search'),
            "ErrorText" => __('No results, please make another search !', 'fancy-search'),
            "autoCompleteActive" => false,
            "autoCompleteSelector" => ".search-field",
            "autoCompleteAutofocus" => false,
            "autoCompleteType" => 0,
            "autoCompleteNumber" => 5,
            "autoCompleteTypeSuggest" => true,
            "autoCompleteCreate" => false,
            "autoCompleteTable" => $wpdb->prefix . "autosuggest",
            "autoCompleteColumn" => "words",
            "autoCompleteGenerate" => true,
            "autoCompleteSizeMin" => 2,
            "autoCorrectActive" => true,
            "autoCorrectType" => 2,
            "autoCorrectMethod" => true,
            "autoCorrectString" => __('Try with another spelling : ', 'fancy-search'),
            "autoCorrectCreate" => false
        );
        $champ = wp_parse_args($instance, $defaut);
        $default = $wpdb->insert($table_Fancy_Search, array('db' => $champ['db'], 'tablesPost' => $champ['tablesPost'], 'tablesPage' => $champ['tablesPage'], 'nameField' => $champ['nameField'], 'colonnesWherePost' => $champ['colonnesWherePost'], 'colonnesWherePage' => $champ['colonnesWherePage'], 'typeSearch' => $champ['typeSearch'], 'encoding' => $champ['encoding'], 'typeResults' => $champ['typeResults'], 'addonsActive' => $champ['addonsActive'], 'exactSearch' => $champ['exactSearch'], 'accents' => $champ['accents'], 'exclusionWords' => $champ['exclusionWords'], 'nbResultsOK' => $champ['nbResultsOK'], 'NumberOK' => $champ['NumberOK'], 'NumberPerPage' => $champ['NumberPerPage'], 'idform' => $champ['idform'], 'placeholder' => $champ['placeholder'], 'Style' => $champ['Style'], 'formatageDate' => $champ['formatageDate'], 'DateOK' => $champ['DateOK'], 'AuthorOK' => $champ['AuthorOK'], 'CategoryOK' => $champ['CategoryOK'], 'TitleOK' => $champ['TitleOK'], 'ArticleOK' => $champ['ArticleOK'], 'CommentOK' => $champ['CommentOK'], 'ImageOK' => $champ['ImageOK'], 'BlocOrder' => $champ['BlocOrder'], 'strongWords' => $champ['strongWords'], 'OrderOKPost' => $champ['OrderOKPost'], 'OrderOKPage' => $champ['OrderOKPage'], 'OrderColumnPost' => $champ['OrderColumnPost'], 'OrderColumnPage' => $champ['OrderColumnPage'], 'AscDescPost' => $champ['AscDescPost'], 'AscDescPage' => $champ['AscDescPage'], 'AlgoOK' => $champ['AlgoOK'], 'paginationActive' => $champ['paginationActive'], 'paginationStyle' => $champ['paginationStyle'], 'paginationFirstLast' => $champ['paginationFirstLast'], 'paginationPrevNext' => $champ['paginationPrevNext'], 'paginationFirstPage' => $champ['paginationFirstPage'], 'paginationLastPage' => $champ['paginationLastPage'], 'paginationPrevText' => $champ['paginationPrevText'], 'paginationNextText' => $champ['paginationNextText'], 'paginationType' => $champ['paginationType'], 'paginationText' => $champ['paginationText'], 'postType' => $champ['postType'], 'categories' => $champ['categories'], 'ResultText' => $champ['ResultText'], 'ErrorText' => $champ['ErrorText'], 'autoCompleteActive' => $champ['autoCompleteActive'], 'autoCompleteSelector' => $champ['autoCompleteSelector'], 'autoCompleteAutofocus' => $champ['autoCompleteAutofocus'], 'autoCompleteType' => $champ['autoCompleteType'], 'autoCompleteNumber' => $champ['autoCompleteNumber'], 'autoCompleteCreate' => $champ['autoCompleteCreate'], 'autoCompleteTable' => $champ['autoCompleteTable'], 'autoCompleteColumn' => $champ['autoCompleteColumn'], 'autoCompleteTypeSuggest' => $champ['autoCompleteTypeSuggest'], 'autoCompleteGenerate' => $champ['autoCompleteGenerate'], 'autoCompleteSizeMin' => $champ['autoCompleteSizeMin'], 'autoCorrectActive' => $champ['autoCorrectActive'], 'autoCorrectType' => $champ['autoCorrectType'], 'autoCorrectMethod' => $champ['autoCorrectMethod'], 'autoCorrectString' => $champ['autoCorrectString'], 'autoCorrectCreate' => $champ['autoCorrectCreate']));

        // Creating the default inverted index (for auto completion)
        $wpdb->query(
                $wpdb->prepare("CREATE TABLE IF NOT EXISTS " . $champ['autoCompleteTable'] . " (
					idindex INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					" . $champ['autoCompleteColumn'] . " VARCHAR(250) NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", 'foo')
        );
    }
}

// When that disable the extension, the table is deleted ...
function Fancy_Search_desinstall() {
    global $wpdb, $table_Fancy_Search;

    // Selects the table data
    $select = $wpdb->get_row($wpdb->prepare("SELECT autoCompleteTable FROM $table_Fancy_Search WHERE id=1", 'foo'));

    // Remove reversed if the existing index
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS " . $select->autoCompleteTable, 'foo'));

    // Remove the base table
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS $table_Fancy_Search", 'foo'));
}

// When the plugin is updated, it boosts the function
function Fancy_Search_Upgrade() {
    global $Fancy_Search_Version;
    if (get_site_option('Fancy_Search_version') != $Fancy_Search_Version) {
        Fancy_Search_install_update();
    }
}

add_action('plugins_loaded', 'Fancy_Search_Upgrade');

/*
 * ADD GOOGLE FONT
 * */

function Fancy_Search_fonts_url($name, $fontweight) {
    $fonts_url = '';

    $LibreBaskerville = _x('on', $name . ' font: on or off', 'liona');

    if ('off' !== $LibreBaskerville) {
        $font_families = array();
        $font_families[] = $name . ':' . $fontweight;

        $query_args = array(
            'family' => urlencode(implode('|', $font_families)),
            'subset' => urlencode('latin,latin-ext'),
        );

        $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');
    }

    return esc_url_raw($fonts_url);
}

// Function v1.2 update to 3.0
function Fancy_Search_install_update() {
    global $wpdb, $table_Fancy_Search, $Fancy_Search_Version;
    // Retrieving the current version (to see if updated ...)
    $installed_ver = get_option("Fancy_Search_version");

    if ($installed_ver != $Fancy_Search_Version) {
        $encodeSQL = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", 'foo'));

        $sqlShow = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'categories'", 'foo'));
        if ($sqlShow != 1) {
            $sqlUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD categories TEXT", 'foo'));
            // Update of new defaults
            $defautUpgrade = array(
                "categories" => serialize(array('all'))
            );
            $chp = wp_parse_args($instance, $defautUpgrade);
            $defaultUpgrade = $wpdb->update($table_Fancy_Search, array('categories' => $chp['categories']), array('id' => 1));
        }

        $sqlShowTIS = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'paginationType'", 'foo'));
        if ($sqlShowTIS != 1) {
            $tableTISUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD (
				paginationType VARCHAR(50) NOT NULL,
				paginationText VARCHAR(250) NOT NULL
			)", 'foo'));
            $defautsTIS = array(
                "paginationType" => "trigger",
                "paginationText" => "Load More"
            );
            $fldTIS = wp_parse_args($instance, $defautsTIS);
            $TISUpgrade = $wpdb->update($table_Fancy_Search, array('paginationType' => $fldTIS['paginationType'], 'paginationText' => $fldTIS['paginationText']), array('id' => 1));

            // Update version
            update_option("Fancy_Search_version", $Fancy_Search_Version);
        }

        $sqlShowAC = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'autoCompleteActive'", 'foo'));
        if ($sqlShowAC != 1) {
            $tableUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD (
			autoCompleteActive BOOLEAN NOT NULL,
			autoCompleteSelector VARCHAR(50) NOT NULL,
			autoCompleteAutofocus BOOLEAN NOT NULL,
			autoCompleteType INT,
			autoCompleteNumber INT,
			autoCompleteTypeSuggest BOOLEAN NOT NULL,
			autoCompleteCreate BOOLEAN NOT NULL,
			autoCompleteTable VARCHAR(50) NOT NULL,
			autoCompleteColumn VARCHAR(50) NOT NULL,
			autoCompleteGenerate BOOLEAN NOT NULL,
			autoCompleteSizeMin TINYINT
			)", 'foo'));
            $defauts = array(
                "autoCompleteActive" => false,
                "autoCompleteSelector" => ".search-field",
                "autoCompleteAutofocus" => false,
                "autoCompleteType" => 0,
                "autoCompleteNumber" => 5,
                "autoCompleteTypeSuggest" => true,
                "autoCompleteCreate" => false,
                "autoCompleteTable" => $wpdb->prefix . "autosuggest",
                "autoCompleteColumn" => "words",
                "autoCompleteGenerate" => true,
                "autoCompleteSizeMin" => 2
            );
            $fld = wp_parse_args($instance, $defauts);
            $autoCompleteUpgrade = $wpdb->update($table_Fancy_Search, array('autoCompleteActive' => $fld['autoCompleteActive'], 'autoCompleteSelector' => $fld['autoCompleteSelector'], 'autoCompleteAutofocus' => $fld['autoCompleteAutofocus'], 'autoCompleteType' => $fld['autoCompleteType'], 'autoCompleteNumber' => $fld['autoCompleteNumber'], 'autoCompleteTypeSuggest' => $fld['autoCompleteTypeSuggest'], 'autoCompleteCreate' => $fld['autoCompleteCreate'], 'autoCompleteTable' => $fld['autoCompleteTable'], 'autoCompleteColumn' => $fld['autoCompleteColumn'], 'autoCompleteGenerate' => $fld['autoCompleteGenerate'], 'autoCompleteSizeMin' => $fld['autoCompleteSizeMin']), array('id' => 1));

            // Creating the default inverted index (for auto completion)
            $wpdb->query($wpdb->prepare("CREATE TABLE IF NOT EXISTS " . $fld['autoCompleteTable'] . " (
						 idindex INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						 " . $fld['autoCompleteColumn'] . " VARCHAR(250) NOT NULL)
						 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", 'foo')
            );

            // Update version
            update_option("Fancy_Search_version", $Fancy_Search_Version);
        }

        $sqlShowPH = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'placeholder'", 'foo'));
        if ($sqlShowPH != 1) {
            $tablePHUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD (placeholder VARCHAR(200))", 'foo'));
            $defautsPH = array("placeholder" => "");
            $fldPH = wp_parse_args($instance, $defautsPH);
            $PHUpgrade = $wpdb->update($table_Fancy_Search, array('placeholder' => $fldPH['placeholder']), array('id' => 1));

            // Update version
            update_option("Fancy_Search_version", $Fancy_Search_Version);
        }

        $sqlShowID = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'idform'", 'foo'));
        if ($sqlShowID != 1) {
            $tableIDUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD (idform VARCHAR(200))", 'foo'));
            $defautsID = array("idform" => "s");
            $fldID = wp_parse_args($instance, $defautsID);
            $IDUpgrade = $wpdb->update($table_Fancy_Search, array('idform' => $fldID['idform']), array('id' => 1));

            // Update version
            update_option("Fancy_Search_version", $Fancy_Search_Version);
        }

        $sqlShowCorrect = $wpdb->query($wpdb->prepare("SHOW COLUMNS FROM $table_Fancy_Search LIKE 'autoCorrectActive'", 'foo'));
        if ($sqlShowCorrect != 1) {
            $tableUpgrade = $wpdb->query($wpdb->prepare("ALTER TABLE $table_Fancy_Search ADD (
				autoCorrectActive BOOLEAN NOT NULL,
				autoCorrectType TINYINT,
				autoCorrectMethod BOOLEAN NOT NULL,
				autoCorrectString TEXT NOT NULL,
				autoCorrectCreate BOOLEAN NOT NULL
			)", 'foo'));
            $defauts = array(
                "autoCorrectActive" => true,
                "autoCorrectType" => 2,
                "autoCorrectMethod" => true,
                "autoCorrectString" => __('Tentez avec une autre orthographe : ', 'fancy-search'),
                "autoCorrectCreate" => false
            );
            $fld = wp_parse_args($instance, $defauts);
            $autoCompleteUpgrade = $wpdb->update($table_Fancy_Search, array('autoCorrectActive' => $fld['autoCorrectActive'], 'autoCorrectType' => $fld['autoCorrectType'], 'autoCorrectMethod' => $fld['autoCorrectMethod'], 'autoCorrectString' => $fld['autoCorrectString'], 'autoCorrectCreate' => $fld['autoCorrectCreate']), array('id' => 1));

            // Creating the default inverted index (for auto completion)
            $wpdb->query($wpdb->prepare("CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "autocorrectindex (
						 idWord INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						 word VARCHAR(200) NOT NULL,
						 metaphone VARCHAR(200) NOT NULL,
						 soundex VARCHAR(200) NOT NULL,
						 theme VARCHAR(200) NOT NULL,
						 coefficient FLOAT(4,1) NOT NULL DEFAULT '1.0')
						 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci", 'foo'));

            // Update version
            update_option("Fancy_Search_version", $Fancy_Search_Version);
        }
    }
}

// Added menu and associated submenus
function Fancy_Search_admin() {
    $page_title = 'Help and Settings Fancy-Search';  // Internal title to the settings page
    $menu_title = 'Fancy Search';       // Submenu title
    $capability = 'manage_options';        // Administrative role that has access to the submenu
    $menu_slug = 'fancy-search';       // Alias (slug) of the page
    $function = 'Fancy_Search_Callback';    // Function called to display the settings page
//	$function2		= 'Fancy_Search_Callback_Styles';			// Function called to display the Style Management page
//	$function3		= 'Fancy_Search_Callback_Pagination';		// Function called to display the options page for pagination
//	$function4		= 'Fancy_Search_Callback_Autocorrection';	// Function called to display the options page for automatic correction
    $function5 = 'Fancy_Search_Callback_Addons'; // Function called to display the options page for the auto completion
    $function6 = 'Fancy_Search_Callback_Documentation'; // Function called to display the documentation page

    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, FANCY_URL . '/assets/img/icon-17.png', 200);
//	add_submenu_page($menu_slug, __('Templates and styles','fancy-search'), __('Templates and styles','fancy-search'), $capability, $function2, $function2);
//	add_submenu_page($menu_slug, __('Display options','fancy-search'), __('Display options','fancy-search'), $capability, $function3, $function3);
//	add_submenu_page($menu_slug, __('Autocorrect','fancy-search'), __('Autocorrect','fancy-search'), $capability, $function4, $function4);
    add_submenu_page($menu_slug, __('Add-ons', 'fancy-search'), __('Add-ons', 'fancy-search'), $capability, $function5, $function5);
    add_submenu_page($menu_slug, __('Documents', 'fancy-search'), __('Documentation', 'fancy-search'), $capability, $function6, $function6);
}

add_action('admin_menu', 'Fancy_Search_admin');

// Blocking the form submission (with or without placeholder)
function Fancy_Search_Stop_Form($form) {
    global $wpdb, $table_Fancy_Search;

    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));

    // Variables utiles (name et placeholder)
    $idSearch = $select->idform;
    $placeholder = $select->placeholder;

    // Adds JavaScript blocking
    if (!empty($idSearch)) {
        if (empty($placeholder)) {
            $form = str_ireplace('<form', '<form onsubmit="if(document.getElementById(\'' . $idSearch . '\').value == \'\') { return false; }"', $form);
        } else {
            $form = str_ireplace('<form', '<form onsubmit="if(document.getElementById(\'' . $idSearch . '\').value == \'' . $placeholder . '\' || (document.getElementById(\'' . $idSearch . '\').placeholder == \'' . $placeholder . '\' && document.getElementById(\'' . $idSearch . '\').value == \'\')) { return false; }"', $form);
        }
    }

    // Returns the "new" form
    return $form;
}

add_filter('get_search_form', 'Fancy_Search_Stop_Form');

// Adding a style sheet for the admin
function Fancy_Search_Admin_CSS() {
    $handle = 'bootstrap_tab';
    $style = FANCY_URL . '/assets/css/bootstrap-tabs.css';
    wp_enqueue_style($handle, $style, 15);
    $handle = 'admin_css';
    $style = FANCY_URL . '/assets/css/fancy-search-admin.css';
    wp_enqueue_style($handle, $style, 15);

    wp_enqueue_script('bootstrap_tabs', FANCY_URL . "/assets/js/bootstrap-tab.js", array(), false, $in_footer = true);
}

add_action('admin_enqueue_scripts', 'Fancy_Search_Admin_CSS');

// Adding conditioning a custom style sheet
function Fancy_Search_CSS($bool) {
    wp_enqueue_style('linearicons', FANCY_URL . '/assets/fonts/linearicons/demo-files/demo.css');
    wp_enqueue_style('fancysearch-fonts', Fancy_Search_fonts_url('Roboto', '300,400,500,700,400italic'), array(), null);
    wp_enqueue_style('mCustomScrollbar', FANCY_URL . '/assets/css/templates/jquery.mCustomScrollbar.css', false);
    if ($bool == 'yes') {
//            $url = FANCY_URL.'/assets/css/templates/style-demo.css';
//            wp_register_style('style-demo', $url);
//            wp_enqueue_style('style-demo');
        wp_enqueue_style('style-demo', FANCY_URL . '/assets/css/templates/style-demo.css');
    }
    add_action('wp_enqueue_scripts', 'Fancy_Search_CSS');
}

//  Url Page
function fs_curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

//  Load Ajax sidebar menu left
add_action('wp_footer', 'Fancy_Search_action_javascript');

function Fancy_Search_action_javascript() {
    global $wpdb, $table_Fancy_Search, $select;

    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));

    $fancysearch_type = '';
    if (isset($_GET['type']) && $_GET['type'] != '') {
        $fancysearch_type = $_GET['type'];
    }
//  light_box
    if ($select->typeResults == 'lightbox' || $fancysearch_type == 'lightbox') {
        Fancy_Search_CSS($select->Style);
        wp_enqueue_script('fs-resize', FANCY_URL . '/assets/js/fs-resize.js', array(), false, $in_footer = true);
        wp_enqueue_script('imagesloaded', FANCY_URL . '/assets/js/imagesloaded.pkgd.js', array(), false, $in_footer = true);
        wp_enqueue_script('infinitescroll', FANCY_URL . '/assets/js/jquery.infinitescroll.min.js', array('jquery'), false, $in_footer = true);
        ;
        wp_enqueue_script('mCustomScrollbar', FANCY_URL . '/assets/js/jquery.mCustomScrollbar.concat.min.js', array(), false, $in_footer = true);
        wp_enqueue_script('fs-lightbox', FANCY_URL . '/assets/js/fs-lightbox.js', array(), false, $in_footer = true);
        wp_localize_script('fs-lightbox', 'Lightbox_Trigger_Scroll', array(
            'paginationActive' => $select->paginationActive,
            'paginationType' => $select->paginationType
                )
        );
        ?><script>
            jQuery(document).ready(function ($) {

                /*  Menu Ajax   */
                fs_lightbox_menu_ajax($);

                /*  Lightbox    */
                if ($("form#searchform").hasClass("fs-lightbox") == false) {
                    $("form#searchform").addClass("fs-lightbox");
                }
                if ($("form").hasClass("fs-lightbox")) {
                    $("body").append("<div class='fs-lightbox-loading'><img src='<?php echo FANCY_URL . '/assets/img/loading-lightbox.GIF'; ?>' alt='Fancy-Search'/></div>");
                    $("form.fs-lightbox").submit(function (e) {
                        e.preventDefault();
                        $(".fs-lightbox-loading").fadeIn();
                        var text = $('.fs-lightbox input').val().split(' ');
                        var count = text.length;
                        var link = text[0];
                        for (var i = 1; i < count; i++) {
                            link = link + '+' + text[i];
                        }
                        var url = "<?php echo esc_url(home_url('/')); ?>" + "?s=" + link;

                        $.ajax({
                            type: 'GET',
                            url: url,
                            complete: function (jqXHR, textStatus) {
                                var condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === "success" || textStatus === "notmodified");
                                if (condition) {
                                    $("body").append("<div class='fs-lightbox-content fs-modal fs-effect-1 fs-show'><div id='fancysearch-content' class='fancysearch-content fancysearch-grid fs-content-effect'></div></div>");

                                    /*  Load-content    */
                                    var data = jqXHR.responseText;
                                    $('.fs-lightbox-content .fancysearch-content').html($(data).filter('#fancysearch-content').html());
                                    $(".fs-lightbox-loading").fadeOut(800);
                                    $(".fs-lightbox-content").fadeIn(500);
                                    $(".fancysearch-content").fadeIn(500, function () {
                                        $(".fs-content-effect").css({"opacity": "1", "transform": "scale(1)"});
                                        $(this).mCustomScrollbar({
                                            theme: "minimal-dark"
                                        });
                                    });
                                    /*  Link-Menuitem   */
                                    $('.fs-menu-item.post').attr('href', url + '&action=post');
                                    $('.fs-menu-item.page').attr('href', url + '&action=page');

                                    /*  Menu Ajax   */
                                    fs_lightbox_menu_ajax($);


                                    /*  Load Trigger and Scroll */
                                    fs_lightbox_trigger_scroll();

                                    /*  Position- Menu  */
                                    var menu = $(".fs-lightbox-content .fs-menu"),
                                            container = $(".fs-lightbox-content .fs-container"),
                                            content = $(".fs-lightbox-content .fs-content"),
                                            width_menu = menu.width(),
                                            w_container = container.width(),
                                            w_window = $(window).width(),
                                            left_menu = Math.ceil((w_window - w_container) / 2 - 30);
        //                                    l_container =   Math.ceil(container.position().left);
        //									alert(container.position().left);
                                    content.css("padding-left", width_menu);
                                    menu.css("left", left_menu);
                                    /*  Margin-Top    */
                                    var h_head = $('.fs-header').outerHeight();
                                    if (w_window > 480) {
                                        menu.css('margin-top', h_head + 40);
                                    } else {
                                        menu.css('margin-top', h_head + 20);
                                    }

                                }
                            }
                        });
                    });
                }

            });
        </script>
        <?php
    }
}

// Form search
function Fancy_Search_form_search() {
    global $wpdb, $moteur, $select, $table_Fancy_Search, $fs_post_type, $paginationValide;

    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
    $nameSearch = $select->nameField;
    $today =  date("Y/m/d");
    echo '<form method="get" id="searchform" class="fs-searchform  fs-lightbox" action="' . esc_url(home_url("/")) . '">
			<input type="text" placeholder="Search" name="s" id="tz-search-input" class="field fssearchform inputbox search-query fs-search-input">
                        <p>Start Date: <input type="text" id="datepicker" name="start_date"></p>
                        <p>End Date: <input type="text" id="datepicker1" name="end_date"></p>
                        <button type="submit" class="submit fs-submit searchsubmit" name="submit" value="" >Search</button>
		</form>
                
		';
    ?>
    <script>
          jQuery(document).ready(function () {

            jQuery('#datepicker').datepicker({
               maxDate: '0'
       
            });

            jQuery("#datepicker1").datepicker({
                maxDate: '0' 
            });
         });
    </script>

<?php
}

add_shortcode('fancy-search-form', 'Fancy_Search_form_search');


/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Create file search.php

if (is_plugin_active('fancy-search/fancy-search.php')) {
    // Plugin is activated
    $filename = get_template_directory() . '/search.php';
    $filename_copy = get_template_directory() . '/search-fancy-copy.php';
    include ABSPATH . "/wp-admin/includes/file.php";
    global $wp_filesystem;
    WP_Filesystem();
    if (file_exists($filename) == true && file_exists($filename_copy) == false) {
        if (!$wp_filesystem->move($filename, get_template_directory() . '/search-fancy-copy.php', true)) {
            esc_html_e('error moving file!', 'fancy-search');
        }
        if (!$wp_filesystem->put_contents($filename, balanceTags('<?php get_header(); if( function_exists("Fancy_Search") ){Fancy_Search();} get_footer(); ?>'), FS_CHMOD_FILE)) {
            esc_html_e('error saving file!', 'fancy-search');
        }
    } else {
        if (!$wp_filesystem->put_contents($filename, balanceTags('<?php get_header(); if( function_exists("Fancy_Search") ){Fancy_Search();} get_footer(); ?>'), FS_CHMOD_FILE)) {
            esc_html_e('error saving file!', 'fancy-search');
        }
    }
}

/*
 * Method limit excerpt
 */

function Fancy_Search_excerpt_length() {
    return 10;
}

add_filter('excerpt_length', 'Fancy_Search_excerpt_length', 999);
?>