<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function Fancy_Search_FullText() {
	global $wpdb, $table_Fancy_Search;
	$select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
	
	// Reclaiming serviceable variable values
	$columnSelectSearch = $select->colonnesWhere;
	$databaseSearch = $select->db;
	$tableSearch = $select->tables;

	// Inclusion of search engine class
	include('class.inc/moteur-php5.5.class-inc.php');

	$alterTable = new alterTableFullText($wpdb, $databaseSearch, $tableSearch, $columnSelectSearch);
	echo '<script type="application/javascript">alert("'.__('FULLTEXT indexes created with success! \ You can use the type FULLTEXT now ...','fancy-search').'");</script>';
}

// Updating default data
function Fancy_Search_update() {
	global $wpdb, $table_Fancy_Search; // insert global variables

	// General
    $Fancy_Search_search_table_post		= esc_html($_POST['Fancy_Search_table_post']);
    $Fancy_Search_search_table_page		= esc_html($_POST['Fancy_Search_table_page']);
	$Fancy_Search_resulttext		    = esc_html($_POST['Fancy_Search_resulttext']);
	$Fancy_Search_errortext		        = esc_html($_POST['Fancy_Search_errortext']);
    $Fancy_Search_colonneswhere_post    = sanitize_text_field($_POST['Fancy_Search_colonneswhere_post']);
    $Fancy_Search_colonneswhere_page    = sanitize_text_field($_POST['Fancy_Search_colonneswhere_page']);
	$Fancy_Search_encoding		        = esc_attr($_POST['Fancy_Search_encoding']);
    $Fancy_Search_typeresults		    = esc_attr($_POST['Fancy_Search_typeresults']);
    $Fancy_Search_addonsActive		    = sanitize_text_field($_POST['Fancy_Search_addonsActive']);
	$Fancy_Search_exclusionwords	    = sanitize_text_field($_POST['Fancy_Search_exclusionwords']);

	
	// Category
	$Fancy_Search_categories 		= array();
	foreach($_POST['Fancy_Search_categories'] as $ctgSave) {
		array_push($Fancy_Search_categories, $ctgSave);
	}
	if(is_numeric($_POST['Fancy_Search_numberPerPage']) || !empty($_POST['Fancy_Search_numberPerPage'])) {
		$Fancy_Search_numberPerPage = intval($_POST['Fancy_Search_numberPerPage']);
	} else {
		$Fancy_Search_numberPerPage = 0;
	}
	
	// Order search results
	$Fancy_Search_strong		= $_POST['Fancy_Search_strong'];
    $Fancy_Search_orderOK_post		= $_POST['Fancy_Search_orderOK_post'];
    $Fancy_Search_orderOK_page		= $_POST['Fancy_Search_orderOK_page'];
    $Fancy_Search_orderColumn_post	= $_POST['Fancy_Search_orderColumn_post'];
    $Fancy_Search_orderColumn_page	= $_POST['Fancy_Search_orderColumn_page'];
    $Fancy_Search_ascdesc_post		= $_POST['Fancy_Search_ascdesc'];
    $Fancy_Search_ascdesc_page		= $_POST['Fancy_Search_ascdesc_page'];

    // Display Option
    $Fancy_Search_nbResultsOK		= $_POST['Fancy_Search_nbResultsOK'];
    $Fancy_Search_numberOK		    = $_POST['Fancy_Search_numberOK'];
    $Fancy_Search_style             = $_POST['Fancy_Search_style'];
    $Fancy_Search_formatageDateOK	= $_POST['Fancy_Search_formatageDateOK'];
    $Fancy_Search_dateOK			= $_POST['Fancy_Search_dateOK'];
    $Fancy_Search_authorOK		    = $_POST['Fancy_Search_authorOK'];
    $Fancy_Search_categoryOK		= $_POST['Fancy_Search_categoryOK'];
    $Fancy_Search_titleOK			= $_POST['Fancy_Search_titleOK'];
    $Fancy_Search_articleOK		    = $_POST['Fancy_Search_articleOK'];
    $Fancy_Search_commentOK		    = $_POST['Fancy_Search_commentOK'];
    $Fancy_Search_imageOK			= $_POST['Fancy_Search_imageOK'];
    $Fancy_Search_blocOrder		    = $_POST['Fancy_Search_blocOrder'];

    //  Pagination
    $Fancy_Search_pagination_active		    = $_POST['Fancy_Search_pagination_active'];
    $Fancy_Search_pagination_firstlast	    = $_POST['Fancy_Search_pagination_firstlast'];
    $Fancy_Search_pagination_prevnext		= $_POST['Fancy_Search_pagination_prevnext'];
    $Fancy_Search_pagination_firstpage	    = $_POST['Fancy_Search_pagination_firstpage'];
    $Fancy_Search_pagination_lastpage		= $_POST['Fancy_Search_pagination_lastpage'];
    $Fancy_Search_pagination_prevtext		= $_POST['Fancy_Search_pagination_prevtext'];
    $Fancy_Search_pagination_nexttext		= $_POST['Fancy_Search_pagination_nexttext'];
    $Fancy_Search_pagination_type			= $_POST['Fancy_Search_pagination_type'];
    $Fancy_Search_pagination_text			= $_POST['Fancy_Search_pagination_text'];

	$Fancy_Search_update = $wpdb->update(
		$table_Fancy_Search,
		array(
            //  General
            "tablesPost" => $Fancy_Search_search_table_post,
            "tablesPage" => $Fancy_Search_search_table_page,
            "colonnesWherePost" => $Fancy_Search_colonneswhere_post,
            "colonnesWherePage" => $Fancy_Search_colonneswhere_page,
			"encoding" => $Fancy_Search_encoding,
            "typeResults" => $Fancy_Search_typeresults,
            "addonsActive" => $Fancy_Search_addonsActive,
			"exclusionWords" => $Fancy_Search_exclusionwords,
			"NumberPerPage" => $Fancy_Search_numberPerPage,
			"strongWords" => $Fancy_Search_strong,
			"OrderOKPost" => $Fancy_Search_orderOK_post,
            "OrderOKPage" => $Fancy_Search_orderOK_page,
            "OrderColumnPost" => $Fancy_Search_orderColumn_post,
            "OrderColumnPage" => $Fancy_Search_orderColumn_page,
            "AscDescPost" => $Fancy_Search_ascdesc_post,
            "AscDescPage" => $Fancy_Search_ascdesc_page,
			"categories" => serialize($Fancy_Search_categories),
			"ResultText" => $Fancy_Search_resulttext,
			"ErrorText" => $Fancy_Search_errortext,

            //  Display Option
            "nbResultsOK" => $Fancy_Search_nbResultsOK,
            "NumberOK" => $Fancy_Search_numberOK,
            "Style" => $Fancy_Search_style,
            "formatageDate" => $Fancy_Search_formatageDateOK,
            "DateOK" => $Fancy_Search_dateOK,
            "AuthorOK" => $Fancy_Search_authorOK,
            "CategoryOK" => $Fancy_Search_categoryOK,
            "TitleOK" => $Fancy_Search_titleOK,
            "ArticleOK" => $Fancy_Search_articleOK,
            "CommentOK" => $Fancy_Search_commentOK,
            "ImageOK" => $Fancy_Search_imageOK,
            "BlocOrder" => $Fancy_Search_blocOrder,

            //  Pagination
            "paginationActive" => $Fancy_Search_pagination_active,
            "paginationFirstLast" => $Fancy_Search_pagination_firstlast,
            "paginationPrevNext" => $Fancy_Search_pagination_prevnext,
            "paginationFirstPage" => $Fancy_Search_pagination_firstpage,
            "paginationLastPage" => $Fancy_Search_pagination_lastpage,
            "paginationPrevText" => $Fancy_Search_pagination_prevtext,
            "paginationNextText" => $Fancy_Search_pagination_nexttext,
            "paginationType" => $Fancy_Search_pagination_type,
            "paginationText" => $Fancy_Search_pagination_text
		), 
		array('id' => 1)

	);
}

