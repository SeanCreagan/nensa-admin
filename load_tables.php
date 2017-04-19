<?php

function nensa_jn_ranking_func( $atts ) {
	$atts = shortcode_atts( array(
		'datatables_id' => 0,
	), $atts, 'nensa_jn_ranking' );

	return nensa_display_jn_rankings_table( (int)$atts['datatables_id'] ); 
}

add_shortcode( 'nensa_jn_ranking', 'nensa_jn_ranking_func' );

function nensa_event_results_func( $atts ) {
	$atts = shortcode_atts( array(
		'datatables_id' => 0,
	), $atts, 'nensa_event_results' );

	return nensa_display_event_results_table( (int)$atts['datatables_id'] ); 
}

add_shortcode( 'nensa_event_results', 'nensa_event_results_func' );

function nensa_display_jn_rankings_table( $datatables_id ) {

	if (!isset($datatables_id) || $datatables_id == 0) { return; }

	if (array_key_exists("season",$_GET)) {
		$season = $_GET['season'];
	} 

	if (array_key_exists("gender",$_GET)) {
		$gender = $_GET['gender'];
	} 

	$season_array = array(2017);
  			$gender_array = array('F','M');

	echo "<form method=post name=f1>";
	echo "<select name='season' ><option value=''>Select season</option>";
	foreach ($season_array as $noticia2) {
		if(isset($_POST['season']) && ($_POST['season'] == $noticia2)) {
			$season = $_POST['season'];
			echo "<option selected value='$season'>$season</option>"."<BR>";}
		else { 
			echo "<option value='$noticia2'>$noticia2</option>";
		}
	}
	echo "</select>";
	echo "</br></br>";
	echo "<select name='gender'><option value=''>Select gender</option>";
	foreach ($gender_array as $noticia) {
		if(isset($_POST['gender']) && ($_POST['gender'] == $noticia)) {
			$gender = $_POST['gender'];
			echo  "<option selected value='$gender'>$gender</option>";
		} else {
			echo  "<option value='$noticia'>$noticia</option>";
		}
	}
	echo "</select>";
	echo "</br></br>";
	echo "<input type=submit value=Submit>";
	echo "</form>";
	if(isset($_POST['gender'])){
		$season = $_POST['season'];
		$gender = $_POST['gender'];
		$shortcode = '[wpdatatable id='.$datatables_id.' VAR1="'.$season.'" VAR2="'.$gender.'" table_view=regular]';
    echo "</br><hr>";
    echo do_shortcode($shortcode); 
	}
}

function nensa_display_event_results_table( $datatables_id ) {

?>
	<script language=JavaScript>
	function reload(form)
	{
		var val=form.season.options[form.season.options.selectedIndex].value;
		var url = [location.protocol, '//', location.host, location.pathname].join('');
		self.location=url + '?season=' + val;
	}
	function reload3(form)
	{
		var val=form.season.options[form.season.options.selectedIndex].value;
		var val1=form.event_id.options[form.event_id.options.selectedIndex].value;
		var url = [location.protocol, '//', location.host, location.pathname].join('');
		self.location=url + '?season=' + val + '&event_id=' + val1;
	}
	function reloadall(form)
	{
		var val=form.season.options[form.season.options.selectedIndex].value;
		var val1=form.event_id.options[form.event_id.options.selectedIndex].value;
		var val2=form.event_name.options[form.event_name.options.selectedIndex].value;
		var url = [location.protocol, '//', location.host, location.pathname].join('');
		self.location=url + '?season=' + val + '&event_id=' + val1 + '&event_name=' + val2;
	}
	</script>

<?php

	if (!isset($datatables_id) || $datatables_id == 0) { return; }

	if ( !isset( $results_db ) ) {
				$results_db = new PDO('mysql:host='.RESULTS_DB_HOST.';dbname='.RESULTS_DB_NAME, RESULTS_DB_USER, RESULTS_DB_PASSWORD);

	}

	if ( !isset( $results_db ) ) { echo "DB not available"; }
		$quer2="SELECT DISTINCT season FROM race_event where season > 2016 order by season desc"; 
	if (array_key_exists("season",$_GET)) {
		$season = $_GET['season'];
	} 
	if(isset($season) and strlen($season) > 0) {
		$quer="SELECT DISTINCT event_name, event_id FROM race_event WHERE parent_event_id IS NULL AND season=$season order by event_name"; 
	}

	if (array_key_exists("event_id",$_GET)) {
		$event_id = $_GET['event_id'];
	} 
	if(isset($event_id) and strlen($event_id) > 0) {
		$quer3="SELECT DISTINCT event_name FROM race_event where parent_event_id=$event_id order by event_name"; 
	} 

	echo "<form method=post name=f1>";
	echo "<select name='season' onchange=\"reload(this.form)\"><option value=''>Select one</option>";
	foreach ($results_db->query($quer2) as $noticia2) {
		if($noticia2['season']==@$season) {
			echo "<option selected value='$noticia2[season]'>$noticia2[season]</option>"."<BR>";}
		else { 
			echo  "<option value='$noticia2[season]'>$noticia2[season]</option>";
		}
	}
	echo "</select>";
	echo "</br></br>";
	echo "<select style='width:400px' name='event_id' onchange=\"reload3(this.form)\"><option value=''>Select one</option>";
	foreach ($results_db->query($quer) as $noticia) {
		if($noticia['event_id']==@$event_id) {
			echo  "<option selected value='$noticia[event_id]'>$noticia[event_name]</option>";
		} else {
			echo  "<option value='$noticia[event_id]'>$noticia[event_name]</option>";
		}
	}
	echo "</select>";
	echo "</br></br>";
	echo "<select style='width:400px' name='event_name'><option value=''>Select one</option>";
	foreach ($results_db->query($quer3) as $noticia) {
	  if(isset($_POST['event_name'])){
	    $event_name = $_POST['event_name'];
			echo  "<option selected value='$event_name'>$event_name</option>";
    } else {	
      echo  "<option selected value='$noticia[event_name]'>$noticia[event_name]</option>";
    }
	}
	echo "</select>";
	echo "</br></br>";
	echo "<input type=submit value=Submit>";
	echo "</form>";
	$selected_val = "";
	if(isset($_POST['event_name'])){
		$selected_val = $_POST['event_name'];  // Storing Selected Value In Variable
		$shortcode = '[wpdatatable id='.$datatables_id.' var1="'.$selected_val.'" table_view=regular]';
	  echo "</br><hr>";
	  echo do_shortcode($shortcode); 
	}
}

?>
