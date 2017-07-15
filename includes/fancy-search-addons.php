<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Option page of the display function for auto completion
function Fancy_Search_Callback_Addons() {
	global $wpdb, $table_Fancy_Search; // insert global variables

	// Add keywords in the index
	if(isset($_POST['Fancy_Search_action_addwords'])) {
		Fancy_Search_Autocompletion_AddWords();
	}

	// Start the delete function extracts
	if(isset($_POST['Fancy_Search_action_deletewords'])) {
		Fancy_Search_Autocompletion_DeleteWords();
	}

	/* --------------------------------------------------------------------- */
	/* --------------------------- Showing page ---------------------------- */
	/* --------------------------------------------------------------------- */
	echo '<div class="wrap fancy-search-admin">';
	echo '<div class="icon32 icon"></div>';
	echo '<h2>'; _e('Fancy Search Add-ons','fancy-search'); echo '</h2><br/>';
//    do_action( 'Fancy_Search_addons_upload');
	// Selecting data in the database
	$select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
	if(isset($_FILES["zip_file"]["name"])) {
		$filename = $_FILES["zip_file"]["name"];
		$source = $_FILES["zip_file"]["tmp_name"];
		$type = $_FILES["zip_file"]["type"];

		$name = explode(".", $filename);
		$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
		foreach($accepted_types as $mime_type) {
			if($mime_type == $type) {
				$okay = true;
				break;
			}
		}

		$continue = strtolower($name[1]) == 'zip' ? true : false;
		if(!$continue) {
			$message = "The file you are trying to upload is not a .zip file. Please try again.";
		}

		/* PHP current path */
		$path = dirname(__FILE__).'/add-ons/';  // absolute path to the directory where zipper.php is in
		$filenoext = basename ($filename, '.zip');  // absolute path to the directory where zipper.php is in (lowercase)
		$filenoext = basename ($filenoext, '.ZIP');  // absolute path to the directory where zipper.php is in (when uppercase)

		$targetdir = $path . $filenoext; // target directory
		$targetzip = $path . $filename; // target zip file

		/* create directory if not exists', otherwise overwrite */
		/* target directory is same as filename without extension */

		if (is_dir($targetdir))  rmdir_recursive ( $targetdir);


		mkdir($targetdir, 0777);

		/* here it is really happening */
		$message='';
		if(move_uploaded_file($source, $targetzip)) {
			WP_Filesystem();
			$unzipfile = unzip_file( $targetzip, $path);
			unlink($targetzip);
			if ( $unzipfile ) {
				echo 'Successfully unzipped the file!';
			} else {
				echo 'There was an error unzipping the file.';
			}


		} else {
			$message = "There was a problem with the upload. Please try again.";
		}
	}

?>
	<div class="fs-header-title">
		<div class="fs-addons-header">
			<?php if(isset($message)) echo "<p>".$message."</p>"; ?>
			<h4>Choose a zip file to upload:</h4>
			<form enctype="multipart/form-data" method="post" action="">
				<input type="file" name="zip_file" />
				<input type="submit" name="submit" value="Upload" />
			</form>
		</div>
	</div>
	<ul class="fs-addons">
		<?php
		$dir    = ''.FANCY_PATH.'/add-ons';
		$folders = scandir($dir);
		$countdir = count($folders);
		if($countdir >2){ $i=1;
			foreach($folders as $folder){
				if( $folder != '.' && $folder != '..'){
					$content = '';
					if( $folder == 'page' ){
						$content = 'Search for pages and related content...';
					}if( $folder == 'post' ){
						$content = 'Search for Posts, Post types, Post Categories, Post Tags and related content....';
					}
					?>
					<li class="fs-addon-item <?php echo esc_html($folder); ?>">
						<a href="#" >
							<h3><?php echo esc_html('Search '.$folder); ?></h3>
							<p><?php echo esc_html($content); ?></p>
						</a>
					</li>
				<?php
				}
			}
		}
		?>
	</ul>


<?php
	echo '</div>'; // End of the admin page
} // End of the callback function


// Added based on keywords in the index (so full!)
function Fancy_Search_Autocompletion_AddWords() {
	global $wpdb, $table_Fancy_Search; // insert global variables

	// Data selection from the tables of the database
	$select = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_Fancy_Search WHERE id=1", 'foo'));
	$selectWords = $wpdb->get_results($wpdb->prepare("SELECT ".$select->autoCompleteColumn." FROM ".$select->autoCompleteTable."", 'foo'));
	$words = $select->autoCompleteColumn;

	// Recovery of words and phrases in a data table
	$expressions = array_map('trim', explode(',',htmlspecialchars($_POST['Fancy_Search_autocompletion_addwords'])));

	// Recovery of the words in the inverted index
	$selected = array();
	foreach($selectWords as $w) {
		$selected[] = $w->$words;
	}

	foreach($expressions as $exp) {
		if(strlen($exp) > $select->autoCompleteSizeMin) {
			if(!in_array($exp, $selected)) {
				$wpdb->query($wpdb->prepare("INSERT INTO ".$select->autoCompleteTable."(".$select->autoCompleteColumn.") VALUES ('".$exp."')", 'foo'));
			}
		}
	}
}

// Deleting Selected excerpts
function Fancy_Search_Autocompletion_DeleteWords() {
	global $wpdb, $table_Fancy_Search; // insert global variables

	$tableDelete = $wpdb->get_row("SELECT autoCompleteTable, autoCompleteColumn FROM ".$table_Fancy_Search, ARRAY_N);
	$tabWords = sanitize_text_field($_POST['Fancy_Search_autocompletion_deletewords']);

	foreach($tabWords as $word) {
		$wpdb->delete($tableDelete[0], array($tableDelete[1] => stripslashes($word)));
	}
}
?>