// Display function of the help page and extension settings
function Fancy_Search_Callback() {
	global $wpdb, $table_Fancy_Search; // insérer les variables globales

	// Activate the update function (upload)
	if(isset($_POST['Fancy_Search_action']) && $_POST['Fancy_Search_action'] == __('Update' , 'fancy-search')) {
		Fancy_Search_update();
	}
	
	// Activate the update function (upload)
	if(isset($_POST['Fancy_Search_fulltext'])) {
		Fancy_Search_FullText();
	}

	/* --------------------------------------------------------------------- */
	/* ---------------------------- Showing page --------------------------- */
	/* --------------------------------------------------------------------- */
	echo '<div class="wrap fancy-search-admin">';
	echo '<div class="icon32 icon"></div>';
	echo '<h2>'; _e('Fancy-Search settings.','fancy-search'); echo '</h2><br/>';
    echo '<div class="fs-header-title">';
    _e('<strong>Fancy-Search</strong> enables a powerful search engine for WordPress.', 'fancy-search');
    echo '<br/>';
    _e('The following documentation describes the installation and overall operation of the fancy search plugin.', 'fancy-search');
    echo '<br/>';
    _e('<em> N.B. : contact <a href=\"http://www.templaza.com/\" target=\"_blank\">Templaza</a>, the creator of the plugin, for more information.</em>', 'fancy-search'); echo '<br/>';
    echo '</div>';
    echo '<div id="fancysearch-header-wrap">';
    echo '<div id="fancysearch-logo">';
    echo '<a target="_blank" href="#"></a>';
    echo '</div>';
    echo '</div>';
    do_action('fs_addons_column');

	// Selecting data in the database
	$select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
    add_action( 'admin_footer', 'Fancy_Search_admin_javascript' ); // Write our JS below here

    function Fancy_Search_admin_javascript() {?>
        <script type="text/javascript">
            function montrer(object) {
                if (document.getElementById) document.getElementById(object).style.display = 'block';
            }

            function cacher(object) {
                if (document.getElementById) document.getElementById(object).style.display = 'none';
            }
        </script>
    <?php }
?>
		<!-- Form to install FULLTEXT indexes (if enabled by clicking on the link) -->
        <form id="Fancy-Search-Form" method="post">
        	<input type="hidden" name="Fancy_Search_fulltext" value="" />
        </form>
        
        <!-- Update data form -->
        <form method="post" action="">
            <div class="fancysearch-sub-header">
                <input type="submit" name="Fancy_Search_action" class="button-primary" value="<?php _e('Update' , 'fancy-search'); ?>" />
            </div>
       	<div class="block">
            <div class="fs-tabs">
                <ul class="nav nav-tabs fs-admin-container">
                    <li class="active"><a href="#fs_setting" data-toggle="tab"><?php _e('General','fancy-search') ?></a></li>
                    <li><a href="#fs_display" data-toggle="tab"><?php _e('Display Option','fancy-search') ?></a></li>
                    <li><a href="#fs_pagination" class="pro" data-toggle="tab"><?php _e('Pagination','fancy-search') ?></a></li>
                    <li><a href="#fs_addons" class="pro" data-toggle="tab"><?php _e('Add-ons','fancy-search') ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="fs_setting">
                        <div class="col">

                        <!-- General search engine options -->
                            <h4><?php _e('General search engine options','fancy-search'); ?></h4>

                            <p class="tr">
                                <input value="<?php echo $select->NumberPerPage; ?>" name="Fancy_Search_numberPerPage" id="Fancy_Search_numberPerPage" type="text" />
                                <label for="Fancy_Search_numberPerPage"><strong><?php _e('Number of results per page','fancy-search'); ?></strong></label>
                                <br/><em><?php _e('0 or blank to display all in one page (without pagination)','fancy-search'); ?></em>
                            </p>

                            <p class="tr">
                                <select name="Fancy_Search_typeresults" id="Fancy_Search_typeresults">
                                    <option value="searchpage" <?php if($select->typeResults == 'searchpage') { echo 'selected="selected"'; } ?>><?php _e('Search Page','fancy-search'); ?></option>
                                    <option value="lightbox" <?php if($select->typeResults == 'lightbox') { echo 'selected="selected"'; } ?>><?php _e('Lightbox','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_typeresults"><strong><?php _e('Results are displayed in lightbox style or in Search Page','fancy-search'); ?></strong></label>
                            </p>

                        <!-- Formatting options for search queries -->
                            <h4><?php _e('Formatting options for search queries','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_exclusionwords" id="Fancy_Search_exclusionwords">
                                    <option value="" <?php if(empty($select->accents)) { echo 'selected="selected"'; } ?>><?php _e('Disabled','fancy-search'); ?></option>
                                    <option value="1" <?php if($select->exclusionWords == 1) { echo 'selected="selected"'; } ?>><?php _e('< 1 character','fancy-search'); ?></option>
                                    <option value="2" <?php if($select->exclusionWords == 2) { echo 'selected="selected"'; } ?>><?php _e('< 2 characters','fancy-search'); ?></option>
                                    <option value="3" <?php if($select->exclusionWords == 3) { echo 'selected="selected"'; } ?>><?php _e('< 3 characters','fancy-search'); ?></option>
                                    <option value="4" <?php if($select->exclusionWords == 4) { echo 'selected="selected"'; } ?>><?php _e('< 4 characters','fancy-search'); ?></option>
                                    <option value="5" <?php if($select->exclusionWords == 5) { echo 'selected="selected"'; } ?>><?php _e('< 5 characters','fancy-search'); ?></option>
                                    <option value="6" <?php if($select->exclusionWords == 6) { echo 'selected="selected"'; } ?>><?php _e('< 6 characters','fancy-search'); ?></option>
                                    <option value="7" <?php if($select->exclusionWords == 7) { echo 'selected="selected"'; } ?>><?php _e('< 7 characters','fancy-search'); ?></option>
                                    <option value="8" <?php if($select->exclusionWords == 8) { echo 'selected="selected"'; } ?>><?php _e('< 8 characters','fancy-search'); ?></option>
                                    <option value="9" <?php if($select->exclusionWords == 9) { echo 'selected="selected"'; } ?>><?php _e('< 9 characters','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_exclusionwords"><strong><?php _e('Exclude short words?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_encoding" id="Fancy_Search_encoding">
                                    <option value="utf-8" <?php if($select->encoding == "utf-8") { echo 'selected="selected"'; } ?>><?php _e('UTF-8','fancy-search'); ?></option>
                                    <option value="iso-8859-1" <?php if($select->encoding == "iso-8859-1") { echo 'selected="selected"'; } ?>><?php _e('ISO-8859-1 (Latin-1)','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_encoding"><strong><?php _e('Choice of character encoding','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                        <div class="col">

                        <!-- Highlight keywords and display settings -->
                            <h4><?php _e('Highlight keywords and display settings','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_strong" id="Fancy_Search_strong">
                                    <option value="exact" <?php if($select->strongWords == "exact") { echo 'selected="selected"'; } ?>><?php _e('Precise','fancy-search'); ?></option>
                                    <option value="total" <?php if($select->strongWords == "total") { echo 'selected="selected"'; } ?>><?php _e('Approaching','fancy-search'); ?></option>
                                    <option value="no" <?php if($select->strongWords == "no") { echo 'selected="selected"'; } ?>><?php _e('No bolding','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_strong"><strong><?php _e('Highlight keywords search','fancy-search'); ?></strong></label>
                                <br/><em><?php _e('\"Precise\" for the exact string, \"approaching\" for the word containing a string (if LIKE search)','fancy-search'); ?></em>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->ResultText; ?>" name="Fancy_Search_resulttext" id="Fancy_Search_resulttext" type="text" />
                                <label for="Fancy_Search_resulttext"><strong><?php _e('Text for the search query','fancy-search'); ?></strong></label>
                                <br/><em><?php _e('Empty to hide the text.','fancy-search'); ?></em>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->ErrorText; ?>" name="Fancy_Search_errortext" id="Fancy_Search_errortext" type="text" />
                                <label for="Fancy_Search_errortext"><strong><?php _e('Display text when there is no result','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                    </div>
                    <div class="tab-pane" id="fs_display">
                        <div class="col">
                            <h4><?php _e('Block to display','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_titleOK" id="Fancy_Search_titleOK">
                                    <option value="1" <?php if($select->TitleOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->TitleOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_titleOK"><strong><?php _e('Display title?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_dateOK" id="Fancy_Search_dateOK">
                                    <option value="1" <?php if($select->DateOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->DateOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_dateOK"><strong><?php _e('Display date?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_authorOK" id="Fancy_Search_authorOK">
                                    <option value="1" <?php if($select->AuthorOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->AuthorOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_authorOK"><strong><?php _e('Display author name?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_categoryOK" id="Fancy_Search_categoryOK">
                                    <option value="1" <?php if($select->CategoryOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->CategoryOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_categoryOK"><strong><?php _e('Display category?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_commentOK" id="Fancy_Search_commentOK">

                                    <option value="1" <?php if($select->CommentOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->CommentOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_commentOK"><strong><?php _e('Display number of comments?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_articleOK" id="Fancy_Search_articleOK">
                                    <option value="no" <?php if($select->ArticleOK == "no") { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                    <option value="excerpt" <?php if($select->ArticleOK == "excerpt") { echo 'selected="selected"'; } ?>><?php _e('The excerpt','fancy-search'); ?></option>
                                    <option value="excerptmore" <?php if($select->ArticleOK == "excerptmore") { echo 'selected="selected"'; } ?>><?php _e('Excerpt + \"Read more...\"','fancy-search'); ?></option>
                                    <option value="article" <?php if($select->ArticleOK == "article") { echo 'selected="selected"'; } ?>><?php _e('The article','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_articleOK"><strong><?php _e('Show the article or the excerpt?','fancy-search'); ?></strong></label>
                                <br/><em><?php _e('The article which is not used for search results is a page','fancy-search'); ?></em>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_imageOK" id="Fancy_Search_imageOK">
                                    <option value="1" <?php if($select->ImageOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->ImageOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_imageOK"><strong><?php _e('Display post thumbnails?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_blocOrder" id="Fancy_Search_blocOrder">
                                    <option value="D-A-C" <?php if($select->BlocOrder == "D-A-C") { echo 'selected="selected"'; } ?>><?php _e('Date - Author - Category','fancy-search'); ?></option>
                                    <option value="D-C-A" <?php if($select->BlocOrder == "D-C-A") { echo 'selected="selected"'; } ?>><?php _e('Date - Category - Author','fancy-search'); ?></option>
                                    <option value="A-D-C" <?php if($select->BlocOrder == "A-D-C") { echo 'selected="selected"'; } ?>><?php _e('Author - Date - Category','fancy-search'); ?></option>
                                    <option value="A-C-D" <?php if($select->BlocOrder == "A-C-D") { echo 'selected="selected"'; } ?>><?php _e('Author - Category - Date','fancy-search'); ?></option>
                                    <option value="C-A-D" <?php if($select->BlocOrder == "C-A-D") { echo 'selected="selected"'; } ?>><?php _e('Category - Author - Date','fancy-search'); ?></option>
                                    <option value="C-D-A" <?php if($select->BlocOrder == "C-D-A") { echo 'selected="selected"'; } ?>><?php _e('Category - Date - Author','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_blocOrder"><strong><?php _e('Informations display order','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                        <div class="col">
                            <h4><?php _e('Blocks style','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_style" id="Fancy_Search_style">
                                    <option value="yes" <?php if($select->Style == "yes") { echo 'selected="selected"'; } ?>><?php _e('CSS Demo','fancy-search'); ?></option>
                                    <option value="no" <?php if($select->Style == "no") { echo 'selected="selected"'; } ?>><?php _e('CSS Default','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_style"><strong><?php _e('CSS style for search page','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->formatageDate; ?>" name="Fancy_Search_formatageDateOK" id="Fancy_Search_formatageDateOK" type="text" />
                                <label for="Fancy_Search_formatageDateOK"><strong><?php _e('Date format (if enabled)','fancy-search'); ?></strong></label>
                                <br/><em><?php _e('<a href=\"http://php.net/manual/fr/function.date.php\" target=\"_blank\">See the documentation of PHP about dates</a> (for example: \"l, F j, Y\" to \"Tuesday, June 25, 2013\")','fancy-search'); ?></em>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_nbResultsOK" id="Fancy_Search_nbResultsOK">
                                    <option value="1" <?php if($select->nbResultsOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->nbResultsOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_nbResultsOK"><strong><?php _e('Display number of results?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_numberOK" id="Fancy_Search_numberOK">
                                    <option value="1" <?php if($select->NumberOK == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->NumberOK == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_numberOK"><strong><?php _e('Number the search results?','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                    </div>
                    <div class="tab-pane" id="fs_pagination">
                        <div class="col">
                            <h4><?php _e('Settings and General Styles','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_pagination_active" id="Fancy_Search_pagination_active">
                                    <option value="1" <?php if($select->paginationActive == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->paginationActive == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_pagination_active"><strong><?php _e('Enabled pagination','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_pagination_type" id="Fancy_Search_pagination_type">
                                    <option value="classic" <?php if($select->paginationType == "classic") { echo 'selected="selected"'; } ?>><?php _e('Classic pagination','fancy-search'); ?></option>
                                    <option value="trigger" <?php if($select->paginationType == "trigger") { echo 'selected="selected"'; } ?>><?php _e('Trigger (with click)','fancy-search'); ?></option>
                                    <option value="infinite" <?php if($select->paginationType == "infinite") { echo 'selected="selected"'; } ?>><?php _e('Infinite scroll','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_pagination_type"><strong><?php _e('Display type for the results','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->paginationText; ?>" type="text" name="Fancy_Search_pagination_text" id="Fancy_Search_pagination_text" />
                                <label for="Fancy_Search_pagination_text"><strong><?php _e('Text for the trigger Ajax','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                        <div class="col">
                            <h4><?php _e('Label and options for classic pagination','fancy-search'); ?></h4>
                            <p class="tr">
                                <select name="Fancy_Search_pagination_firstlast" id="Fancy_Search_pagination_firstlast">
                                    <option value="1" <?php if($select->paginationFirstLast == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->paginationFirstLast == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_pagination_firstlast"><strong><?php _e('Display "first page" and "last page"?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <select name="Fancy_Search_pagination_prevnext" id="Fancy_Search_pagination_prevnext">
                                    <option value="1" <?php if($select->paginationPrevNext == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                                    <option value="0" <?php if($select->paginationPrevNext == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                                </select>
                                <label for="Fancy_Search_pagination_prevnext"><strong><?php _e('Display "previous" and "next"?','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->paginationFirstPage; ?>" name="Fancy_Search_pagination_firstpage" id="Fancy_Search_pagination_firstpage" type="text" />
                                <label for="Fancy_Search_pagination_firstpage"><strong><?php _e('Text for "first page"','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->paginationLastPage; ?>" name="Fancy_Search_pagination_lastpage" id="Fancy_Search_pagination_lastpage" type="text" />
                                <label for="Fancy_Search_pagination_lastpage"><strong><?php _e('Text for "last page"','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->paginationPrevText; ?>" name="Fancy_Search_pagination_prevtext" id="Fancy_Search_pagination_prevtext" type="text" />
                                <label for="Fancy_Search_pagination_prevtext"><strong><?php _e('Text for "previous"','fancy-search'); ?></strong></label>
                            </p>
                            <p class="tr">
                                <input value="<?php echo $select->paginationNextText; ?>" name="Fancy_Search_pagination_nexttext" id="Fancy_Search_pagination_nexttext" type="text" />
                                <label for="Fancy_Search_pagination_nexttext"><strong><?php _e('Text for "next"','fancy-search'); ?></strong></label>
                            </p>
                        </div>
                    </div>
                    <div class="tab-pane fs_addons" id="fs_addons">
                        <ul class="fs_addons_nav nav nav-tabs">
                            <?php do_action('fs_addons_tab_menus'); ?>
                        </ul>
                        <div class="fs_addon_content tab-content">
                            <?php do_action('fs_addons_options'); ?>
                        </div>
                    </div>
                </div>
            </div>
			<p class="clear"></p>
        </div>
        <div class="fancysearch-sub-header fancysearch-button">
            <input type="submit" name="Fancy_Search_action" class="button-primary" value="<?php _e('Update' , 'fancy-search'); ?>" />
        </div>
        <p class="clear"></p>
        </form>
<?php
	echo '</div>'; // End of the admin page
} // End of the callback function
?>