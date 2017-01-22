	add_action( 'wp_enqueue_scripts', 'ajax_test_enqueue_scripts' );
	function ajax_test_enqueue_scripts() {
		wp_enqueue_script( 'nensa_ajax', plugins_url( 'js/nensa_ajax.js', __FILE__ ), array('jquery'), '1.0', true );
	}