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
  if(isset($_POST["season"]) && isset($_POST["event_name"]))
  {
    $season = $_POST["season"];
    $event_name = $_POST["event_name"];

    if (isset($_POST["event_state"])) {
      $event_state = $_POST["event_state"];
    } else {
      $event_state = '';
    }

    if (isset($_POST["event_host"])) {
      $event_host = $_POST["event_host"];
    } else {
      $event_host = '';
    }

    if (isset($_POST["event_venue"])) {
      $event_venue = $_POST["event_venue"];
    } else {
      $event_venue = '';
    }

    if (isset($_POST["event_date"])) {
      $event_date = $_POST["event_date"];
    } else {
      $event_date = '';
    }

    $result = $conn->query("SELECT * FROM RACE_EVENT WHERE season='$season' AND event_name='$event_name'");
    $count = mysqli_num_rows($result);
    if ($count > 0) { 
      $d += 1;
      echo 'Event already exists. ';
      return; 
    }

    $sql = null;
   
    $sql = mysqli_query($conn, "INSERT INTO RACE_EVENT (season, event_name, state, event_date, host, venue ) VALUES ('$season','$event_name','$event_state','$event_date', '$event_host', '$event_venue')");
      
    if ($sql == 0) {
      $text = $conn->error;
      echo "There was an error adding the event.";
    } else {
      echo "Your event was successfully added.";
    }

  } else {
    echo 'Select season and event name. ';
  }

?>
  <h1>Add Event</h1>
  </br>
  <form action=# method="POST" style="background-color: GAINSBORO;">
    <table class="form-table"> 
      <tr valign="top" name="Season"><th><?php _e('Select Season:','nensa_admin'); ?></th>
        <td>
          <select name="season" id="season" value=2017 >
            <option value=2017>2017</option>
            <option value=2016>2016</option>
            <option value=2015>2015</option>
            <option value=2014>2014</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Enter Event Name:','nensa_admin'); ?></th>
        <td>
          <input name="event_name" id="event_name" type="text">
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Date:','nensa_admin'); ?></th>
        <td>
          <input type="date" id="event_date" name="event_date" max="2020-12-31"><br>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Enter Venue Name:','nensa_admin'); ?></th>
        <td>
          <input name="event_venue" id="event_venue" type="text">
        </td>
      </tr> 
      <tr valign="top"><th scope="row"><?php _e('Enter State:','nensa_admin'); ?></th>
        <td>
          <select name="event_state" id="event_state" value="NH">
            <option value="NH">MA</option>
            <option value="MA">NH</option>
            <option value="ME">ME</option>
            <option value="VT">VT</option>
            <option value="NY">NY</option>
          </select>
        </td>
      </tr> 
      <tr valign="top"><th scope="row"><?php _e('Enter Host Name:','nensa_admin'); ?></th>
        <td>
          <input name="event_host" id="event_host" type="text">
        </td>
      </tr>                                              
    </table>
      <p class="submit" style="padding-left: 12px;">
        <input type="submit" class="button-primary" value="<?php _e('Create New Event', 'nensa_admin') ?>" /></br>
      </p>
  </form>
  </br>
  <hr>

<?php
}

function create_race() {

  //wp_enqueue_script( 'ajax-script', plugins_url( '/js/nensa_ajax.js', __FILE__ ), array('jquery') );

  global $wpdb1;

  include ("connection.php");
  ini_set('auto_detect_line_endings', true);
  if(isset($_POST["race_name"]) && isset($_POST["event_select"]))
  {
    $race_name = $_POST["race_name"];
    $event_name = $_POST["event_select"];

    if (isset($_POST["race_date"])) {
      $race_date = $_POST["race_date"];
    } else {
      $race_date = '';
    }

    if (isset($_POST["race_distance"])) {
      $race_distance = $_POST["race_distance"];
    } else {
      $race_distance = '';
    }

    if (isset($_POST["race_date"])) {
      $race_date = $_POST["race_date"];
    } else {
      $race_date = '';
    }

    if (isset($_POST["race_technique"])) {
      $race_technique = $_POST["race_technique"];
    } else {
      $race_technique = '';
    }

    if (isset($_POST["race_start_format"])) {
      $race_start_format = $_POST["race_start_format"];
    } else {
      $race_start_format = '';
    }

    if (isset($_POST["race_age_groups"])) {
      $race_age_groups = $_POST["race_age_groups"];
    } else {
      $race_age_groups = '';
    }

    if (isset($_POST["gender"])) {
      $gender = $_POST["gender"];
    } else {
      $gender = '';
    }

    $result = $conn->query("SELECT event_id, season FROM RACE_EVENT WHERE event_name='$event_name'");
      
    if ($result->num_rows == 1) {
      $row = $result->fetch_assoc();
      $event_id = (int)$row['event_id'];
      $season = (int)$row['season'];
    } else {
      echo 'Event Not Matched in Database. ';
      return;
    }

    $sql = null;
   
    $sql = mysqli_query($conn, "INSERT INTO RACE_EVENT (parent_event_id, season, event_name, sex, event_date, age_group, technique, distance, start_format ) VALUES ('$event_id', '$season','$race_name','$gender','$race_date', '$race_age_groups', '$race_technique', '$race_distance', '$race_start_format')");
      
    if ($sql == 0) {
      $text = $conn->error;
      echo "There was an error adding the event.";
    } else {
      echo "Your event was successfully added.";
    }

  } else {
    echo 'Select season and event name. ';
  }

?>
  <h1>Add Race</h1>
  </br>
  <form action=# method="POST" style="background-color: GAINSBORO;">
    <table class="form-table"> 
      <tr valign="top"><th scope="row"><?php _e('Select Event:','nensa_admin'); ?></th>
        <td>
          <select id="event_select" name="event_select" value="">
            <option name="" value=""></option>
            
            <?php  // Get all db table names
            $sql = "SELECT  event_name  FROM RACE_EVENT WHERE season=2017 AND parent_event_id IS NULL;";
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
      <tr valign="top"><th scope="row"><?php _e('Enter Race Name:','nensa_admin'); ?></th>
        <td>
          <input name="race_name" id="race_name" type="text">
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Date:','nensa_admin'); ?></th>
        <td>
          <input type="date" id="race_date" name="race_date" max="2020-12-31"><br>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Distance:','nensa_admin'); ?></th>
        <td>
          <select name="race_distance" id="race_distance" value="1.3K">
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
          <select name="race_technique" id="race_technique" value="Skate">
            <option value="Skate">Skate</option>
            <option value="Classic">Classic</option>
          </select>
        </td>
      </tr>
      <tr valign="top"><th scope="row"><?php _e('Select Start Format:','nensa_admin'); ?></th>
        <td>
          <select name="race_start_format" id="race_start_format" value="Interval">
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
      <tr valign="top"><th scope="row"><?php _e('Enter Age Group(s):','nensa_admin'); ?></th>
        <td>
          <input name="race_age_groups" id="race_age_groups" type="text">
        </td>
      </tr>                                              
    </table>
      <p class="submit" style="padding-left: 12px;">
        <input type="submit" class="button-primary" value="<?php _e('Add Race To Event', 'nensa_admin') ?>" /></br>
      </p>
  </form>
  </br>
  <hr>

<?php
}
?>