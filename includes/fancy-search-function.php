<?php
/* -------------------------------------------- */
/* ------------ Motor function ------------ */
/* -------------------------------------------- */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

function Fancy_Search() { //echo '<pre>'; print_r($_GET); exit;
    global $wpdb, $table_Fancy_Search, $moteur, $select;

    // Selecting data in the database
    $select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
//    $select1 = $wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo', 1337 );
//    var_dump($select);die('abc');
    // Instantiation useful variables
    $selector = $select->autoCompleteSelector;
    $dbName = $select->db;
    $tableName = $select->autoCompleteTable;
    $tableColumn = $select->autoCompleteColumn;
    $limitDisplay = $select->autoCompleteNumber;
    $multiple = $select->autoCompleteTypeSuggest;
    $type = $select->autoCompleteType;
    $autoFocus = $select->autoCompleteAutofocus;
    $create = false; // Is allowed to false because the table is created otherwise
    // Other useful variables
    $nameSearch = $select->nameField;
    $encoding = $select->encoding;

    // Inclusion of search engine class
    include_once('class.inc/moteur-php5.5.class-inc.php');
    // Adding conditioning style sheets for display
    Fancy_Search_CSS($select->Style);
    // Loading Script
    wp_enqueue_script('fs-resize', FANCY_URL . '/assets/js/fs-resize.js', array(), false, $in_footer = true);
    wp_enqueue_script('imagesloaded', FANCY_URL . '/assets/js/imagesloaded.pkgd.js', array(), false, $in_footer = true);
    wp_enqueue_script('infinitescroll', FANCY_URL . '/assets/js/jquery.infinitescroll.min.js', array('jquery'), false, $in_footer = true);
    wp_enqueue_script('theia-sticky-sidebar', FANCY_URL . '/assets/js/theia-sticky-sidebar.js', array(), false, $in_footer = true);
    wp_enqueue_script('fs-custom', FANCY_URL . '/assets/js/fs-custom.js', array(), false, $in_footer = true);
    // Launch of the infinite scroll feature or so active trigger (compulsory motor and placed after $ $ correctionsmoteur!)
    // Launch of the function of auto completion so active ...
    if ($select->autoCompleteActive == 1) {
        $autocompletion = new autoCompletion($wpdb, FANCY_URL . "/includes/class.inc/autocompletion/autocompletion-PHP5.5.php", $selector, $tableName, $tableColumn, $multiple, $limitDisplay, $type, $autoFocus, $create, $encoding);

        // Starting automatic filling function of the inverted index (if enabled)
        if ($select->autoCompleteGenerate == true) {
            $autocompletion->autoComplete(stripslashes($_GET[$nameSearch]), $select->autoCompleteSizeMin);
        }
    }

    // Showing results if the engine is running!

    function fs_display($query, $nbResults, $words) {
        global $select, $wpdb, $moteur, $wp_rewrite, $correctionsmoteur, $autocorrect, $fs_post_type;

        $outputBeg = '';
        $outputBeg .= '<div class="fs-beforeresults">' . "\n";

        // Conditional display corrections
        if ($select->autoCorrectActive == true && ($select->autoCorrectType == 0 || $select->autoCorrectType == 2)) {
            if (!empty($correctionsmoteur)) {
                $outputBeg .= "<p class=\"fs-corrections\">" . $select->autoCorrectString . $correctionsmoteur . "</p>\n";
            }
        }

        if ($nbResults == 0) {
//                $outputBeg .= "</div>\n";
            $output = "<div class=\"fs-blocksearch\">\n";
            $output .= '<p class="fs-errorsearch">' . __($select->ErrorText, 'fancy-search') . '</p>' . "\n";
            $output .= "</div>\n";
            $output .= "<script type='text/javascript'>jQuery(document).ready(function(){ jQuery('.fs-pagenavi, .fs-loadmore').remove(); });</script>\n";
        } else {
            $output = '';
            $nb = 0;
            if (isset($_GET['page'])) {
                $nb = $nb + ($select->NumberPerPage * ($_GET['page'] - 1));
            }

            // View the number of results
            if ($select->nbResultsOK == true) {
                $displayResultats = new affichageResultats();
                if ($select->NumberPerPage == 0 || $select->paginationType != 'classic') {
                    if ($select->autoCorrectActive == true && !empty($correctionsmoteur) && $autocorrect) {
                        $outputBeg .= $displayResultats->nbResultats(true, array(__('result', 'fancy-search'), __('results', 'fancy-search')), __('with automatic correction of research', 'fancy-search'), __(' to ', 'fancy-search'));
                    } else {
                        $outputBeg .= $displayResultats->nbResultats(true, array(__('result', 'fancy-search'), __('results', 'fancy-search')), __('Your search', 'fancy-search'), __(' to ', 'fancy-search'));
                    }
                } else {
                    if ($select->autoCorrectActive == true && !empty($correctionsmoteur) && $autocorrect) {
                        $outputBeg .= $displayResultats->nbResultats(false, array(__('result', 'fancy-search'), __('results', 'fancy-search')), __('with automatic correction of research', 'fancy-search'), __(' to ', 'fancy-search'));
                    } else {
                        $outputBeg .= $displayResultats->nbResultats(false, array(__('result', 'fancy-search'), __('results', 'fancy-search')), __('Your search', 'fancy-search'), __(' to ', 'fancy-search'));
                    }
                }
            }

            $outputBeg .= "<div class='fs-viewas'>\n";
            $outputBeg .= "<div class='fs-list'>\n";
            $outputBeg .= "<i class='icon-list'></i>\n";
            $outputBeg .= "<span>list style</span>\n";
            $outputBeg .= "</div>\n";
            $outputBeg .= "<div class='fs-grid'>\n";
            $outputBeg .= "<i class='icon-icons'></i>\n";
            $outputBeg .= "<span>grid style</span>\n";
            $outputBeg .= "</div>\n";
            $outputBeg .= "</div>\n";

            $outputBeg .= "</div>\n";
            $outputBeg .= "<div class='clearBlock'></div>";
            $outputBeg .= '<div class="FancySearch' . '" id="results-' . $nbResults . '">' . "\n";

            // echo '<pre>'; print_r($query); exit;

            foreach ($query as $key) { // Results of the loop (WordPress Version) is launched
                // Retrieving the result number
                $nb++;

                // Loop useful whether to add (utf8_encode) example (not enabled by default ...)
                foreach ($key as $k => $v) {
                    $key[$k] = $v;
                }

                // Find images at A, categories and authors
                $tableCible = $wpdb->posts; // Retrieving the data base table to go (here "posts" for those pages and articles)
                $tableMeta = $wpdb->postmeta; // Metal recovery for the image to a
                $tableRelationship = $wpdb->term_relationships; // Retrieving taxonomy relations
                $tableTaxonomy = $wpdb->term_taxonomy; // Retrieving terms of taxonomy
                $tableTerms = $wpdb->terms; // Retrieving words
                $tableUsers = $wpdb->users; // Retrieving authors
                $ImageOK = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $tableCible . " AS p INNER JOIN " . $tableMeta . " AS m1 ON (m1.post_id = '" . $key['ID'] . "' AND m1.meta_value = p.ID AND m1.meta_key = '_thumbnail_id' AND p.post_type = 'attachment')", 'foo'));
                $CategoryOK = $wpdb->get_results($wpdb->prepare("SELECT name FROM " . $tableTerms . " AS terms LEFT JOIN " . $tableTaxonomy . " AS tax ON (terms.term_id = tax.term_id AND tax.taxonomy = 'category') INNER JOIN " . $tableRelationship . " AS rel ON (tax.term_taxonomy_id = rel.term_taxonomy_id) WHERE rel.object_id = '" . $key['ID'] . "'", 'foo'));
                $AuthorOK = $wpdb->get_results($wpdb->prepare("SELECT users.ID, user_nicename, display_name FROM " . $tableUsers . " AS users INNER JOIN " . $tableCible . " AS p ON users.ID = p.post_author WHERE p.ID = '" . $key['ID'] . "'", 'foo'));

                // Number of category (if more, we will display differently)
                $nbCategory = count($CategoryOK);

                // global block
                if ($nb % 2 == 1 && $nb % 3 != 1) {
                    $output .= "\n<div class=\"fs-blocksearch clear-2\" id=\"" . $nb . "\">\n";
                } elseif ($nb % 3 == 1 && $nb % 2 != 1) {
                    $output .= "\n<div class=\"fs-blocksearch clear-3\" id=\"" . $nb . "\">\n";
                } elseif ($nb % 6 == 1) {
                    $output .= "\n<div class=\"fs-blocksearch clear-6\" id=\"" . $nb . "\">\n";
                } else {
                    $output .= "\n<div class=\"fs-blocksearch\" id=\"" . $nb . "\">\n";
                }


                // Conditioning display the date and title
                if ($select->TitleOK == true && $select->NumberOK == true) {
                    $output .= '<div class="fs-firstsearch">' . "\n";
                    $output .= '<p class="fs-titlesearch"><a href="' . get_permalink($key['ID']) . '"><i>' . $nb . '.</i>' . $key['post_title'] . '</a></p>' . "<p class='clearBlock'></p>\n";
                    $output .= '</div>' . "\n";
                } else if ($select->TitleOK == true && $select->NumberOK == false) {
                    $output .= '<div class="fs-firstsearch">' . "\n";
                    $output .= '<p class="fs-titlesearch"><a href="' . get_permalink($key['ID']) . '">' . $key['post_title'] . '</a></p>' . "\n";
                    $output .= '</div>' . "\n";
                } else if ($select->TitleOK == false && $select->NumberOK == true) {
                    $output .= '<div class="fs-firstsearch">' . "\n";
                    $output .= '<p class="fs-numbersearch">' . $nb . '</p>' . "\n";
                    $output .= '</div>' . "\n";
                }

                //  Post thumbnail
                if ($select->ImageOK == true) {
                    $thumbnail = get_the_post_thumbnail($key['ID'], 'medium');
                    if (isset($thumbnail) && !empty($thumbnail)) {
                        $output .= '<a class="fs-thumbnail" href="' . get_permalink($key['ID']) . '">';
                        $output .= $thumbnail;
                        $output .= '</a>';
                    } else {
                        $output .= '<a class="fs-no-thumbnail" href="' . get_permalink($key['ID']) . '"></a>';
                    }
                }

                // Viewing time block for + author + category
                if ($select->DateOK == true || $select->AuthorOK == true || $select->CategoryOK == true) {
                    //$output .= 'sumaya';
                    //print_r($key);
                    $output .= '<p class="fs-secondsearch">' . "\n";
                    if ($select->DateOK == true || $select->AuthorOK == true || $select->CategoryOK == true) {
                        $output .= '<span class="fs-datesearch">' . __('Published ', 'fancy-search') . '</span>';
                    }
                    if ($select->BlocOrder == "D-A-C") { // Order: Date - Title - Category
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                    } else // Order: Date - Category - Author
                    if ($select->BlocOrder == "D-C-A") {
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                    } else // Order: Author - Category - Date
                    if ($select->BlocOrder == "A-C-D") {
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                    } else // Order: Author - Date - Category
                    if ($select->BlocOrder == "A-D-C") {
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                    } else // Order: Category - Date - Author
                    if ($select->BlocOrder == "C-D-A") {
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                    } else // Order: Category - Author - Date
                    if ($select->BlocOrder == "C-A-D") {
                        // Display category Conditioning
                        if ($select->CategoryOK == true) {
                            if ($nbCategory > 0) {
                                $output .= '<span class="fs-categorysearch">' . __('in ', 'fancy-search') . "\n";
                            }
                            $counter = 0;
                            foreach ($CategoryOK as $ctg) {
                                $categoryID = get_cat_ID($ctg->name);
                                $categoryURL = get_category_link($categoryID);
                                $output .= '<a href="' . esc_url($categoryURL) . '">' . $ctg->name . '</a>';
                                if ($nbCategory > 1 && $counter < ($nbCategory - 1)) {
                                    $output .= ", \n";
                                }
                                $counter++;
                            }
                            if ($nbCategory > 0) {
                                $output .= '</span>' . "\n";
                            }
                        }
                        // The author of conditional display
                        if ($select->AuthorOK == true) {
                            foreach ($AuthorOK as $author) {
                                $authorURL = get_author_posts_url($author->ID, $author->user_nicename);
                                $output .= '<span class="fs-authorsearch">' . __('by ', 'fancy-search') . '<a href="' . esc_url($authorURL) . '">' . $author->display_name . '</a></span>' . "\n";
                            }
                        }
                        // Viewing conditioning date
                        if ($select->DateOK == true) {
                            $dateInfo = mysql2date($select->formatageDate, $key['post_date']);
                            $output .= '<span class="fs-datesearch">' . __('the ', 'fancy-search') . $dateInfo . '</span>' . "\n";
                        }
                    }

                    // Conditional display comments
                    if ($select->CommentOK == true) {
                        if ($key['comment_count'] == 0) {
                            $output .= '<span class="fs-commentsearch"><a href="' . get_permalink($key['ID']) . '#comments">' . __('No comment', 'fancy-search') . '</a></span>' . "\n";
                        } else if ($key['comment_count'] == 1) {
                            $output .= '<span class="fs-commentsearch"><a href="' . get_permalink($key['ID']) . '#comments">' . $key['comment_count'] . ' ' . __('comment', 'fancy-search') . '</a></span>' . "\n";
                        } else {
                            $output .= '<span class="fs-commentsearch"><a href="' . get_permalink($key['ID']) . '#comments">' . $key['comment_count'] . ' ' . __('comments', 'fancy-search') . '</a></span>' . "\n";
                        }
                    }

                    $output .= '</p>' . "\n";
                }

//echo $output; exit;
                // Conditional display section, of the extract and of the image to the A
                if (($select->ArticleOK == "excerpt" || $select->ArticleOK == "excerptmore" || $select->ArticleOK == "article")) {
                    // $output .= 'hira';
                    $output .= '<div class="fs-blockcontent">' . "\n";

                    if (isset($_GET['s']) && !empty($_GET['s'])) {
                        //$num = 15 - strlen($_GET['s']);
                        //$div = round($num / 2);
                        //list($a, $b) = explode($_GET['s'], $key['post_content']);
                        //$description = $a.' '.$_GET['s'].' '.$b;
                        // wp_trim_words($a, $div);
                        // wp_trim_words($b, $div);
                        //$description = wp_trim_words($a, $div) . ' ' . $_GET['s'] . ' ' . wp_trim_words($b, $div);
                        $sentences = explode('.', $key['post_content']);
                        $description = '';
                        foreach ($sentences as $sentence) {
                            $offset = stripos($sentence, $_GET['s']);
                            if ($offset) {
                                $description .= $sentence.'.';
                            }
                        }
                    } else {
                        $description = wp_trim_words($key['post_content'], 15);
                    }



                    if ($select->ArticleOK == "excerpt") {
                        //if (isset($key['post_excerpt']) && !empty($key['post_excerpt'])) {
                        $output .= '<div class="fs-textsearch">' . "\n";
                        if ($description == '') {
                            $output .= wp_trim_words($key['post_excerpt'], 15);
                        } else {
                            $output .= $description;
                        }
                        $output .= '</div>' . "\n";
                        //}
                    } else if ($select->ArticleOK == "excerptmore") {
                        //if (isset($key['post_excerpt']) && !empty($key['post_excerpt'])) {
                        $output .= '<div class="fs-textsearch">' . "\n";
                        if ($description == '') {
                            $output .= wp_trim_words($key['post_excerpt'], 15);
                        } else {
                            $output .= $description;
                        }

                        $output .= '</div>' . "\n";
                        //}
                        $output .= '<div class="fs-readmoresearch"><a href="' . get_permalink($key['ID']) . '">' . __('Read more...', 'fancy-search') . '</a></div>' . "\n";
                    } else if ($select->ArticleOK == "article" && $fs_post_type != 'page') {
                        // if (isset($key['post_content']) && !empty($key['post_content'])) {
                        if ($description == '') {
                            $output .= '<div class="fs-textsearch">' . $key['post_content'] . '</div>' . "\n";
                        } else {
                            $output .= '<div class="fs-textsearch">' . $description . '</div>' . "\n";
                        }
                        //}
                    }
                    $output .= '<div class="clearBlock"></div>' . "\n";
                    $output .= '</div>' . "\n";

                    // Conditional display of the image on a title or without extract (not recommended)
                } else if ($select->ArticleOK == "no") {
                    $output .= '';
                } else {
                    $output .= $description;
                }
                $output .= "</div>\n";
            }
            // Whether to use the Highlight
            if ($select->strongWords != 'no') {
                $strong = new surlignageMot($words, $output, $select->strongWords, $select->exactSearch, $select->typeSearch);
                $output = $strong->contenu;
            }
        } //echo $output;exit;
        $outputEnd = "</div>\n"; // End BlockSearch
        // Return the complete results search engine
        echo $outputBeg . $output . $outputEnd;
    }

