<?php
/**
 * Llorix One Lite functions and definitions
 *
 * @package llorix-one-lite
 */

/*
add_action( 'wp_ajax_jn_select', 'jn_select_callback' );
function jn_select_callback() {
  global $wpdb;
  $season = intval( $_POST['season'] );
  echo $season;
  wp_die();
}
*/
//add_action('wp_ajax_import_results', 'import_results_callback');
function create_event() {

  //wp_enqueue_script( 'ajax-script', plugins_url( '/js/nensa_ajax.js', __FILE__ ), array('jquery') );

  global $wpdb1;

  include ("connection.php");
  ini_set('auto_detect_line_endings', true);
  if(isset($_POST["submit"]))
  {

    if(isset($_POST["season"])) {
      $event_name = $_POST['event_select']; 
    } else {
      echo 'Select an event. ';
      return;
    }

    $result = $conn->query("SELECT * FROM RACE_EVENT WHERE season='$season' AND event_name='$event_name'");
    $count = mysqli_num_rows($result);
    if ($count > 0) { 
      $d += 1;
      echo 'Race already exists. ';
      return; 
    }

    $e = 0;
    $sql = null;
   
    $sql = mysqli_query($conn, "INSERT INTO RACE_EVENT () VALUES ()");
      
    if ($sql == 0) {
      $text = $conn->error;
      $e += 1;
    } else {
      $c += 1;
    }

    //echo "You have imported ". $c ." results. You skipped over ". $d ." duplicate records. There were ". $m ." member_season_id conflicts. There were ". $e ." errors.";

  }

  //echo 'Hi';
  //die();

?>
  <h1>Add Event</h1>
  </br>
  <form action=# method="POST" style="background-color: GAINSBORO;">
    <table class="form-table"> 
      <tr valign="top" name="Season"><th><?php _e('Select Season:','nensa_admin'); ?></th>
        <td>
          <select name="Season" value=2017>
            <option value=2017>2017</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Event Type:','nensa_admin'); ?></th>
        <td>          
          <select name="event_type" id="event_type" value="Eastern Cup">
            <option value="Eastern Cup">Eastern Cup</option>
            <option value="Zak Cup">Zak Cup</option>
            <option value="Marathon">Marathon</option>
            <option value="Club">Club</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Date:','nensa_admin'); ?></th>
        <td>
          <input type="date" id="event_date" name="event_date" max="2020-12-31"><br>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select State:','nensa_admin'); ?></th>
        <td>
          <select name="event_state" id="event_state" value="NH">
            <option value="MA">MA</option>
            <option value="NH">NH</option>
            <option value="ME">ME</option>
            <option value="VT">VT</option>
            <option value="NY">NY</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Distance:','nensa_admin'); ?></th>
        <td>
          <select name="event_distance" id="event_distance" value="5K">
            <option value="1.3K">1.3K</option>
            <option value="1.5K">1.5K</option>
            <option value="5K">5K</option>
            <option value="10K">10K</option>
            <option value="15K">15K</option>
            <option value="20K">20K</option>
            <option value="25K">25K</option>
            <option value="30K">30K</option>
            <option value="40K">40K</option>
            <option value="50K">50K</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Technique:','nensa_admin'); ?></th>
        <td>
          <select name="event_technique" id="event_technique" value="Skate">
            <option value="Skate">Skate</option>
            <option value="Classic">Classic</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Format:','nensa_admin'); ?></th>
        <td>
          <select name="event_format" id="event_format" value="Sprint">
            <option value="Sprint">Sprint</option>
            <option value="Distance">Distance</option>
            <option value="Pursue">Pursue</option>
            <option value="Skiathlon">Skiathlon</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Start Type:','nensa_admin'); ?></th>
        <td>
          <select name="start_type" id="start_type" value="Interval">
            <option value="Interval">Interval</option>
            <option value="Mass Start">Mass Start</option>
            <option value="Wave">Wave</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Gender:','nensa_admin'); ?></th>
        <td>
          <select name="gender" id="gender" value="F">
            <option value="M">M</option>
            <option value="F">F</option>
          </select>
        </td>
      </tr> 
      <tr valign="top"><th scope="row"><?php _e('Enter Techical Delegate:','nensa_admin'); ?></th>
        <td>
          <input name="event_distance" id="event_distance" type="text">
        </td>
      </tr> 
      <tr valign="top"><th scope="row"><?php _e('Enter Event Temp:','nensa_admin'); ?></th>
        <td>
          <input type="number" id="event_temp" name="event_temp" min="-20" max="50">
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Enter Snow Conditions:','nensa_admin'); ?></th>
        <td>
          <select name="snow_conditions" id="snow_conditions" value="New Snow">
            <option value="None">New Snow</option>
            <option value="Old Snow">Old Snow</option>
            <option value="Manmade">Manmade</option>
            <option value="Feels Like Styrofoam">Feels Like Styrofoam</option>
            <option value="Dust On Crust">Dust On Crust</option>
            <option value="Really Shite">Really Shite</option>
            <option value="Rocks Everywhere">Rocks Everywhere</option>
            <option value="Slush">Slush</option>
            <option value="Pine Needles">Pine Needles</option>
            <option value="Black Ice">Black Ice</option>
            <option value="Mash Potato">Mash Potato</option>
          </select>
        </td>
      </tr> 
      <tr valign="top"><th scope="row"><?php _e('Select Results Type:','nensa_admin'); ?></th>
        <td>
          <select name="results_type" id="results_type" value="USSA Scored">
            <option value="USSA Scored">USSA Scored</option>
            <option value="Zak Cup">Zak Cup</option>
            <option value="Marathon">Marathon</option>
            <option value="Club">Club</option>
          </select>
        </td>
      </tr>                                              
    </table>
      <p class="submit" style="padding-left: 12px;">
        <input type="submit" class="button-primary" value="<?php _e('Submit', 'nensa_admin') ?>" /></br>
      </p>
  </form>
  </br>
  <hr>

<?php
}
?>