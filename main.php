<?php
/**
 * Plugin Name: NENSA Admin
 * Plugin URI: www.nensa.net
 * Description: Manage NENSA Results and Rankings.
 * Version: 0.1
 * Author: Jeffrey Tingle
 * Author URI: https://www.linkedin.com/in/jeffreytingle
 * License: GPL2
*/
include ("import_functions.php");
include ("load_tables.php");
include ("connection.php");

add_action( 'admin_enqueue_scripts', 'my_enqueue' );
function my_enqueue($hook) {
	if (!strpos($hook, 'nensa_admin') !== false) {
	  	return;
	} 
        
	wp_enqueue_script( 'ajax-script', plugins_url( '/js/nensa_ajax.js', __FILE__ ), array('jquery') );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'season' => 2017 ) );
}

// Same handler function...
add_action( 'wp_ajax_jn_select', 'jn_select_callback' );
function jn_select_callback() {
	global $wpdb;
	$season = intval( $_POST['season'] );
  echo $season;
	wp_die();
}

class nensa_admin {

	// Setup options variables
	protected $option_name = 'nensa_admin';  // Name of the options array
	protected $data = array(  // Default options values
		'jq_theme' => 'smoothness'
	);

	public function __construct() {

		$hostname = "localhost";
		$username = "nensa";
		$password = "nensa";
		$database = "nensa_results_db";
		global $wpdb1;

		$wpdb1 = new wpdb($username, $password, $database, $hostname);
		
		// Check if is admin
		// We can later update this to include other user roles
		if (is_admin()) {
      add_action( 'plugins_loaded', array( $this, 'nensa_admin_plugins_loaded' ));//Handles tasks that need to be done at plugins loaded stage.
			add_action( 'admin_menu', array( $this, 'nensa_admin_register' ));  // Create admin menu page
			add_action( 'admin_init', array( $this, 'nensa_admin_settings' ) ); // Create settings
			register_activation_hook( __FILE__ , array($this, 'nensa_admin_activate')); // Add settings on plugin activation
		}
	}
	
  public function nensa_admin_plugins_loaded(){
      
  }
        
	public function nensa_admin_activate() {
		update_option($this->option_name, $this->data);
	}
	
	public function nensa_admin_register(){
    $wp_csv_to_db_page = add_submenu_page( 'options-general.php', __('NENSA Admin','nensa_admin'), __('NENSA Admin','nensa_admin'), 'manage_options', 'nensa_admin_menu_page', array( $this, 'nensa_admin_menu_page' )); // Add submenu page to "Settings" link in WP
		add_action( 'admin_print_scripts-' . $wp_csv_to_db_page, array( $this, 'nensa_admin_admin_scripts' ) );  // Load our admin page scripts (our page only)
		add_action( 'admin_print_styles-' . $wp_csv_to_db_page, array( $this, 'nensa_admin_admin_styles' ) );  // Load our admin page stylesheet (our page only)
	}
	
	public function nensa_admin_settings() {
		register_setting('wp_csv_to_db_options', $this->option_name, array($this, 'nensa_admin_validate'));
	}
	
	public function nensa_admin_validate($input) {
		$valid = array();
		/*
		$valid['jq_theme'] = sanitize_text_field($input['jq_theme']);
		if (strlen($valid['jq_theme']) == 0) {
			add_settings_error(
					'jq_theme',                      // Setting title
					'jq_theme_texterror',            // Error ID
					'Please select a jQuery theme.', // Error message
					'error'                          // Type of message
			);
	
			// Set it to the default value
			$valid['jq_theme'] = $this->data['jq_theme'];
		}
		*/
		$valid['jq_theme'] = $input['jq_theme'];

    	return $valid;
	}
	
	public function nensa_admin_admin_scripts() {
		wp_enqueue_script('media-upload');  // For WP media uploader
		wp_enqueue_script('thickbox');  // For WP media uploader
		wp_enqueue_script('jquery-ui-tabs');  // For admin panel page tabs
		wp_enqueue_script('jquery-ui-dialog');  // For admin panel popup alerts
		
		wp_enqueue_script( 'nensa_admin', plugins_url( '/js/admin_page.js', __FILE__ ), array('jquery') );  // Apply admin page scripts
		wp_localize_script( 'nensa_admin', 'wp_csv_to_db_pass_js_vars', array( 'ajax_image' => plugin_dir_url( __FILE__ ).'images/loading.gif', 'ajaxurl' => admin_url('admin-ajax.php') ) );
	}
	