// End of the display callback function

    $fs_typeclass = array();
    ?>
    <div id="fancysearch-content" class="fancysearch-content fancysearch-list">
        <div class="fs-loadajax"><img src="<?php echo FANCY_URL . '/assets/img/loading-lightbox.GIF'; ?>" alt="Fancy Search"/></div>
        <div class="fs-header">
            <div class="fs-container">
                <div class="fs-header-title">
                    <?php
                    if (!empty($select->ResultText)) {
                        echo('<h2>' . trim(__($select->ResultText, 'fancy-search')) . ' <em>' . get_search_query() . '</em></h2>');
                    }
                    ?>
                    <form method="get" class="fs-searchform" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="text" class="field fssearchform inputbox search-query fs-search-input" name="s" placeholder="<?php esc_attr_e('Search...', 'fancy-search'); ?>" />
                        <button type="submit" class="submit fs-submit searchsubmit" name="submit" value="" ><i class="icon-magnifier"></i></button>
                    </form>
                </div>
            </div>
            <span class='fs-close-lightbox icon-cross'></span>
        </div>
        <div class="fs-container">
            <div class="fs-maincontent">
                <div class="fs-menu">
                    <ul id="fs-nav" class="fs-nav theiaStickySidebar">
                        <li class="fs-menu-type"><?php do_action('fs_addons_menu_item_post'); ?></li>
                        <!--<li class="fs-menu-type"><?php do_action('fs_addons_menu_item_page'); ?></li>-->
                    </ul>
                </div>
                <div id="fs_append" class="fs-content">
                    <!--                        <div class="theiaStickySidebar">-->
                    <?php
                    $action = $_GET['action'];

                    if (isset($action)) {
                        do_action('fs_addons_content_' . $action);
                    } else {
                        do_action('fs_addons_content_post');
                    }
                    ?>
                    <!--                        </div>-->
                </div>
            </div>
        </div>
    </div><?php
}

// Added a shortcode [fancy-search] -> echo do_shortcode ('[fancy-search]');
add_shortcode('fancy-search', 'Fancy_Search');
?>