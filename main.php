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
include ("neon_fetch.php");
include ("neon_retrieve.php");
include ("create_event.php");

/*
add_action( 'admin_enqueue_scripts', 'my_enqueue' );
function my_enqueue($hook) {
	if (!strpos($hook, 'nensa_admin') !== false) {
	  	return;
	} 
        
	wp_enqueue_script( 'ajax-script', plugins_url( '/js/nensa_ajax.js', __FILE__ ), array('jquery') );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'ajax-script', 'ajax_object',
  array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'season' => 2015 ) );
}

// Same handler function...
add_action( 'wp_ajax_jn_select', 'jn_select_callback' );
function jn_select_callback() {
	global $wpdb;
	$season = intval( $_POST['season'] );
  echo $season;
	wp_die();
}
*/
class nensa_admin {

	// Setup options variables
	protected $option_name = 'nensa_admin';  // Name of the options array
	protected $data = array(  // Default options values
		'jq_theme' => 'smoothness'
	);

	public function __construct() {

		global $wpdb1;

		$wpdb1 = new wpdb(RESULTS_DB_USER, RESULTS_DB_PASSWORD, RESULTS_DB_NAME, RESULTS_DB_HOST);
		
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
    $nensa_admin_page = add_submenu_page( 'options-general.php', __('NENSA Admin','nensa_admin'), __('NENSA Admin','nensa_admin'), 'manage_options', 'nensa_admin_menu_page', array( $this, 'nensa_admin_menu_page' )); // Add submenu page to "Settings" link in WP
		add_action( 'admin_print_scripts-' . $nensa_admin_page, array( $this, 'nensa_admin_admin_scripts' ) );  // Load our admin page scripts (our page only)
		add_action( 'admin_print_styles-' . $nensa_admin_page, array( $this, 'nensa_admin_admin_styles' ) );  // Load our admin page stylesheet (our page only)
	}
	
	public function nensa_admin_settings() {
		register_setting('nensa_admin_options', $this->option_name, array($this, 'nensa_admin_validate'));
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

		wp_enqueue_script( 'ajax-script', plugins_url( '/js/nensa_ajax.js', __FILE__ ), array('jquery') );

		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		wp_localize_script( 'ajax-script', 'ajax_object',	array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'season' => 2014 ) );
		
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
        
      <h2><?php _e('NENSA Result, Event and Member Management','nensa_admin'); ?></h2>
      
      <p>This plugin allows you to manage NENSA Result, Member and Event data.</p>
      
      <div id="tabs">
        <ul>
  				<li><a href="#tabs-1"><?php _e('Member Lookup','nensa_admin'); ?></a></li>
  				<li><a href="#tabs-2"><?php _e('Load Results','nensa_admin'); ?></a></li>
  				<li><a href="#tabs-3"><?php _e('Add Event','nensa_admin'); ?></a></li>
  				<li><a href="#tabs-4"><?php _e('Add Race','nensa_admin'); ?></a></li>
          <li><a href="#tabs-5"><?php _e('DataTable Reference','nensa_admin'); ?></a></li>
        </ul>
          <div id="tabs-1">
          	<?php	 search_neon_for_racer(); ?>
          </div> <!-- End tab 1 -->
          <div id="tabs-2">
          	</br><strong>Load 2016/2017 Event Data</strong></br>
						</br>

						<form id="import" name="import" method="post" enctype="multipart/form-data">
							<table class="form-table"> 
					      <tr valign="top"><th scope="row"><?php _e('Select Season:','nensa_admin'); ?></th>
					        <td>
								    <select id="event_select" name="event_select" value="">
								        <option name="" value=""></option>
								        
								        <?php  // Get all db table names
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
								 <tr valign="top"><th scope="row"><?php _e('Select CSV File:','nensa_admin'); ?></th>
								  <td>
								  	<input id="results_file" type="file" name="file" />
								  </td>
								</tr>
							</table>
							<p class="submit">
						    <input id="import_button" type="submit" name="submit" class="button-primary" value="<?php _e('Import Event', 'nensa_admin') ?>" />
						  </p>
					  </form>
          </div> <!-- End tab 2 -->
          <div id="tabs-3">
        		<?php	 create_event(); ?>
          </div> <!-- End tab 3 -->
          <div id="tabs-4">
        		<?php	 create_race(); ?>
          </div> <!-- End tab 4 -->
          <div id="tabs-5">
        
          </div> <!-- End tab 4 -->
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