	public function nensa_admin_admin_styles() {
		wp_enqueue_style('thickbox');  // For WP media uploader
		wp_enqueue_style('sdm_admin_styles', plugins_url( '/css/admin_page.css', __FILE__ ));  // Apply admin page styles
		
		// Get option for jQuery theme
		$options = get_option($this->option_name);
		$select_theme = isset($options['jq_theme']) ? $options['jq_theme'] : 'smoothness';
		?><link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/<?php echo $select_theme; ?>/jquery-ui.css"><?php  // For jquery ui styling - Direct from jquery
	}
	
	public function nensa_admin_menu_page() {

	  if(!current_user_can('manage_options')){
	      wp_die('Error! Only site admin can perform this operation');
	  }
            
		// Set variables		
		global $wpdb1;
		$error_message = '';
		$success_message = '';
		$message_info_style = '';
		
		//
		// If Delete Table button was pressed
		if(!empty($_POST['delete_db_button_hidden'])) {
			
			$del_qry = 'DROP TABLE '.$_POST['table_select'];
			$del_qry_success = $wpdb1->query($del_qry);
			
			if($del_qry_success) {
				$success_message .= __('Congratulations!  The database table has been deleted successfully.','nensa_admin');
			}
			else {
				$error_message .= '* '.__('Error deleting table. Please verify the table exists.','nensa_admin');
			}
		}
		
		if ((isset($_POST['export_to_csv_button'])) && (empty($_POST['table_select']))) {
			$error_message .= '* '.__('No Database Table was selected to export. Please select a Database Table for exportation.','nensa_admin').'<br />';
		}
		
		// If button is pressed to "Import to DB"
		if (isset($_POST['execute_button'])) {
			
			// If the "Select Table" input field is empty
			if(empty($_POST['table_select'])) {
				$error_message .= '* '.__('No Database Table was selected. Please select a Database Table.','nensa_admin').'<br />';
			}
			// If the "Select Input File" input field is empty
			if(empty($_POST['csv_file'])) {
				$error_message .= '* '.__('No Input File was selected. Please enter an Input File.','nensa_admin').'<br />';
			}
			// Check that "Input File" has proper .csv file extension
			$ext = pathinfo($_POST['csv_file'], PATHINFO_EXTENSION);
			if($ext !== 'csv') {
				$error_message .= '* '.__('The Input File does not contain the .csv file extension. Please choose a valid .csv file.','nensa_admin');
			}
			
			// If all fields are input; and file is correct .csv format; continue
			if(!empty($_POST['table_select']) && !empty($_POST['csv_file']) && ($ext === 'csv')) {
				
				// If "disable auto_inc" is checked.. we need to skip the first column of the returned array (or the column will be duplicated)
				if(isset($_POST['remove_autoinc_column'])) {
					$db_cols = $wpdb1->get_col( "DESC " . $_POST['table_select'], 0 );  
					unset($db_cols[0]);  // Remove first element of array (auto increment column)
				} 
				// Else we just grab all columns
				else {
					$db_cols = $wpdb1->get_col( "DESC " . $_POST['table_select'], 0 );  // Array of db column names
				}
				// Get the number of columns from the hidden input field (re-auto-populated via jquery)
				$numColumns = $_POST['num_cols'];
				
				// Open the .csv file and get it's contents
				if(( $fh = @fopen($_POST['csv_file'], 'r')) !== false) {
					
					// Set variables
					$values = array();
					$too_many = '';  // Used to alert users if columns do not match
					
					while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
						if(count($row) == $numColumns) {  // If .csv column count matches db column count
							$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
						}
					}
					
					// If user elects to input a starting row for the .csv file
					if(isset($_POST['sel_start_row']) && (!empty($_POST['sel_start_row']))) {
						
						// Get row number from user
						$num_var = $_POST['sel_start_row'] - 1;  // Subtract one to make counting easy on the non-techie folk!  (1 is actually 0 in binary)
						
						// If user input number exceeds available .csv rows
						if($num_var > count($values)) {
							$error_message .= '* '.__('Starting Row value exceeds the number of entries being updated to the database from the .csv file.','nensa_admin').'<br />';
							$too_many = 'true';  // set alert variable
						}
						// Else splice array and remove number (rows) user selected
						else {
							$values = array_slice($values, $num_var);
						}
					}
					
					// If there are no rows in the .csv file AND the user DID NOT input more rows than available from the .csv file
					if( empty( $values ) && ($too_many !== 'true')) {
						$error_message .= '* '.__('Columns do not match.','nensa_admin').'<br />';
						$error_message .= '* '.__('The number of columns in the database for this table does not match the number of columns attempting to be imported from the .csv file.','nensa_admin').'<br />';
						$error_message .= '* '.__('Please verify the number of columns attempting to be imported in the "Select Input File" exactly matches the number of columns displayed in the "Table Preview".','nensa_admin').'<br />';
					}
					else {
						// If the user DID NOT input more rows than are available from the .csv file
						if($too_many !== 'true') {
							
							$db_query_update = '';
							$db_query_insert = '';
								
							// Format $db_cols to a string
							$db_cols_implode = implode(',', $db_cols);
								
							// Format $values to a string
							$values_implode = implode(',', $values);
							
							
							// If "Update DB Rows" was checked
							if (isset($_POST['update_db'])) {
								
								// Setup sql 'on duplicate update' loop
								$updateOnDuplicate = ' ON DUPLICATE KEY UPDATE ';
								foreach ($db_cols as $db_col) {
									$updateOnDuplicate .= "$db_col=VALUES($db_col),";
								}
								$updateOnDuplicate = rtrim($updateOnDuplicate, ',');
								
								
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode.$updateOnDuplicate;
								$db_query_update = $wpdb1->query($sql);
							}
							else {
								$sql = 'INSERT INTO '.$_POST['table_select'] . ' (' . $db_cols_implode . ') ' . 'VALUES ' . $values_implode;
								$db_query_insert = $wpdb1->query($sql);
							}
							
							// If db db_query_update is successful
							if ($db_query_update) {
								$success_message = __('Congratulations!  The database has been updated successfully.','nensa_admin');
							}
							// If db db_query_insert is successful
							elseif ($db_query_insert) {
								$success_message = __('Congratulations!  The database has been updated successfully.','nensa_admin');
								$success_message .= '<br /><strong>'.count($values).'</strong> '.__('record(s) were inserted into the', 'nensa_admin').' <strong>'.$_POST['table_select'].'</strong> '.__('database table.','nensa_admin');
							}
							// If db db_query_insert is successful AND there were no rows to udpate
							elseif( ($db_query_update === 0) && ($db_query_insert === '') ) {
								$message_info_style .= '* '.__('There were no rows to update. All .csv values already exist in the database.','nensa_admin').'<br />';
							}
							else {
								$error_message .= '* '.__('There was a problem with the database query.','nensa_admin').'<br />';
								$error_message .= '* '.__('A duplicate entry was found in the database for a .csv file entry.','nensa_admin').'<br />';
								$error_message .= '* '.__('If necessary; please use the option below to "Update Database Rows".','nensa_admin').'<br />';
							}
						}
					}
				}
				else {
					$error_message .= '* '.__('No valid .csv file was found at the specified url. Please check the "Select Input File" field and ensure it points to a valid .csv file.','nensa_admin').'<br />';
				}
			}
		}
		
