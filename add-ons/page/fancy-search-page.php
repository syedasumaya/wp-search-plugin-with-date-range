<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*  Back End Add-ons    */
//  Menu-tab
function fancysearch_page_tab_menu(){
    _e('<li><a href="#fs_page" class="pro" data-toggle="tab">');
    _e('Page','fancy-search');
    _e('</a></li>');
}
add_action('fs_addons_tab_menus', 'fancysearch_page_tab_menu');

//  Option add-ons
function fancysearch_page_option(){
    global $wpdb, $table_Fancy_Search; // insert global variables
    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
    ?>
    <div id="fs_page" class="tab-pane">
        <div class="col">
            <h4><?php _e('Advanced options','fancy-search'); ?></h4>
            <p class="tr">
                <input type="text" name="Fancy_Search_table_page" id="Fancy_Search_table_page" value="<?php echo $select->tablesPage; ?>" />
                <label for="Fancy_Search_table_page"><strong><?php _e('Search table','fancy-search'); ?></strong></label>
                <br/><em><?php _e('\"Other\" if you do not use the table \"xx_posts\"','fancy-search'); ?></em>
            </p>
            <p class="tr">
                <input value="<?php echo $select->colonnesWherePage; ?>" name="Fancy_Search_colonneswhere_page" id="Fancy_Search_colonneswhere_page" type="text" />
                <label for="Fancy_Search_colonneswhere_page"><strong><?php _e('Table columns to search','fancy-search'); ?></strong></label>
                <br/><em><?php _e('Separate values with commas','fancy-search'); ?></em>
            </p>
        </div>
        <div class="col">
            <!-- Order search results -->
            <h4><?php _e('Order search results','fancy-search'); ?></h4>
            <p class="tr">
                <select name="Fancy_Search_orderOK_page" id="Fancy_Search_orderOK_page">
                    <option value="1" <?php if($select->OrderOKPage == true) { echo 'selected="selected"'; } ?>><?php _e('Yes','fancy-search'); ?></option>
                    <option value="0" <?php if($select->OrderOKPage == false) { echo 'selected="selected"'; } ?>><?php _e('No','fancy-search'); ?></option>
                </select>
                <label for="Fancy_Search_orderOK_page"><strong><?php _e('Sort results?','fancy-search'); ?></strong></label>
            </p>
            <p class="tr">
                <select name="Fancy_Search_orderColumn_page" id="Fancy_Search_orderColumn_page">
                    <?php
                    $columns = $wpdb->get_results($wpdb->prepare("SELECT column_name FROM information_schema.COLUMNS WHERE table_name = '".$select->tablesPage."'", 'foo'));
                    $numberColumn = count($columns,1);
                    for($i=0; $i < $numberColumn; $i++) {
                        foreach($columns[$i] as $column => $value) {
                            ?>
                            <option value="<?php echo $value; ?>" <?php if($select->OrderColumnPage == $value) { echo 'selected="selected"'; } ?>><?php _e($value,'fancy-search'); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <label for="Fancy_Search_orderColumn_page"><strong><?php _e('Order Column','fancy-search'); ?></strong></label>
            </p>
            <p class="tr">
                <select name="Fancy_Search_ascdesc_page" id="Fancy_Search_ascdesc_page">
                    <option value="ASC" <?php if($select->AscDescPage == "ASC") { echo 'selected="selected"'; } ?>><?php _e('Ascending (ASC)','fancy-search'); ?></option>
                    <option value="DESC" <?php if($select->AscDescPage == "DESC") { echo 'selected="selected"'; } ?>><?php _e('Descending (DESC)','fancy-search'); ?></option>
                </select>
                <label for="Fancy_Search_ascdesc_page"><strong><?php _e('Ascending or descending?','fancy-search'); ?></strong></label>
            </p>
        </div>
    </div>

    <?php
}
add_action('fs_addons_options', 'fancysearch_page_option');

// Selecting data in the database
//function fs_page_table(){
//    global $wpdb, $table_Fancy_Search,$colonnesWhere;
//
//    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
//    $colonnesWhere = '';
//}
//add_action('fs_addons_table', 'fs_page_table');





/*  Front End Add-ons   */

//  Display Content Results
function fancysearch_page(){
    global $wpdb, $moteur, $select,$table_Fancy_Search,$fs_post_type,$paginationValide;

    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));

    // Instantiation useful variables
    $table = $select->tablesPage;
    $nameSearch = $select->nameField;
    $typeRecherche = $select->typeSearch;
    $encoding = $select->encoding;
    $exclusion = $select->exclusionWords;
    $exact = $select->exactSearch;
    $accent = $select->accents;

    // Other useful variables
    $firstlast = $select->paginationFirstLast;
    $prevnext = $select->paginationPrevNext;
    $firstpage = $select->paginationFirstPage;
    $lastpage = $select->paginationLastPage;
    $prevtext = $select->paginationPrevText;
    $nexttext = $select->paginationNextText;

//     Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
    if(empty($select->colonnesWherePage)) {
        $colonnesWhere = array('post_title', 'post_content', 'post_excerpt');
    } else {
        $colonnesWhere = explode(',',trim($select->colonnesWherePage));
    }

    // Launch Search Engine
    $moteur = new moteurRecherche($wpdb, stripslashes($_GET[$nameSearch]), $table, $typeRecherche, $exclusion, $encoding, $exact, $accent);
    $moteur->moteurRequetes($colonnesWhere);

    // Display the correction results
    global $correctionsmoteur, $autocorrect; // Necessary to get the result in the display function
    if($select->autoCorrectActive == true) {
        $correctionsmoteur = $moteur->getCorrection($wpdb->prefix."autocorrectindex", "s", $select->autoCorrectMethod);

        if($select->autoCorrectType == 1 || $select->autoCorrectType == 2) {
            if($moteur->getIndex($wpdb->prefix."autocorrectindex")) {
                $autocorrect = $moteur->getCorrectedResults();
            }
        }
    }


    // Displaying results based on one or more selected categories (for articles only!)
    if(!in_array('all', unserialize($select->categories)) && $select->categories != 'a:0:{}' && $select->postType == "page") {
        $conditions = "as WPP INNER JOIN $wpdb->term_relationships as TR INNER JOIN $wpdb->terms as TT WHERE WPP.ID = TR.object_id AND TT.term_id = TR.term_taxonomy_id AND (";
        $nbCat = 0;
        foreach(unserialize($select->categories) as $cate) {
            $conditions .= "TT.slug = '".$cate."'";
            if($nbCat < (count(unserialize($select->categories)) -1)) {
                $conditions .= " OR ";
            }
            $nbCat++;
        }
        $conditions .= ") AND";
    } else {
        $conditions = '';
    }

    $wpAdaptation = "AND post_type = 'page'";
    $fs_post_type = 'page';
    // Launch of the display function
    echo("<div id='fs-".esc_attr($fs_post_type)."-content' class='fancysearch-".esc_attr($fs_post_type)." fs-list-content'>");
    echo("<div class='fs-tab-content'>");
    if($select->paginationActive == true && $select->NumberPerPage != 0) {
        $moteur->moteurAffichage('fs_display', '', array(true, htmlspecialchars($_GET['page']), htmlspecialchars($select->NumberPerPage), true), array($select->OrderOKPage, $select->OrderColumnPage, $select->AscDescPage), $algo = array($select->AlgoOK,'algo','DESC','ID'), $wpAdaptation, $conditions);
        $paginationValide = true;
    } else if ($select->paginationActive == false && $select->NumberPerPage != 0) {
        $moteur->moteurAffichage('fs_display', '', array(true, htmlspecialchars($_GET['page']), htmlspecialchars($select->NumberPerPage), true), array($select->OrderOKPage, $select->OrderColumnPage, $select->AscDescPage), $algo = array($select->AlgoOK,'algo','DESC','ID'), $wpAdaptation, $conditions);
    }
    echo("</div>");
    echo("</div>");


    // Launch of the paging function if activated ...
    if($select->paginationActive == true) {
        if($select->paginationType == "infinite") {
            $class_pagination   =   "fs-infinite";
        }elseif($select->paginationType == "trigger") {
            $class_pagination   =   "fs-trigger";
        }elseif($select->paginationType == "classic") {
            $class_pagination   =   "fs-classic";
        }
        echo ('<div class="fs-pagenavi '.$class_pagination.'">');

        $moteur->moteurPagination(htmlspecialchars($_GET['page']), 'page',$fs_post_type, 2, 0, $prevnext, $firstlast, $arrayAff = array($prevtext, $nexttext, $firstpage, $lastpage, 'precsuiv', 'pagination-current', 'pagination-block', 'pagination-disabled'), $arraySeparateur = array('&hellip;', ' ', ' ', ' ', ' '));

        // Adds the text of the trigger if the option is active
        if($select->paginationType == "trigger") {
            echo ('<div id="loadMore" class="fs-loadmore"><a>'.$select->paginationText.'</a></div>');
        }

        echo ('</div>');
    }

}
add_action('fs_addons_content_page', 'fancysearch_page');

//  Display Item Menu Sidebarleft
function fancysearch_page_menu_item(){?>
    <a class="fs-menu-item page" href="<?php echo esc_url(fs_curPageURL()).'&action=page'; ?>"><?php _e('page','fancy-search');?></a>
<?php }
add_action('fs_addons_menu_item_page', 'fancysearch_page_menu_item');