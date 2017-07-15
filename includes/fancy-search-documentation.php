<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Display function of the help page and extension settings
function Fancy_Search_Callback_Documentation() {

	// Activate the update function indexes FULL TEXT (upload)
	if(isset($_POST['Fancy_Search_fulltext'])) {
		Fancy_Search_FullText_Doc();
	}

	/* --------------------------------------------------------------------- */
	/* ---------------------------- Showing page --------------------------- */
	/* --------------------------------------------------------------------- */
	echo '<div class="wrap fancy-search-admin">';
	echo '<div class="icon32 icon"></div>';
	echo '<h2>'; _e('Documents','fancy-search'); echo '</h2><br/>';
	echo '<div class="fs-header-title">';
	_e('<strong>Fancy-Search</strong> will automatically create search.php files and search-fancy-copy.php file in your theme.', 'fancy-search');
	echo '<br/>';
	_e('If there is a search.php file in theme, the content in this existing file will be moved into search-fancy-copy.php file and search.php file will have new content of plugin.', 'fancy-search');
	echo '<br/>';
	_e('<em> N.B. : contact <a href=\"http://www.templaza.com/\" target=\"_blank\">Templaza</a>, the creator of the plugin, for more information.</em>', 'fancy-search'); echo '<br/>';
	echo '</div>';
?>
    <div class="fs-readme">
        <div class="block">
            <div class="col">
                <h4><?php _e('Plugin installation','fancy-search'); ?></h4>
                <p class="tr"><?php _e('1. Add shortcode to use.','fancy-search'); ?></p>
                <div class="tr-info">
                    <p><?php _e('Locate the search form','fancy-search') ?></p>
                    <strong>
                        <?php   esc_html_e('do_shortcode("fancy-search-form-search") in php field','fancy-search'); ?><br>
                        <?php   esc_html_e('[fancy-search-form-search] in page , post,editor...','fancy-search'); ?><br>
                    </strong>
                </div>
            </div>
            <div class="col">
                <h4><?php _e('Screenshots','fancy-search'); ?></h4>
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-1.jpg'); ?>" alt="Capture Fancy Search - 1" /></p>
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-1_2.jpg'); ?>" alt="Capture Fancy Search - 1_2" /></p>
            </div>
        </div>
        <div class="block">
            <div class="col">
                <p class="tr"><?php _e('2. Set the search engine below','fancy-search'); ?></p>
                <div class="tr-info">
                    <p><?php _e('The default settings meet the essential features of the search engine.','fancy-search') ?></p>
                    <ol>
                        <li><?php _e('Enter the value of the attribute \"name\" field research (\"s\" by default) and the tables in which search (leave default if you are unsure)','fancy-search') ?></li>
                        <li><?php _e('Results are displayed in lightbox style or in Search Page (default: Search Page).','fancy-search') ?></li>
                        <li><?php _e('Formatting options for search queries.','fancy-search') ?></li>
                        <li><?php _e('Highlight keywords search?','fancy-search') ?></li>
                    </ol>
                </div>
            </div>
            <div class="col">
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-3.jpg'); ?>" alt="Capture Fancy Search - 3" /></p>
            </div>
        </div>
        <div class="block">
            <div class="col">
                <p class="tr"><?php _e('3. Stylize and all search results','fancy-search'); ?></p>
                <div class="tr-info">
                    <p><?php _e('Plusieurs options disponibles pour personnaliser l\'affichage des résultats.','fancy-search') ?></p>
                    <ol>
                        <li><?php _e('Choose the blocks to display (everything is adjustable).','fancy-search') ?></li>
                        <li><?php _e('Choose a template from the list or disable themes (custom style).','fancy-search') ?></li>
                        <li><?php _e('Set CSS classes as you wish.','fancy-search') ?></li>
                        <li><?php _e('Format the date as you want (if you want to display).','fancy-search') ?></li>
                        <li><?php _e('Number the search results?','fancy-search') ?></li>
                    </ol>
                </div>
            </div>
            <div class="col">
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-5.png'); ?>" alt="Capture Fancy Search - 4" /></p>
            </div>
        </div>
        <div class="block">
            <div class="col">
                <p class="tr"><?php _e('4. Set pagination (optional)','fancy-search'); ?></p>
                <div class="tr-info">
                    <p><?php _e('Several paging customization options.','fancy-search') ?></p>
                    <ol>
                        <li><?php _e('Enable or disable pagination (display all the results if disabled).','fancy-search') ?></li>
                        <li><?php _e('Select the links to display in the pagination.','fancy-search') ?></li>
                        <li><?php _e('Choose a theme for paging or not (several colors available).','fancy-search') ?></li>
                        <li><?php _e('Edit labels pagination if required.','fancy-search') ?></li>
                    </ol>
                </div>
            </div>
            <div class="col">
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-6.png'); ?>" alt="Capture Fancy Search - 5" /></p>
            </div>
        </div>
        <div class="block">
            <div class="col">
                <p class="tr"><?php _e('5. Add-ons (optional)','fancy-search'); ?></p>
                <div class="tr-info">
                    <p><?php _e('Options for each add-on.','fancy-search') ?></p>
                    <ol>
                        <li><?php _e('Advanced options:Search table and Table columns to search.','fancy-search') ?></li>
                        <li><?php _e('Sort search results.','fancy-search') ?></li>
                    </ol>
                </div>
            </div>
            <div class="col">
                <p class="tr"><img src="<?php echo esc_url(FANCY_URL.'/assets/img/screenshot-7.png'); ?>" alt="Capture Fancy Search - 6" /></p>
            </div>
        </div>
    </div>

    
    <!-- Form to install FULLTEXT indexes (if enabled by clicking on the link) -->
    <form id="fancy-search-Form" method="post">
        <input type="hidden" name="Fancy_Search_fulltext" value="" />
    </form>
<?php
	echo '</div>';
} // End of the callback function

function Fancy_Search_FullText_Doc() {
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
?>