		// If there is a message - info-style
		if(!empty($message_info_style)) {
			echo '<div class="info_message_dismiss">';
			echo $message_info_style;
			echo '<br /><em>('.__('click to dismiss','nensa_admin').')</em>';
			echo '</div>';
		}
		
		// If there is an error message	
		if(!empty($error_message)) {
			echo '<div class="error_message">';
			echo $error_message;
			echo '<br /><em>('.__('click to dismiss','nensa_admin').')</em>';
			echo '</div>';
		}
		
		// If there is a success message
		if(!empty($success_message)) {
			echo '<div class="success_message">';
			echo $success_message;
			echo '<br /><em>('.__('click to dismiss','nensa_admin').')</em>';
			echo '</div>';
		}
		?>
		<div class="wrap">
        
            <h2><?php _e('NENSA CSV to Results Database Options','nensa_admin'); ?></h2>
            
            <p>This plugin allows you to manage NENSA Result, Member and Event data.</p>
            
            <div id="tabs">
              <ul>
		    				<li><a href="#tabs-1"><?php _e('Settings','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-2"><?php _e('Events','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-3"><?php _e('NEON','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-4"><?php _e('Member Season','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-5"><?php _e('Member Skier','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-6"><?php _e('Results','nensa_admin'); ?></a></li>
		    				<li><a href="#tabs-6"><?php _e('DataTables','nensa_admin'); ?></a></li>
              </ul>
                
              <div id="tabs-1">
                
        			<form id="wp_csv_to_db_form" method="post" action="">
                    <table class="form-table"> 
                        
                        <tr valign="top"><th scope="row"><?php _e('Select Database Table:','nensa_admin'); ?></th>
                            <td>
                                <select id="table_select" name="table_select" value="">
                                <option name="" value=""></option>
                                
                                <?php  // Get all db table names
                                global $wpdb1;
                                $sql = "SHOW TABLES";
                                $results = $wpdb1->get_results($sql);
                                $repop_table = isset($_POST['table_select']) ? $_POST['table_select'] : null;
                                
                                foreach($results as $index => $value) {
                                    foreach($value as $tableName) {
                                        ?><option name="<?php echo $tableName ?>" value="<?php echo $tableName ?>" <?php if($repop_table === $tableName) { echo 'selected="selected"'; } ?>><?php echo $tableName ?></option><?php
                                    }
                                }
                                ?>
                            </select>
                            </td> 
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Select Event:','nensa_admin'); ?></th>
                            <td>
                                <select id="event_select" name="event_select" value="">
                                <option name="" value=""></option>
                                
                                <?php  // Get all db table names
                                global $wpdb1;
                                $sql = "SELECT  event_name  FROM RACE_EVENT WHERE season=2017 AND parent_event_id<>0;";
                                $results = $wpdb1->get_results($sql);
                                $repop_table = isset($_POST['event_select']) ? $_POST['event_select'] : null;
                                
                                foreach($results as $index => $value) {
                                    foreach($value as $eventName) {
                                        ?><option name="<?php echo $eventName ?>" value="<?php echo $eventName ?>" <?php if($repop_table === $eventName) { echo 'selected="selected"'; } ?>><?php echo $eventName ?></option><?php
                                    }
                                }
                                ?>
                            </select>
                            </td> 
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Select Input File:','nensa_admin'); ?></th>
                            <td>
                                <?php $repop_file = isset($_POST['csv_file']) ? $_POST['csv_file'] : null; ?>
                                <?php $repop_csv_cols = isset($_POST['num_cols_csv_file']) ? $_POST['num_cols_csv_file'] : '0'; ?>
                                <input id="csv_file" name="csv_file"  type="text" size="70" value="<?php echo $repop_file; ?>" />
                                <input id="csv_file_button" type="button" value="Upload" />
                                <input id="num_cols" name="num_cols" type="hidden" value="" />
                                <input id="num_cols_csv_file" name="num_cols_csv_file" type="hidden" value="" />
                                <br><?php _e('File must end with a .csv extension.','nensa_admin'); ?>
                                <br><?php _e('Number of .csv file Columns:','nensa_admin'); echo ' '; ?><span id="return_csv_col_count"><?php echo $repop_csv_cols; ?></span>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Select Starting Row:','nensa_admin'); ?></th>
                            <td>
                            	<?php $repop_row = isset($_POST['sel_start_row']) ? $_POST['sel_start_row'] : null; ?>
                                <input id="sel_start_row" name="sel_start_row" type="text" size="10" value="<?php echo $repop_row; ?>" />
                                <br><?php _e('Defaults to row 1 (top row) of .csv file.','nensa_admin'); ?>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Disable "auto_increment" Column:','nensa_admin'); ?></th>
                            <td>
                                <input id="remove_autoinc_column" name="remove_autoinc_column" type="checkbox" />
                                <br><?php _e('Bypasses the "auto_increment" column;','nensa_admin'); ?>
                                <br><?php _e('This will reduce (for the purposes of importation) the number of DB columns by "1".','nensa_admin'); ?>
                            </td>
                        </tr>
                        <tr valign="top"><th scope="row"><?php _e('Update Database Rows:','nensa_admin'); ?></th>
                            <td>
                                <input id="update_db" name="update_db" type="checkbox" />
                                <br><?php _e('Will update exisiting database rows when a duplicated primary key is encountered.','nensa_admin'); ?>
                                <br><?php _e('Defaults to all rows inserted as new rows.','nensa_admin'); ?>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input id="execute_button" name="execute_button" type="submit" class="button-primary" value="<?php _e('Import to DB', 'nensa_admin') ?>" />
                        <input id="export_to_csv_button" name="export_to_csv_button" type="submit" class="button-secondary" value="<?php _e('Export to CSV', 'nensa_admin') ?>" />
                        <input id="delete_db_button" name="delete_db_button" type="button" class="button-secondary" value="<?php _e('Delete Table', 'nensa_admin') ?>" />
                        <input type="hidden" id="delete_db_button_hidden" name="delete_db_button_hidden" value="" />
                    </p>
                    </form>
                </div> <!-- End tab 1 -->
                <div id="tabs-2">

                </div> <!-- End tab 2 -->
                <div id="tabs-3">

                </div> <!-- End tab 3 -->
                
                <div id="tabs-4">

                </div> <!-- End tab 4 -->
                <div id="tabs-5">
									
                </div> <!-- End tab 5 -->
                <div id="tabs-6">
									<?php	import_results(); ?>
                </div> <!-- End tab 5 -->
                <div id="tabs-6">
									
                </div> <!-- End tab 5 -->
            </div> <!-- End #tabs -->
        </div> <!-- End page wrap -->
        
        
        <!-- Delete table warning - jquery dialog -->
        <div id="dialog-confirm" title="<?php _e('Delete database table?','nensa_admin'); ?>">
        	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php _e('This table will be permanently deleted and cannot be recovered. Proceed?','nensa_admin'); ?></p>
        </div>
        
        <!-- Alert invalid .csv file - jquery dialog -->
        <div id="dialog_csv_file" title="<?php _e('Invalid File Extension','nensa_admin'); ?>" style="display:none;">
        	<p><?php _e('This is not a valid .csv file extension.','nensa_admin'); ?></p>
        </div>
        
        <!-- Alert select db table - jquery dialog -->
        <div id="dialog_select_db" title="<?php _e('Database Table not Selected','nensa_admin'); ?>" style="display:none;">
        	<p><?php _e('First, please select a database table from the dropdown list.','nensa_admin'); ?></p>
        </div>
        <?php
	}
	
}
$nensa_admin = new nensa_admin();

//  Ajax call for showing table column names
add_action( 'wp_ajax_wp_csv_to_db_get_columns', 'wp_csv_to_db_get_columns_callback' );
function wp_csv_to_db_get_columns_callback() {
	
	// Set variables
	global $wpdb1;
	$sel_val = isset($_POST['sel_val']) ? $_POST['sel_val'] : null;
	$disable_autoinc = isset($_POST['disable_autoinc']) ? $_POST['disable_autoinc'] : 'false';
	$enable_auto_inc_option = 'false';
	$content = '';
	
	// Ran when the table name is changed from the dropdown
	if ($sel_val) {
		
		// Get table name
		$table_name = $sel_val;
		
		// Setup sql query to get all column names based on table name
		$sql = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = "'.$wpdb1->dbname.'" AND TABLE_NAME ="'.$table_name.'" AND EXTRA like "%auto_increment%"';
		
		// Execute Query
		$run_qry = $wpdb1->get_results($sql);
		
		//
		// Begin response content
		$content .= '<table id="ajax_table"><tr>';
		
		// If the db query contains an auto_increment column
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			//$content .= 'auto: '.$run_qry[0]->EXTRA.'<br />';
			//$content .= 'column: '.$run_qry[0]->COLUMN_NAME.'<br />';
			
			// If user DID NOT check 'disable_autoinc'; we need to add that column back with unique formatting 
			if($disable_autoinc === 'false') {
				$content .= '<td class="auto_inc"><strong>'.$run_qry[0]->COLUMN_NAME.'</strong></td>';
			}
			
			// Get all column names from database for selected table
			$column_names = $wpdb1->get_col( 'DESC ' . $table_name, 0 );
			$counter = 0;
			
			//
			// IMPORTANT - If the db results contain an auto_increment; we remove the first column below; because we already added it above.
			foreach ( $column_names as $column_name ) {
				if( $counter++ < 1) continue;  // Skip first iteration since 'auto_increment' table data cell will be duplicated
			    $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		// Else get all column names from database (unfiltered)
		else {
			$column_names = $wpdb1->get_col( 'DESC ' . $table_name, 0 );
			foreach ( $column_names as $column_name ) {
			  $content .= '<td><strong>'.$column_name.'</strong></td>';
			}
		}
		$content .= '</tr></table><br />';
		$content .= __('Number of Database Columns:','nensa_admin').' <span id="column_count"><strong>'.count($column_names).'</strong></span><br />';
		
		// If there is an auto_increment column in the returned results
		if((isset($run_qry[0]->EXTRA)) && (isset($run_qry[0]->COLUMN_NAME))) {
			// If user DID NOT click the auto_increment checkbox
			if($disable_autoinc === 'false') {
				$content .= '<div class="warning_message">';
				$content .= __('This table contains an "auto increment" column.','nensa_admin').'<br />';
				$content .= __('Please be sure to use unique values in this column from the .csv file.','nensa_admin').'<br />';
				$content .= __('Alternatively, the "auto increment" column may be bypassed by clicking the checkbox above.','nensa_admin').'<br />';
				$content .= '</div>';
				
				// Send additional response
				$enable_auto_inc_option = 'true';
			}
			// If the user clicked the auto_increment checkbox
			if($disable_autoinc === 'true') {
				$content .= '<div class="info_message">';
				$content .= __('This table contains an "auto increment" column that has been removed via the checkbox above.','nensa_admin').'<br />';
				$content .= __('This means all new .csv entries will be given a unique "auto incremented" value when imported (typically, a numerical value).','nensa_admin').'<br />';
				$content .= __('The Column Name of the removed column is','nensa_admin').' <strong><em>'.$run_qry[0]->COLUMN_NAME.'</em></strong>.<br />';
				$content .= '</div>';
				
				// Send additional response 
				$enable_auto_inc_option = 'true';
			}
		}
	}
	else {
		$content = '';
		$content .= '<table id="ajax_table"><tr><td>';
		$content .= __('No Database Table Selected.','nensa_admin');
		$content .= '<br />';
		$content .= __('Please select a database table from the dropdown box above.','nensa_admin');
		$content .= '</td></tr></table>';
	}
	
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'content' => $content, 'enable_auto_inc_option' => $enable_auto_inc_option ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

// Ajax call to process .csv file for column count
add_action('wp_ajax_wp_csv_to_db_get_csv_cols','wp_csv_to_db_get_csv_cols_callback');
function wp_csv_to_db_get_csv_cols_callback() {
	
	// Get file upload url
	$file_upload_url = $_POST['file_upload_url'];
	
	// Open the .csv file and get it's contents
	if(( $fh = @fopen($_POST['file_upload_url'], 'r')) !== false) {
		
		// Set variables
		$values = array();
		
		// Assign .csv rows to array
		while(( $row = fgetcsv($fh)) !== false) {  // Get file contents and set up row array
			//$values[] = '("' . implode('", "', $row) . '")';  // Each new line of .csv file becomes an array
			$rows[] = array(implode('", "', $row));
		}
		
		// Get a single array from the multi-array... and process it to count the individual columns
		$first_array_elm = reset($rows);
		$xplode_string = explode(", ", $first_array_elm[0]);
		
		// Count array entries
		$column_count = count($xplode_string);
	}
	else {
		$column_count = 'There was an error extracting data from the.csv file. Please ensure the file is a proper .csv format.';
	}
	
	// Set response variable to be returned to jquery
	$response = json_encode( array( 'column_count' => $column_count ) );
	header( "Content-Type: application/json" );
	echo $response;
	die();
}

// Add plugin settings link to plugins page
add_filter( 'plugin_action_links', 'nensa_admin_plugin_action_links', 10, 4 );
function nensa_admin_plugin_action_links( $links, $file ) {
	
	$plugin_file = 'nensa_admin/main.php';
	if ( $file == $plugin_file ) {
		$settings_link = '<a href="' .
			admin_url( 'options-general.php?page=nensa_admin_menu_page' ) . '">' .
			__( 'Settings', 'nensa_admin' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

// Load plugin language localization
add_action('plugins_loaded', 'nensa_admin_lang_init');
function nensa_admin_lang_init() {
	load_plugin_textdomain( 'nensa_admin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
