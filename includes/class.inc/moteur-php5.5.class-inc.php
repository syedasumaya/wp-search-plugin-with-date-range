<?php
/*
Class Name: SearchEnginePOO WordPress
Creator: Mathieu Chartier
Website: http://blog.internet-formation.fr/2013/09/moteur-de-recherche-php-objet-poo-complet-pagination-surlignage-fulltext/
Note: PHP 5.5 compatible
Version: 2.4
Date: 15 juillet 2015
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*--------------------------------------------------------------------------*/
/*------------------------- Class Search Engine ----------------------------*/
/*-- 1. Start $ engine = new Search Engine (args); -------------------------*/
//-- 2. motor-Run $> Engine Queries (Table Columns Where) ; ----------------*/
//-- 3. Create a display function (eg, "display ()") -----------------------*/
//-- 4. Motor-launch $> Engine Display (display '$ Select columns); --------*/
//-- NB: if needed add $ motor-> Motor Pagination (args) -> if $ _GET ------*/
/*--------------------------------------------------------------------------*/
class moteurRecherche {
	private $db;				// Variable connection (mysql object!)
	private $tableBDD;			// Name of the database table
	private $encode;			// Encoding type ("utf-8" or "iso-8859-1" in particular)
	private $searchType;		// Search type ("Like", "regexp" or "full text")
	private $exactmatch;		// Search method (specific or approaching -> true or false)
//	private $stopWords;			// Table of stop words
	private $exclusion;			// Minimum number of letters for words (except below)
	private $accents;			// Search with or without accent (true or false)
	private $colonnesWhere;		// Table containing the columns in which the search is performed
	private $algoRequest;		// Array containing each word or key phrase (after cutting)
	private $request;			// Array containing each word or key phrase (after cutting)
	private $allExpressions;	// Array containing each word or key phrase (before cleaning)
	private $motsExpressions;	// Array containing each word or key phrase (after cleaning)
	private $condition;			// WHERE the whole of the final query

	private $tableIndex;		// Table name the index of correct words (for automatic correction)
	public $motsCorriges;		// Table corrected words (if the method is used)
	public $requeteCorrigee;	// Full request but corrected

	private $orderBy;			// Table components of the ORDER BY personalized (if callback function)
	private $limitMinMax;		// Table components of personalized LIMIT (if callback function)
	static $limitArg;			// Starting number LIMIT (0 by default)
	static $limit;				// Number of results per page (if paging to "true")

	public $requete;			// User search query
	public $countWords;			// Number of words and phrases that make up the query
	public $nbResults;			// The number of results via the database (LIMIT 0, $ engine-> nbResults shows)
	static $nbResultsChiffre;	// The number of results but figure this time (for paging)
	public $requeteTotale;		// Final SQL query (after algorithm, etc.)

	/*------------------------------------------------------------------------------------*/
	/*------------------------ Constructor of the class (9 parameters)--------------------*/
	/*-- 1. $ db is the mysqli or pdo connection (required for PHP 5.5) ------------------*/
	/*-- 2. $ field is the search query --------------------------------------------------*/
	/*-- 3. $ table is the database table in which to look -------------------------------*/
	/*-- $ 4. Type Search for choosing a search mode (like, regexp or fulltext) ----------*/
	/*-- 5. $ stopwords to exclude the words "empty" from a table ------------------------*/
	/*-- => Include stopwords.php file (variable $ stopwords) to save time ---------------*/
	/*-- 6. $ exclusion to exclude words shorter than the given size ---------------------*/
	/*-- => If empty, no exception will be made (but less accurate) ----------------------*/
	/*-- 7. $ encoding is the desired encoding (utf8, utf-8 iso-8859-1 latin1 ...) -------*/
	/*-- 8. exact $ (true / false) for an exact search or one or more of the words -------*/
	/*-- 9. $ accent (true / false) to search the database without accent permits --------*/
	/*------------------------------------------------------------------------------------*/
	public function __construct($bdd = '', $champ = '', $table = '', $typeRecherche = 'regexp', $exclusion = '', $encoding = 'utf-8', $exact = true, $accent = false) {
		$this->db			= $bdd;
		$this->requete		= trim($champ);
		$this->tableBDD		= $table;
		$this->encode		= strtolower($encoding);
		$this->searchType	= $typeRecherche;
		$this->exactmatch	= $exact;
//		$this->stopWords	= $stopWords;
		$this->exclusion	= $exclusion;
		$this->accents		= $accent;

		// Removing HTML tags (security)
		if($this->encode == 'latin1' || $this->encode == 'Latin1' || $this->encode == 'latin-1' || $this->encode == 'Latin-1') {
			$mb_encode = "ISO-8859-1";
		} elseif($this->encode == 'utf8' || $this->encode == 'UTF8' || $this->encode == 'utf-8' || $this->encode == 'UTF-8') {
			$mb_encode = "UTF-8";
		} else {
			$mb_encode = $encoding;
		}
		$champ = mb_strtolower(strip_tags($champ), $mb_encode);
//		$champ = mb_convert_case(strip_tags($champ), MB_CASE_LOWER, $mb_encode);

		// 1. if an expression is in quotes, one seeks the complete expression (of words)
		// 2. if the keywords are off the quotes, the search word by word is activated
		if(preg_match_all('/["]{1}([^"]+[^"]+)+["]{1}/i', $champ, $entreGuillemets)) {
			// Adds all phrases in quotes in a table
			foreach($entreGuillemets[1] as $expression) {
				$results[] = $expression;
			}
			// Retrieves words that are not quoted in an array
			$sansExpressions = str_ireplace($entreGuillemets[0],"",$champ);
			$motsSepares = explode(" ",$sansExpressions);

			// Retrieving words for correcting results!
			$totalResults = array_merge($entreGuillemets[0], $motsSepares);
		} else {
			$motsSepares = explode(" ",$champ);
			$totalResults = explode(" ",$champ); // Useful for correcting the results!
		}

		// Save the list of words before "cleaning" of the request (stop words, etc.)
		foreach($totalResults as $key => $value) {
			// Removes empty channels of the table (and therefore excluded words)
			if(empty($value)) {
				unset($totalResults[$key]);
			}
			$this->allExpressions = $totalResults;
		}

		// Remove empty array keys (because of too many spaces and strip_tags)
		foreach($motsSepares as $key => $value) {
			// Replaces excluded words (too short) with empty strings
			if(!empty($exclusion)) {
				if(strlen($value) <= $exclusion) {
					$value = '';
				}
			}
			// Removes stops if any words
//			if(!empty($stopWords)) {
//				if(in_array($value, $stopWords)) {
//					$value = '';
//				}
//			}
			// Removes empty channels of the table (and therefore excluded words)
			if(empty($value)) {
				unset($motsSepares[$key]);
			}
		}
		// Add every single word in the word list to search
		foreach($motsSepares as $motseul) {
			$results[] = $motseul;
		}

		// If the array of words and expressions is not empty, then it seeks ... (if no results!)
		if(!empty($results)) {
			// Cleans each field to avoid the risk of piracy ...
			for($y=0; $y < count($results); $y++) {
				$expression = $results[$y];

				// Search Keyword original accent insensitive if the option is activated
				if($accent == false) {
					$recherche[] = htmlspecialchars(trim(strip_tags($expression)));
				} else {
					$withaccent = array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');
					$withnoaccent = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y');
					$recherche[] = str_ireplace($withaccent, $withnoaccent, htmlspecialchars(trim(strip_tags($expression))));
				}
			}
		} else {
			$recherche = array('');
		}

		$this->algoRequest = $recherche; // An array containing words and phrases of the query (duplicate useful!)
		$this->request = $recherche; // An array containing words and expressions of the user query
		$this->countWords = count($recherche,1); // number of words in the query
	}

