<?php

function nensa_jn_ranking_func( $atts ) {
	$atts = shortcode_atts( array(
		'datatables_id' => 0,
	), $atts, 'nensa_jn_ranking' );

	return nensa_display_jn_rankings_table( (int)$atts['datatables_id'] ); 
}

add_shortcode( 'nensa_jn_ranking', 'nensa_jn_ranking_func' );

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
		$shortcode = '[wpdatatable id='.$datatables_id.' VAR1="'.$gender.'" table_view=regular]';
    echo "</br><hr>";
    echo do_shortcode($shortcode); 
	}
}

?>