	/*--------------------------------------------------------------------------------*/
	/*-------- Subject (private) escape special characters (in regex) ----------------*/
	/*--------------------------------------------------------------------------------*/
	private function regexEchap($regex = '([\+\*\?])') {
		if(preg_match($regex, $mot)) {
			$mot = str_ireplace(array('+','*','?'),array('\+','\*','\?'),$mot);
		}
	}

	/*--------------------------------------------------------------------------------*/
	/*--------- Method (private) to process the search request -----------------------*/
	/*--------------------------------------------------------------------------------*/
	private function requestKey($val) {
		/*---------- Adaptation of the charset (UTF-8 is preferred) -------------*/
		if($this->encode == 'utf8' || $this->encode == "utf-8") {
			$encode = "utf8";
		} else if($this->encode == 'iso-8859-1' || $this->encode == "iso-latin-1" || $this->encode == "latin1") {
			$encode = "latin1";
		} else {
			$encode = "utf8";
		}

		/*------------------------- Search options -------------------------*/
		switch($this->searchType) {
			/*------------------------ FULL TEXT Search --------------------------*/
			/*-- Research performance but set ... --------------------------------*/
			/*-- Edit (or add) ft_min_word_len = 1 for short words ---------------*/
			/*-- (situated in the [mysqld] section of the my.ini file MySQL ------*/
			/*--------------------------------------------------------------------*/
			case "FULLTEXT":
			case "fulltext":
				foreach($this->request as $this->request[$val]) {
					if(preg_match('/(^[+-?!:;$^])|([+-?!:;^]$)/i',$this->request[$val])) {
						$this->request[$val] = str_ireplace(array("+", "-", "?", "!", ";", ":", "^"),"",$this->request[$val]);
					}

					// If a plus sign is in the string, to understand the word as exact
					if(preg_match("/([+])+/i",$this->request[$val])) {
						$this->request[$val] = str_ireplace(array("+"),array(" "),$this->request[$val]);
						$this->request[$val] = preg_replace('/('.$this->request[$val].')/', '"$1"', $this->request[$val]);
						$this->request[$val] = str_ireplace(array(" "),array("+"),$this->request[$val]);
					}

					// If the string contains a space (therefore quotes) or binding character ('or -)
					if(preg_match("/([[:blank:]-'])+/i",$this->request[$val])) {
						$this->request[$val] = preg_replace('/('.$this->request[$val].')/i', '"$1"', $this->request[$val]);
					}

					// Adds a front exhaust apostrophes lying around ...
					$this->request[$val] = str_ireplace(array("'"),array("\'"),$this->request[$val]);

					// Adds each word or phrase in a table
					$valueModif[] = $this->request[$val];

					// Variable used when highlighting words ...
					$this->motsExpressions = $valueModif;
				}

				if($this->exactmatch == true) {
					$this->request[$val] = implode(' +', $valueModif);
					return " AGAINST(CONVERT(_".$encode." '+".$this->request[$val].")' USING ".$encode.") IN BOOLEAN MODE) ";
				} else {
					$this->request[$val] = implode(' ', $valueModif);
					return " AGAINST(CONVERT(_".$encode." '".$this->request[$val]."' USING ".$encode.") IN BOOLEAN MODE) ";
				}
				break;


			/*-------------------------- search REGEX ----------------------------*/
			/*---------- Search with a regex (only complete words work) ----------*/
			/*--------------------------------------------------------------------*/
			case "REGEXP":
			case "regexp":
				// Variable used when highlighting words ...
				$this->motsExpressions = $this->request;
				if(preg_match("/^[+\?$\*§\|\[\]\(\)]/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],1,strlen($this->request[$val]));
				}
				if(preg_match("/[+\?$\*§\|\[\]\(\)]$/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],0,-1);
				}
				if(preg_match("/^[²°]/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],1,strlen($this->request[$val]));
				}

				if($this->exactmatch == true) {
					return " REGEXP CONVERT(_".$encode." '[[:<:]]".addslashes($this->request[$val])."[[:>:]]' USING ".$encode.") ";
				} else {
					return " REGEXP CONVERT(_".$encode." '".addslashes($this->request[$val])."' USING ".$encode.") ";
				}
				break;

			/*-------------------------- search LIKE -----------------------------*/
			/*-- But most precise functional research ----------------------------*/
			/*--------------------------------------------------------------------*/
			case "LIKE":
			case "like":
				// Variable used when highlighting words ...
				$this->motsExpressions = $this->request;
				if(preg_match("/^[\(]/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],1,strlen($this->request[$val]));
				}
				if(preg_match("/[\)]$/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],0,-1);
				}

				return " LIKE CONVERT(_".$encode." '%".addslashes($this->request[$val])."%' USING ".$encode.") ";
				break;

			default:
				// Variable used when highlighting words ...
				$this->motsExpressions = $this->request;
				if(preg_match("/^[\(]/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],1,strlen($this->request[$val]));
				}
				if(preg_match("/[\)]$/i",$this->request[$val])) {
					$this->request[$val] = substr($this->request[$val],0,-1);
				}

				return " LIKE CONVERT(_".$encode." '%".addslashes($this->request[$val])."%' USING ".$encode.") ";
				break;
		}
	}

	/*---------------------------------------------------------------------------*/
	/*-- Design method of the search query with one parameter -------------------*/
	/*-- 1. Table columns in which to look (... WHERE condition) ----------------*/
	/*---------------------------------------------------------------------------*/
	public function moteurRequetes($colonnesWhere = array()) { //print_r($this->requestKey); exit;
		$this->colonnesWhere = $colonnesWhere;
		// Operator between the query fields (OR if you want a lot of laxity)
		$operateur = "AND";
		// Operator in a query (AND if you absolutely want the word is in multiple columns SQL)
		$operateurGroupe = "OR";
		// Total number of columns in which SQL search
		$nbColumn= count($colonnesWhere,1);
                
                       if($_GET['start_date']!='' && $_GET['end_date']!='') {
                             $start_date = date('Y-m-d', strtotime($_GET['start_date']));
                             $end_date = date('Y-m-d', strtotime($_GET['end_date']));
                       } 
                       
                       if($_GET['start_date']!='' && $_GET['end_date'] =='') {
                            $start_date = date('Y-m-d', strtotime($_GET['start_date']));
                            $end_date = date('Y-m-d');
                       }

                       if($_GET['start_date']=='' && $_GET['end_date'] !='') {
                            $start_date = '1970-01-01';
                            $end_date = date('Y-m-d', strtotime($_GET['end_date']));
                       }
                        
                       if($_GET['start_date'] =='' && $_GET['end_date'] =='') {
                          $start_date = '';
                          $end_date = '';
                    
                         }
                      if($_GET['s'] != ''){
                          $con = 'AND';  
                      } else {
                          $con = 'OR';
                      }

		/*--------------------------------------------------------------------------------*/
		/*-- Adapts the SQL search by type search selected -------------------------------*/
		/*--------------------------------------------------------------------------------*/
                
                    if($this->searchType == "LIKE" || $this->searchType == "REGEXP" || $this->searchType == "like" || $this->searchType == "regexp") { // Si recherche "like" ou "regexp"
                    //print_r($_GET['s']);
                     
                        
			$query = " (";
			$query .= $colonnesWhere[0].$this->requestKey(0); //echo $query; exit;
			if($nbColumn > 1) { //echo 222; exit;
				for($nb=1; $nb < $nbColumn; $nb++) {
                        
					$query .= $operateurGroupe." ".$colonnesWhere[$nb].$this->requestKey(0);
				}//echo $colonnesWhere[3]; exit;
			}
			$query .= ") ";

			if($this->countWords > 1) {
				for($i=1; $i < $this->countWords; $i++) {
					$query .= $operateur." (".$colonnesWhere[0].$this->requestKey($i);
					if($nbColumn > 1) {
						for($nb=1; $nb < $nbColumn; $nb++) {
							$query .= $operateurGroupe." ".$colonnesWhere[$nb].$this->requestKey($i);
						}
					}
					$query .= ") ";
				}
			}

		} else { //if research "full text"
			$colonnesStrSQL = implode(', ',$colonnesWhere);
			$query = " MATCH (".$colonnesStrSQL.")".$this->requestKey(0);
		}
              
                if($start_date != '' && $end_date !='') { //echo 333;
                  //$query .= "OR (".$colonnesWhere[3]." BETWEEN '".$start_date."' AND '".$end_date."')";   
                  $query .= " ".$con." ";
                  $query .= "('".$start_date."' <= DATE(".$colonnesWhere[3].") AND '".$end_date."' >= DATE(".$colonnesWhere[3]."))";       
                }
$query .= "AND post_status = 'publish'";
//echo $query; exit;
		// recovery of the search engine query
		$this->condition = $query;
	}

	/*---------------------------------------------------------------------------------*/
	/*----------- Display based on the results (with callback) ------------------------*/
	/*-------- 6 possible arguments ... -----------------------------------------------*/
	/*-- 1. appeal to the callback function display (required) ------------------------*/
	/*-- 2. select columns in the database (all if left "empty") ----------------------*/
	/*-- 3. LIMIT in SQL: Table with 4 values: true / false, numDépart, interval ------*/
	/*-- true / false (the fourth value) -> true classic for pagination, false for other --*/
	/*-- 4. ORDER BY: table with 3 values: true / false, column order, ASC / DESC -----*/
	/*-- 5. ORDER BY with relevancy algorithm: Table with 4 values: -------------------*/
	/*-- => True / false, ranking column (unpublished!), ASC / DESC, ID column --------*/
	/*-- NB: The function adds the ranking column if it does not exist! ---------------*/
	/*-- 6. End of query profile: writing his own ORDER BY and / or LIMIT -------------*/
	/*-- 7. Additional condition used for WordPress -----------------------------------*/
	/*---------------------------------------------------------------------------------*/
	public function moteurAffichage($callback = '', $colonnesSelect = '', $limit = array(false, 0, 10, false), $ordre = array(false, "id", "DESC"), $algo = array(false,'algo','DESC','ID'), $orderLimitPerso = '', $conditionsPlus = '') {
        global $wpdb, $table_Fancy_Search;
		// Adding specific conditions for WordPress
		if(empty($conditionsPlus)) {
			$conditions = "WHERE";
		} else {
			$conditions = $conditionsPlus;
		}

		// Retrieving selections columns
		if(empty($colonnesSelect)) {
			$selectColumn = "*";
		} else if (is_array($colonnesSelect)) {
			$selectColumn = implode(", ",$colonnesSelect);
		} else {
			$selectColumn = $colonnesSelect;
		}

		// Limit the number of display per page
		if($limit[0] == true) {
			self::$limitArg = $limit[1];
			self::$limit	= $limit[2];

			if(!isset($limit[1])) {
				$limitDeb = 0;
			} else if($limit[1] == 0) {
				$limitDeb = $limit[1] * $limit[2];
			} else if($limit[3] == false) {
				$limitDeb = $limit[1];
			} else {
				$limitDeb = ($limit[1] - 1) * $limit[2];
			}
			$this->limitMinMax = " LIMIT $limitDeb, $limit[2]";
		} else {
			$this->limitMinMax = "";
		}
//echo "SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso", ARRAY_N; exit;
		// Relevancy algorithm (the more words in the results, more is high)
		$numberWP = $this->db->get_row("SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso", ARRAY_N);
        
		if($algo[0] == true && $numberWP[0] != 0) {
			// Adding a new column in the database to collect values of the algorithm
			$ifColumnExist = $this->db->get_row("SHOW COLUMNS FROM $this->tableBDD LIKE '".$algo[1]."'", ARRAY_N);
			$columnExist = $ifColumnExist;
			if($columnExist[0] != $algo[1]) {
				$addColumn = $this->db->query("ALTER TABLE $this->tableBDD ADD ".$algo[1]." DECIMAL(10,3)");
			}

			$colonnesStrSQL = implode(', ',$this->colonnesWhere);
			$requeteType = $this->db->get_results("SELECT $algo[3], $colonnesStrSQL FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso", ARRAY_N) or die("Algorithm error ! ".$this->db->show_errors());


			foreach($requeteType as $ligne) {
				$count = 0;
				for($p=1; $p < count($this->colonnesWhere)+1; $p++) {
					foreach($this->algoRequest as $mots) {
						$count += substr_count(utf8_encode(strtolower($ligne[$p])), strtolower($mots));
					}
				}

				// Updates the column of the algorithm with the new values
				$requeteAdd = $this->db->query("UPDATE $this->tableBDD SET $algo[1] = '$count' ".$conditions." $this->condition AND $algo[3] = '$ligne[0]'");
			}
		}

		// Displays the selection end custom query or conventional rankings
		if($algo[0] == true && $ordre[0] != true) {
			$this->orderBy = " ORDER BY $algo[1] $algo[2]";
		} else if($algo[0] == true && $ordre[0] == true) {
			// Combines the classic classification algorithm and if both are "true"
			$this->orderBy = " ORDER BY $algo[1] $algo[2], $ordre[1] $ordre[2]";
		} else {
			// Add order criteria (if the option is on the table "true")
			if($ordre[0] == true) {
				$this->orderBy = " ORDER BY $ordre[1] $ordre[2]";
			} else {
				$this->orderBy = "";
			}
		}

		/*-------------------------------------------------------------------*/
		/*------------------------- Total SQL query -------------------------*/
		/*-------------------------------------------------------------------*/
		if(empty($orderLimitPerso) && $numberWP[0] != 0) {
			$this->requeteTotale = $this->db->get_results("SELECT $selectColumn FROM $this->tableBDD ".$conditions." $this->condition $this->orderBy $this->limitMinMax", ARRAY_A) or die("<div>Error in the final application, make sure your complete parameterization !</div>");
			// To calculate the total number of correct results
			$this->nbResults = $this->db->get_results("SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition", ARRAY_N) or die("<div>Error in the counting of results (application problem) !</div>");
			$compte = $this->db->get_var("SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition") or die("<div>Error in the counting of results (application problem)!</div>");
		} else if(!empty($orderLimitPerso) && $numberWP[0] != 0) {
			if($limit[0] == true && $ordre[0] == true) {
				$this->requeteTotale = $this->db->get_results("SELECT $selectColumn FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso $this->orderBy $this->limitMinMax", ARRAY_A) or _e("<div>Error in the query, check if the search words are not a problem !</div>","fancy-search");

			} else if($limit[0] == true && $ordre[0] == false) {
				$this->requeteTotale = $this->db->get_results("SELECT $selectColumn FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso $this->limitMinMax", ARRAY_A) or _e("<div>Error in the query, check your settings !</div>","fancy-search");
			} else {
				$this->requeteTotale = $this->db->get_results("SELECT $selectColumn FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso", ARRAY_A) or _e("<div>Error in the application, make sure your complete parameterization !</div>","fancy-search");
			}
			// To calculate the total number of correct results
			$this->nbResults = $this->db->get_results("SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso", ARRAY_N);
			$compte = $this->db->get_var("SELECT count(*) FROM $this->tableBDD ".$conditions." $this->condition $orderLimitPerso");
		}

		$this->nbResults = $this->nbResults[0][0];

		// Recovering the number of results
		$compteTotal = $compte;
		self::$nbResultsChiffre = $compteTotal;

		// Displays the result of the callback function Callback
		if(!empty($callback)) {
			// Records the number of results used by the total request
			$nbResultats = $this->nbResults;

			// Call the callback function with four mandatory parameters !!!
			// 1. a variable choice to retrieve all of the query (table)
			// 2. a variable choice for the number of results returned by the total request
			// 3. varying the choice for all the words and phrases of the request
			    call_user_func_array($callback, array(&$this->requeteTotale, &$nbResultats, &$this->motsExpressions));
		} else {
			echo "<p>Warning ! No callback called to display results</p>";
		}
	}

	/*----------------------------------------------------------------------------------------------------*/
	/*------------------------------------- Correction results -------------------------------------------*/
	/*-- 1 parameter table Index (corrected table containing the words) ----------------------------------*/
	/*-- 2 GET parameter of the search (default: "s") ----------------------------------------------------*/
	/*-- 3 $select = true if the comparison of words is via the index table ------------------------------*/
	/*----------------------------------------------------------------------------------------------------*/
	public function getCorrection($tableIndex = "", $parametre = "s", $select = true) {
		// Check whether the index exists
		if(empty($tableIndex)) {
			$tableIndex = $this->tableIndex;
		}

		// Table of words to be checked
		$indexinverse = $this->db->get_results("SELECT * FROM ".$tableIndex, ARRAY_A);

		// Initialization useful information
		$queryTotal = array();
		$correction = array();
		$nb = 0;

		if(!empty($indexinverse) && !empty($_GET[$parametre])) {
			// For each word of the query, we test matches (Levenshtein distance)
			foreach($this->allExpressions as $mot) {
				// Metaphone soundex value of a word
				$metaphone = metaphone($mot);
				$soundex = soundex($mot);

				// Loop-array of words to compare the values
				foreach($indexinverse as $word) {
					// Remove the case to avoid problems reading
					$word['word'] = strtolower($word['word']);

					if($select !== true) {
						$metaphoneCompare = metaphone($word['word']);
						$soundexCompare = soundex($word['word']);
					} else {
						$metaphoneCompare = $word['metaphone'];
						$soundexCompare = $word['soundex'];
					}

					// It keeps only the words metaphone or Soundex is worth the
					if($mot != $word['word'] && ($metaphone == $metaphoneCompare || $soundex == $soundexCompare)) {
						$queryTotal[$nb] = "<strong>".$word['word']."</strong>";
						$newWords[$nb] = $word['word']; // Table of words (without bolding, etc.)
						$correction[] = true; // Specifies that a correction has taken place (to condition the display)
						break;
					}
				}

				// It also records the words not "corrected" to restore the full request
				if(empty($queryTotal[$nb])) {
					$queryTotal[$nb] = $mot;
					$newWords[$nb] = $mot; // Table of words (without bolding, etc.)
					$correction[] = false; // Specifies that a correction has not taken place (to condition the display)
				}
				$nb++;
			}
			// Formatting the complete corrected query
			$recherche = implode(" ", $queryTotal);

			// Retrieve the array of words corrected (if necessary external)
			$this->motsCorriges = $newWords;

			// Recovery of the corrected query
			$this->requeteCorrigee = implode(" ", $newWords);

			// It returns the result if there were at least a correction ($ corrected with at least one "true")
			if(in_array(true, $correction)) {
				return $this->queryStringToLink($recherche, $parametre);
			}
		}
	}

	// Private method to link the request containing the corrected words
	private function queryStringToLink($string, $queryArg = "q") {
		// Retrieving keywords in the Query String
		$queryString = urlencode($_GET[$queryArg]);

		// Current web address with Query String
		$url = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];

		// Replacing spaces with "+"
		$stringFormat = urlencode(strip_tags($string));

		// Changing Query String
		$fixedUrl = str_ireplace($queryString, $stringFormat, $url);

		// Formatting the final link
		$link = "<a href=".$fixedUrl.">".$string."</a>";

		return $link;
	}

	// Method to retrieve query results automatically corrected
	public function getCorrectedResults() {
		// Recovers corrected words (without fat, etc.)
		$this->setQuery($this->requeteCorrigee);

		// Relaunch the application with the "new words"
		$this->moteurRequetes($this->colonnesWhere);

		// Checks that good
		return true;
	}

	// Setter to change the query on the fly (useful Accessor for automatic correction)
	public function setQuery($query) {
		$this->getCleanQuery($query);
	}

	// Method to create an inverted index (if non-existent) and add words inside
	public function createIndex($tableName = '') {
		// Check whether the index exists
		if(empty($tableIndex)) {
			$tableName = $this->tableIndex;
		} else {
			$this->tableIndex = $tableName;
		}

		if(!empty($tableName)) {
			$createSQL = "CREATE TABLE IF NOT EXISTS ".$tableName." (
						 idWord INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						 word VARCHAR(200) NOT NULL,
						 metaphone VARCHAR(200) NOT NULL,
						 soundex VARCHAR(200) NOT NULL,
						 theme VARCHAR(200) NOT NULL,
						 coefficient FLOAT(4,1) NOT NULL DEFAULT '1.0')
						 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
			$this->db->query($createSQL) or die("Error creating the inverted index (method create Index)");
		}
	}

	// Method to add the words in the index with additional information
	public function setIndex($arrayWords = array(), $tableIndex = '') {
		// Check whether the index exists
		if(empty($tableIndex)) {
			$tableIndex = $this->tableIndex;
		}

		// Recovery of the words in the index (to avoid duplication)
		$selectWords = $this->db->get_results("SELECT word FROM ".$tableIndex, ARRAY_A);
		$selected = array();
		foreach($selectWords as $w) {
			$selected[] = $w['word'];
		}

		// Put the words one by one in the correct words with their corresponding index values
		foreach($arrayWords as $word) {
			// Adapts to add specific terms in the index as expected
			if(preg_match("#[[:blank:]]+#i", trim($word))) {
				$word = '"'.$word.'"';
			}

			// Adds that if the word or phrase does not exist
			if(!in_array($word, $selected)) {
				// Measurement values "metaphone" and "soundex" for each word
				$metaphone = metaphone($word);
				$soundex = soundex($word);

				// Add the data in the index table
				$prepare = $this->db->prepare("INSERT INTO ".$tableIndex." (word, metaphone, soundex) VALUES (%s, %s, %s)", array($word, $metaphone, $soundex));
				$this->db->query($prepare);
			}
		}
	}

	// Get the list of index data of correct words (words, métaphones, soundex ...)
	public function getIndex($tableIndex = "") {
		// Check whether the index exists
		if(empty($tableIndex)) {
			$tableIndex = $this->tableIndex;
		}

		// Select the entire index
		$all = $this->db->get_results("SELECT * FROM ".$tableIndex, ARRAY_A);

		// List all results
		$index = array();
		foreach($all as $word) {
			$index[] = $word;
		}

		// Returns the result
		if(!empty($index)) {
			return $index;
		} else {
			return false;
		}
	}

	// Check whether or not the table containing the correct word (two parameters: the table name, the name of the database)
	public function isIndex($tableIndex, $databaseName) {
		$this->tableIndex = $tableIndex;

		$getSQL = "SHOW TABLES FROM ".$databaseName." LIKE '".$tableIndex."'";
		$result = $this->db->query($getSQL);
		return $result->num_rows;
	}

	// The method of cleaning an application (duplicate for correction)
	private function getCleanQuery($query = "") {
		// Removing HTML tags (security)
		if($this->encode == 'latin1' || $this->encode == 'Latin1' || $this->encode == 'latin-1' || $this->encode == 'Latin-1') {
			$mb_encode = "ISO-8859-1";
		} elseif($this->encode == 'utf8' || $this->encode == 'UTF8' || $this->encode == 'utf-8' || $this->encode == 'UTF-8') {
			$mb_encode = "UTF-8";
		} else {
			$mb_encode = $encoding;
		}
		$champ = mb_strtolower(strip_tags($query), $mb_encode);

		// 1. if an expression is in quotes, one seeks the complete expression (of words)
		// 2. if the keywords are off the quotes, the search word by word is activated
		if(preg_match_all('/["]{1}([^"]+[^"]+)+["]{1}/i', $champ, $entreGuillemets)) {
			// Adds all phrases in quotes in a table
			foreach($entreGuillemets[1] as $expression) {
				$results[] = $expression;
			}
			// Retrieves words that are not quoted in an array
			$sansExpressions = str_ireplace($entreGuillemets[0],"",$champ);
			$motsSepares = explode(" ",$sansExpressions);

			// Retrieving words for correcting results!
			$totalResults = array_merge($entreGuillemets[0], $motsSepares);
		} else {
			$motsSepares = explode(" ",$champ);
			$totalResults = explode(" ",$champ); // Useful for correcting the results!
		}

		// Save the list of words before "cleaning" of the request (stop words, etc.)
		foreach($totalResults as $key => $value) {
			// Removes empty channels of the table (and therefore excluded words)
			if(empty($value)) {
				unset($totalResults[$key]);
			}
			$this->allExpressions = $totalResults;
		}

		// Remove empty array keys (because of too many spaces and strip_tags)
		foreach($motsSepares as $key => $value) {
			// Replaces excluded words (too short) with empty strings
			if(!empty($this->exclusion)) {
				if(strlen($value) <= $this->exclusion) {
					$value = '';
				}
			}
			// Removes stops if any words
//			if(!empty($this->stopWords)) {
//				if(in_array($value, $this->stopWords)) {
//					$value = '';
//				}
//			}
			// Removes empty channels of the table (and therefore excluded words)
			if(empty($value)) {
				unset($motsSepares[$key]);
			}
		}
		// Add every single word in the word list to search
		foreach($motsSepares as $motseul) {
			$results[] = $motseul;
		}

		// If the array of words and expressions is not empty, then it seeks ... (if no results!)
		if(!empty($results)) {
			// Cleans each field to avoid the risk of piracy ...
			for($y=0; $y < count($results); $y++) {
				$expression = $results[$y];

				// Search Keyword original accent insensitive if the option is activated
				if($this->accents == false) {
					$recherche[] = htmlspecialchars(trim(strip_tags($expression)));
				} else {
					$withaccent = array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý');
					$withnoaccent = array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y');
					$recherche[] = str_ireplace($withaccent, $withnoaccent, htmlspecialchars(trim(strip_tags($expression))));
				}
			}
		} else {
			$recherche = array('');
		}

		$this->algoRequest = $recherche; // An array containing words and phrases of the query (duplicate useful!)
		$this->request = $recherche; // An array containing words and expressions of the user query
		$this->countWords = count($recherche,1); // number of words in the query
	}
	/*----------------------------------------------------------------------------------------------------*/
	/*-------------------------------- End of methods for correcting results -----------------------------*/
	/*----------------------------------------------------------------------------------------------------*/

	/*----------------------------------------------------------------------------------------------------*/
	/*----------------------------------------- Paging function ------------------------------------------*/
	/*-------- 8 possible arguments (mandatory 2) --------------------------------------------------------*/
	/*-- 1. $instruction is $ _ GET ['page'] to $ _ POST ['page] -----------------------------------------*/
	/*-- 1. $param is the name of GET or POST parameter of the page ('page' default) ---------------------*/
	/*-- 2. $InVisible is the number of pages displayed around the current page --------------------------*/
	/*-- 3. $early end to display links (first and last pages) => 0 for empty ----------------------------*/
	/*-- 4. $Prev seq (true / false) to display or not "next page" and "previous page" -------------------*/
	/*-- 5. $Last first (true / false) to display or not "first page" and "last page" --------------------*/
	/*-- 6. $arrayAff is a table that contains the formatting elements (8 args) --------------------------*/
	/*-- => (Back, Forward, first page, LastPage, class PrécSuiv, classPage, classBloc, classInactif) ----*/
	/*-- 7. $Separator array is an array that contains the separator (5 args) ----------------------------*/
	/*-- => (pointSuspension, sepPremiereDernierePage, $sepNumPage, sepSuivPrec, sepDebutFin) ------------*/
	/*----------------------------------------------------------------------------------------------------*/
	public function moteurPagination($instruction = 0, $param = "page", $action ="post", $NbVisible = 2, $debutFin = 0, $suivPrec = true, $firstLast = true, $arrayAff = array('&laquo; previous', 'Next &raquo;', 'First page', 'Last page', 'precsuiv', 'current', 'pagination', 'inactif'), $arraySeparateur = array('&hellip;', ' ', ' ', ' ', ' ')) {

		// Total number of pages to be displayed (depending LIMIT)
		$nb_pages = ceil(self::$nbResultsChiffre / self::$limit);

		// Formatting the request (to avoid problems with the quotes)
		$this->requete = htmlspecialchars($this->requete);

		// Number current page (default 1)
		$parametreGetPost = self::$limitArg;
		if(isset($parametreGetPost) && is_numeric($parametreGetPost)) {
			if($parametreGetPost == 0) {
				$current_page = 1;
			} else {
				$current_page = $parametreGetPost;
			}
		} else {
			$current_page = 1;
		}

		// Retrieving settings URL formatting and links (setting of the page at the end)
		if(($instruction >= 0 && is_numeric($instruction) && $instruction < $nb_pages+1) || $instruction == 0) {
			preg_match_all('#([^=])+([^?&\#])+#i', $_SERVER['QUERY_STRING'], $valueArgs);
			$urlPage = $_SERVER['PHP_SELF'].'?';
			foreach($valueArgs[0] as $arg) {
				$urlPage .= $arg;
				$urlPage = str_replace("&action=".$action."&".$param."=".$parametreGetPost, "", $urlPage);
			}
            $urlPage .= "&action=".$action."&".$param."=";
			$urlPage = str_replace("?action=".$action."?".$param."=".$parametreGetPost."&", "?", $urlPage);
		} else {
			$urlpropre = str_ireplace("?".$param."=".$instruction,"?".$param."=1",$_SERVER['REQUEST_URI']);
			$urlpropre = str_ireplace("&".$param."=".$instruction,"&".$param."=1",$_SERVER['REQUEST_URI']);
			header('location:'.$urlpropre);
		}

		// Start the paging block (block with class)
		$pagination = '<div class="'.$arrayAff[6].'">';

		// If there is more than one page
		if($nb_pages > 1) {
			// Displaying the link "First page" before "previous page"
			if($firstLast == true) {
				for($i=1; $i<=1; $i++) {
					$pagination .= ($current_page==$i) ? '<span class="'.$arrayAff[4].' '.$arrayAff[7].'">'.$arrayAff[2].'</span>' : '<a href="'.$urlPage.$i.'">'.$arrayAff[2].'</a>';
					$pagination .= $arraySeparateur[1];
				}
			}

			// Link to the previous page (if $ precSuiv = true)
			if($suivPrec == true) {
				if ($current_page > 1) {
					$pagination .= '<a class="'.$arrayAff[4].'" href="'.$urlPage.($current_page-1).'" title="'.$arrayAff[0].'">'.$arrayAff[0].'</a>';
					$pagination .= $arraySeparateur[3];
				} else {
					$pagination .= '<span class="'.$arrayAff[4].' '.$arrayAff[7].'">'.$arrayAff[0].'</span>';
					$pagination .= $arraySeparateur[3];
				}
			}

			// Links) early (before previous and potential "...")
			for($i=1; $i<=$debutFin; $i++) {
				$pagination .= ($current_page==$i) ? '<span class="'.$arrayAff[5].'">'.$i.'</span>' : '<a href="'.$urlPage.$i.'">'.$i.'</a>';
				$pagination .= $arraySeparateur[4];
			}


			// "..." After the start
			if(($current_page-$NbVisible) > ($debutFin+1)) {
				$pagination .= ' '.$arraySeparateur[0];
			}

			// One loop around the current page
			$start = ($current_page-$NbVisible) > $debutFin ? $current_page-$NbVisible : $debutFin+1;
			$end = ($current_page+$NbVisible)<=($nb_pages-$debutFin) ? $current_page+$NbVisible : $nb_pages-$debutFin;

			for($i=$start; $i<=$end; $i++) {
				$pagination .= $arraySeparateur[2];
				if($i==$current_page) {
					$pagination .= '<span class="'.$arrayAff[5].'">'.$i.'</span>';
				} else {
					$pagination .= '<a href="'.$urlPage.$i.'">'.$i.'</a>';
				}
			}

			// "..." displayed before the end
			if(($current_page+$NbVisible) < ($nb_pages-$debutFin)) {
				$pagination .= ' '.$arraySeparateur[0];
			}

			// Link (s) end (before next page and before any "...")
			$start = $nb_pages-$debutFin+1;
			if($start <= $debutFin) { $start = $debutFin+1; }
			for($i=$start; $i<=$nb_pages; $i++) {
				$pagination .= $arraySeparateur[4];
				$pagination .= ($current_page==$i) ? '<span class="'.$arrayAff[5].'">'.$i.'</span>' : '<a href="'.$urlPage.$i.'">'.$i.'</a>';
			}

			// Link to the next page (if $ precSuiv = true)
			if($suivPrec == true) {
				if($current_page < $nb_pages) {
					$pagination .= $arraySeparateur[3];
					$pagination .= ' <a class="'.$arrayAff[4].'" href="'.$urlPage.($current_page+1).'" title="'.$arrayAff[1].'">'.$arrayAff[1].'</a>';
				} else {
					$pagination .= $arraySeparateur[3];
					$pagination .= ' <span class="'.$arrayAff[4].' '.$arrayAff[7].'">'.$arrayAff[1].'</span>';
				}
			}

			// Displaying the link "Last page" after "next page"
			if($firstLast == true) {
				$start = $nb_pages-1;
				for($i=$start+1; $i<=$nb_pages; $i++) {
					$pagination .= $arraySeparateur[1];
					$pagination .= ($current_page==$i) ? '<span class="'.$arrayAff[4].' '.$arrayAff[7].'">'.$arrayAff[3].'</span>' : '<a href="'.$urlPage.$i.'">'.$arrayAff[3].'</a>';
				}
			}
		}
		$pagination .= "</div>"; // End paging block
		echo $pagination;
        //  Load ajax pagination
		add_action( 'wp_footer', 'Fancy_Search_ajax_pagination_javascript' ); // Write our JS below here

		function Fancy_Search_ajax_pagination_javascript() { ?>
			<script>
				jQuery(document).ready(function($) {
					$(".pagination-block a").on("click",function(e){

						/*  Show Loadajax image  */
						$(".fs-loadajax").fadeIn(500);

						var url = $(this).attr("href");

						e.preventDefault();
						$.ajax({
							type : 'GET',
							url : url,
							complete : function (jqXHR, textStatus) {
								var condition = (typeof (jqXHR.isResolved) !== 'undefined') ? (jqXHR.isResolved()) : (textStatus === "success" || textStatus === "notmodified");
								if (condition) {
									/*  Hide Loadajax image  */
									$(".fs-loadajax").fadeOut(500);

									/*  Load-content    */
									var data    = jqXHR.responseText;
									$('#fs_append').html($(data).find('.fs-content').html());

									/*  Padding-Menu    */
									var nav_width = $('.fs-menu').outerWidth();
//                                $('.fs-content').css({'padding-left':nav_width});

									/*  Margin-Top    */
									var h_head = $('.fs-header').outerHeight();
									var w_window = $(window).width();
									if(w_window > 480){
										$('.fs-maincontent').css('padding-top',h_head+40);
									}else{
										$('.fs-maincontent').css('padding-top',h_head+20);
									}
								}
							}
						});
					});
				});
			</script>
		<?php }
        ?>

    <?php
	}

	function limit() {
		return self::$limit;
	}
	function nbResults() {
		return self::$nbResultsChiffre;
	}

}

/*-------------------------------------------------------------------*/
/*------------------ Class Girl to show results ---------------------*/
/*-- 4 optional parameters:
/*-- 1. "false" to display the number of results per page
/*-- 2. Table to show "results" and "outcomes"
/*-- 3. End of sentence ("for your search")
/*-- 4. Coordination for nb results per page
/*-------------------------------------------------------------------*/
class affichageResultats extends moteurRecherche {
	public function nbResultats($illimite = false, $wordsResults = array("result", "results"), $phrase = 'Your search', $coord = " à ") {
		if($illimite == true) {
			if(parent::nbResults() < 2) {
				$res = " ".$wordsResults[0];
			} else {
				$res = " ".$wordsResults[1];
			}
			return "<div class=\"searchNbResults\"><span class='numR'>".parent::nbResults()."</span>".$res." ".$phrase.".</div>";
		} else {
			if(parent::$limitArg == 0) {
				$nbDebut = 1;
				if(parent::nbResults() > parent::$limit) {
					$nbFin = (parent::$limitArg+1) * parent::$limit;
				} else {
					$nbFin = parent::nbResults();
				}
			} else {
				$nbDebut = ((parent::$limitArg-1) * parent::$limit)+1;

				if(ceil(parent::nbResults()/(parent::$limit*parent::$limitArg)) != 1) {
					$nbFin = parent::$limitArg * parent::$limit;
				} else {
					$nbFin = parent::nbResults();
				}
			}

			if(parent::nbResults() < 2) {
				$res = " ".$wordsResults[0];
			} else {
				$res = " ".$wordsResults[1];
			}
			return "<div class=\"searchNbResults\"><span class='numR'>".parent::nbResults()."</span>".$res." ".$phrase." (".$nbDebut.$coord.$nbFin.").</div>";
		}
	}
}

/*------------------------------------------------------------------------------------------------*/
/*-------------------------------- Class for highlights words ----------------------------------*/
/*------------ Structure: to highlight new password () in the display function ----------------*/
//-- 5 case :
//-- -> 1. Table of words to be emphasized
//-- -> 2. Text in which highlighting apply
//-- -> 3. Highlight Type:
//--        -> "Exact" for precise typed string (default)
//--        -> "Total" or "complete" for complete words
//-- -> 4. Accuracy of highlighting:
//-- 		-> True to highlight the specific word (depending on the type of research)
//--		-> False to emphasize the word containing a precise string (depending on the type of research)
//-- -> 5. Search type: FULL TEXT, or REGEX LIKE value
//--		-> NB: it also determines the accuracy of the highlight (FULLTEXT is more accurate)
/*--------------------------------------------------------------------------------------------------*/
class surlignageMot {
	public $contenu;

	// Starts function without "echo / print" for text
	public function __get($var) {
		echo $this->contenu;
	}

	/*----------------------------------------------------------------------------------*/
	/*----------------------- Highlight method with 5 points -------------------*/
	/*------ $ bold = new highlight Password ($ word, $ text, 'exact', true, "FULL TEXT"); ------*/
	/*----------------------------------------------------------------------------------*/
	public function __construct($mots, &$contenu, $typeSurlignage = "exact", $exact = true, $typeRecherche = "FULLTEXT") {
		foreach($mots as $mot) {
			// Displays expressions quoted in bold
			if(preg_match_all('/"(.*)"/i', $mot, $args)) {
				foreach($args[0] as $arg) {
					$mot = str_ireplace(array('"','\"'),array(' ',' '),$mot);
				}
			}
			// Allows the characters to escape the regexp
			if(preg_match_all('([\+\*\?\/\'\"\-])', $mot, $args)) {
				foreach($args[0] as $arg) {
					$mot = str_ireplace(array('+', '*', '?', '/', "'", '"'),array('\+','\*','\?', '\/', '\'', ''),$mot);
				}
			}


			// ADAPTS highlighting words as needed (exact string, or without full word)
			if($typeSurlignage == "exact" && (($exact == true && $typeRecherche != "LIKE") || ($exact == false && $typeRecherche == "FULLTEXT"))) {
				$contenu = preg_replace('/([[:blank:]<>\(\[\{\'].?:?;?,?)('.$mot.')([\)\]\}.,;:!\?[:blank:]<>])/i', '$1<b>$2</b>$3', $contenu);
			} else if($typeSurlignage == "exact" && (($exact == true && $typeRecherche == "LIKE") || ($exact == false) && $typeRecherche != "FULLTEXT")) {
				$contenu = preg_replace('/('.$mot.'{1,'.strlen($mot).'})/i', '<b>$1</b>', $contenu);
			} else if($typeSurlignage == "total" || $typeSurlignage == "complet") {
				$contenu = preg_replace('/([[:blank:]<>])([^[:blank:]<>]*'.$mot.'[^[:blank:]<>]*)([[:blank:]])/i', '$1<b>$2</b>$3', $contenu);
			}

			// Cleaning the <hn> infected bolding
			if(preg_match_all('/<[\/]?[hH]+<b>('.$mot.')<\/b>+/i', $contenu, $args)) {
				foreach($args[0] as $arg) {
					$contenu = preg_replace('/(<[\/]?[a-zA-Z]+)<b>('.$mot.')<\/b(>)+/i', '$1$2$3', $contenu);
				}
			}

			// Cleaning infected other tags bolding
			if(preg_match_all('/<[\/]?[^hH]?<b>('.$mot.')<\/b>?(^>)*/i', $contenu, $args)) {
				foreach($args[0] as $arg) {
					$contenu = preg_replace('/(<[\/]?[^hH]?)<b>('.$mot.')<\/b>?(^>)*/i', '$1$2$3$4', $contenu);
				}
			}

			// Cleans <strong> added "too much" in common HTML attributes (especially src and href)
			// Thus, if a search term is a URL, a class (...), the <strong> will be omitted and everything will work ...
			// preg_match_all('/(src|href|alt|title|class|id|rel)=["\']{1}[^\'"]+('.$mot.')[^\'"]+["\']{1}/i',$contenu, $ args)
			if(preg_match_all('/(src|href|alt|title|class|id|rel)=["\']{1}[^\'"]+('.$mot.')[^\'"]+["\']{1}/i',$contenu, $args)) {
				foreach($args[0] as $arg) {
					$contenu = preg_replace('/(src|href|alt|title|class|id|rel)*(=["\']{1}[^\'"]*)<b>+('.$mot.')<\/b>+([^\'"]*["\']{1})/i', '$1$2$3$4', $contenu);
				}
			}
		}
		$this->contenu = $contenu;
	}
} // End of class highlighting

/*----------------------------------------------------------------------------------------------------------------*/
/*---------------------------------------- Class for auto completion -------------------------------------------*/
//-- 2 PHP Object class (manufacturer and autoComplete method)
//-- Constructeur (10 arguments dont 6 optionnels) :
//-- -> 1. path to the PHP file that handles auto completion (default "autocompletion.php")
//-- -> 2. Selector search field autocomplete ('#id', '.class', etc.)
//-- -> 3. table name of the table of the inverted index
//-- -> 4. name of the column (the "field") in which are inserted all keywords
//-- -> 5. OPTIONAL: allows multiple auto-suggestion (true) or only for the first word (false)
//-- -> 6. OPTIONAL: display limit the number of results (5 by default)
//-- -> 7. OPTIONAL: AutoSuggest types (0 = the word begins with the string; 1 = word contains the string)
//-- -> 8. OPTIONAL: autofocus on the first result (true or false -> false recommended)
//-- -> 9. OPTIONAL: authorizes the creation or not of the inverted index table (unnecessary if existing, so false)
//-- ->10. OPTIONAL: character encoding ("utf-8" or "iso-8859-1" wholesale)
//-- AutoComplete method (2 arguments, only one required)
//-- -> 1. Name of the search box ($ _ GET ['nom_du_champ'])
//-- -> 2. OPTIONAL: minimum length for words added in the index (over 2 default letters)
/*----------------------------------------------------------------------------------------------------------------*/
class autoCompletion {
	private $db;
	private $table;
	private $column;
	private $encode;

	public function __construct($bdd, $urlDestination = "autocompletion.php", $selector = "#moteur", $tableName = "autosuggest", $colName = "words", $multiple = true, $limitDisplay = 5, $type = 0, $autoFocus = false, $create = false, $encode = "utf-8") {
		// Recording information in the PHP class object
		$this->db		= $bdd;
		$this->table	= htmlspecialchars($tableName);
		$this->column	= htmlspecialchars($colName);
		$this->encode	= strtolower($encode);

		if($create == true) {
			$createSQL = "CREATE TABLE IF NOT EXISTS ".$this->table." (
						 idindex INT(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
						 ".$this->column." VARCHAR(250) NOT NULL)
						 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
			$this->db->query($createSQL) or die("Error with auto completion");
		}

		// Manage autofocus and automatic selection of the first result
		if($autoFocus == true) {
			$autoFocus = "true";
		} else {
			$autoFocus = "false";
		}

		// Script generation Ajax jQuery autocomplete (you must have previously added the associated files!)
		// Remember the following files so that the system works autocompletion.php, jquery.autocomplete.js, jquery.js (or other name), CSS
		$scriptAutoCompletion = "\n".'<script type="text/javascript">'."\n";
		$scriptAutoCompletion.= 'jQuery(document).ready(function() {'."\n";
		$scriptAutoCompletion.= "jQuery('".$selector."').autocomplete('".$urlDestination."?t=".$tableName."&f=".$colName."&l=".$limitDisplay."&type=".$type."&e=".$encode."', { selectFirst:".$autoFocus.", max:".$limitDisplay.", multiple:".$multiple.", multipleSeparator:' ', delay:100, noRecord:'' })"."\n";
		$scriptAutoCompletion.=	"})"."\n";
		$scriptAutoCompletion.=	'</script>'."\n";
		echo $scriptAutoCompletion;
	}

	public function autoComplete($field = '', $minLength = 2) {
		$table = $this->table;
		$column = $this->column;

		/*-------------------------------------------*/
		/*--- Retrieving typed keywords ------*/
		/*--- Add new words in the index --*/
		/*-------------------------------------------*/
		// Removing HTML tags (security)
		if($this->encode == 'latin1' || $this->encode == 'Latin1' || $this->encode == 'latin-1' || $this->encode == 'Latin-1') {
			$mb_encode = "ISO-8859-1";
		} elseif($this->encode == 'utf8' || $this->encode == 'UTF8' || $this->encode == 'utf-8' || $this->encode == 'UTF-8') {
			$mb_encode = "UTF-8";
		} else {
			$mb_encode = $encoding;
		}
		$field = mb_strtolower(strip_tags($field), $mb_encode);

		// 1. if an expression is in quotes, one seeks the complete expression (of words)
		// 2. if the keywords are off the quotes, the search word by word is activated
		if(preg_match_all('/["]{1}([^"]+[^"]+)+["]{1}/i', $field, $entreGuillemets)) {
			// Adds all phrases in quotes in a table
			foreach($entreGuillemets[1] as $expression) {
				$results[] = esc_sql($expression);
			}
			// Retrieves words that are not quoted in an array
			$sansExpressions = str_ireplace($entreGuillemets[0],"",$field);
			$motsSepares = explode(" ",$sansExpressions);
		} else {
			$motsSepares = explode(" ", esc_sql($field));
		}
		// Remove empty array keys (because of too many spaces and strip_tags)
		foreach($motsSepares as $key => $value) {
			// Replaces excluded words (too short) with empty strings
			if(!empty($exclusion)) {
				if(strlen($value) <= $exclusion) {
					$value = '';
				}
			}
			// Removes stops if any words
//			if(!empty($stopWords)) {
//				if(in_array($value, $stopWords)) {
//					$value = '';
//				}
//			}
			// Removes empty channels of the table (and therefore excluded words)
			if(empty($value)) {
				unset($motsSepares[$key]);
			}
		}
		// Add every single word in the word list to search
		foreach($motsSepares as $motseul) {
			$results[] = $motseul;
		}

		// If the array of words and expressions is not empty, then it seeks ... (if no results!)
		if(!empty($results)) {
			// Cleans each field to avoid the risk of piracy ...
			for($y=0; $y < count($results); $y++) {
				$expression = $results[$y];
				$recherche[] = htmlspecialchars(trim(strip_tags($expression)));
			}

			// Recovery of the words in the inverted index
			$selectWords = $this->db->get_results("SELECT ".$column." FROM ".$table, ARRAY_A);
			$selected = array();
			foreach($selectWords as $w) {
				$selected[] = $w[$column];
			}

			foreach($recherche as $word) {
				if(strlen($word) > $minLength) {
					if(!in_array($word, $selected)) {
						$addWordsSQL = "INSERT INTO ".$this->table." SET ".$this->column." = '".$word."'";
						$this->db->query($addWordsSQL) or die("Error with the addition in the auto completion");
					}
				}
			}
		}
	}
} // End of class autocompletion

/*------------------------------------------------------------------------*/
/*----------------- Class to create the index FullText -------------------*/
/*-- 1. Start $ alter table alter table FullText = new (); ---------------*/
/*-- 2. Three parameters: the database name, table and columns -----------*/
/*-- N.B. : function changes the MyISAM table (for FullText) -------------*/
/*------------------------------------------------------------------------*/
class alterTableFullText {
	private $db; // Variable connection (mysql object!)

	public function __construct($bdd, $nomBDD, $table, $colonnes) {
		$this->db = $bdd;
		// Checking the SQL table type whether it is in MyISAM
		$engineSQL = $this->db->get_results("SHOW TABLE STATUS FROM $nomBDD LIKE '".$table."'", ARRAY_A);
		$engine = $engineSQL;

		// Changing the MyISAM table if necessary (FULL TEXT compatibility)
		if($engine["Engine"] != "MyISAM") {
			$MyISAMConverter = $this->db->query("ALTER TABLE $table ENGINE=MYISAM") or die("Erreur : ".$this->db->show_errors());
		}
		// Creation of FULLTEXT indexes in columns if they do not already exist ...
		if (is_array($colonnes)) {
			foreach($colonnes as $colonne) {
				$ifFullTextExists = $this->db->get_results("SHOW INDEX FROM $table WHERE column_name = '$colonne' AND Index_type = 'FULLTEXT'", ARRAY_A);
				$fullTextExists = $ifFullTextExists;
				if($fullTextExists['Index_type'] != 'FULLTEXT') {
					$alterTableFullText = $this->db->query("ALTER TABLE $table ADD FULLTEXT($colonne)") or die("Error : ".$this->db->show_errors());
				}
			}
		} else {

			$colonnes = str_ireplace(' ', '', $colonnes);
			$SQLFields = explode(',',$colonnes);
			foreach($SQLFields as $colonne) {
				$ifFullTextExists = $this->db->get_results("SHOW INDEX FROM $table WHERE column_name = '$colonne' AND Index_type = 'FULLTEXT'", ARRAY_A);
				$fullTextExists = $ifFullTextExists;
				if($fullTextExists['Index_type'] != 'FULLTEXT') {
					$alterTableFullText = $this->db->query("ALTER TABLE $table ADD FULLTEXT($colonne)") or die("Error : ".$this->db->show_errors());
				}
			}
		}
	}
}
?>