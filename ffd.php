<?php
/*
Plugin Name: FFD website extra configurations
Description: This plugin adds some common functionalities and configurations to FFD website.
Version: 0.1
Author: Montera34
Author URI: https://montera34.com
License: GPLv3
 */

$v = '0.1';

// USER CAPABILITIES
////

// USER PROFILE
////

// Removes the leftover 'Visual Editor', 'Keyboard Shortcuts' and 'Toolbar' options.
if ( ! function_exists( 'ffd_remove_personal_options' ) ) {
	
	function ffd_remove_personal_options( $subject ) {
		echo "\n" . '<script type="text/javascript">jQuery(document).ready(function($) { $(\'form#your-profile > h3:first\').hide(); $(\'form#your-profile > table:first\').hide(); $(\'form#your-profile\').show(); });</script>' . "\n";
	}

}
add_action( 'show_user_profile', 'ffd_remove_personal_options',10,1 );

// remove bio box
// this function will work if TML plugin is active and it handles user profile page
if(!function_exists('ffd_remove_bio_box')){
	function ffd_remove_bio_box($buffer){
		$buffer = str_replace('<h3>À propos de vous</h3>','',$buffer);
		$buffer = str_replace('<h3>About Yourself</h3>','',$buffer);
		$buffer = preg_replace('/<tr class=\"tml-user-description-wrap\"[\s\S]*?<\/tr>/','',$buffer,1);
		return $buffer;
	}
	function ffd_user_profile_start(){ ob_start('ffd_remove_bio_box'); }
	function ffd_user_profile_end(){ ob_end_flush(); }
}
//add_action('admin_head-profile.php','user_profile_subject_start');
add_action('wp_head','ffd_user_profile_start');
//add_action('admin_footer-profile.php','user_profile_subject_end');
add_action('wp_footer','fdd_user_profile_end');

// SCRIPTS
////
//add_action( 'wp_enqueue_scripts', 'ffd_scripts',100 );
function ffd_scripts() {
	global $v;
	// dequeue script from PMPro Variable Price plugin
	wp_dequeue_script('pmprovp');
	wp_enqueue_script(
		'ffd-js',
		plugins_url( 'js/ffd.js' , __FILE__),
		array('jquery'),
		$v,
		TRUE
	);
}


// ADD PLUGIN OPTION SUBPAGE TO DASHBOARD
////
add_action('admin_menu', 'ffd_register_options_page');
function ffd_register_options_page() {
	add_menu_page('FFD extra configurations','FFD extra config','activate_plugins','ffd.php','ffd_options_page', 'dashicons-marker');
}

// REGISTER PLUGIN SETTINGS
add_action( 'admin_init', 'ffd_register_settings' );
function ffd_register_settings() {
	register_setting( 'ffd_multipilote_group', 'ffd_multipilote' );
	add_settings_section( 'ffd-section-multipilote', __('Multipilote prices','ffd'), 'ffd_section_multipilote_callback', 'multipilote' );
	add_settings_field( 'base_price', __('Base price','ffd'), 'ffd_multipilote_base_price_callback', 'multipilote', 'ffd-section-multipilote' );
	add_settings_field( 'reduced_price', __('Reduced price','ffd'), 'ffd_multipilote_reduced_price_callback', 'multipilote', 'ffd-section-multipilote' );
}

// CALLBACK FUNCTIONS
function ffd_section_multipilote_callback() {
	echo __('<p>This price settings are applied to multipilote membership checkout.</p><p>To make this settings works, you must include the following shortcode in the begining of checkout page content: <code>[fdd-multipilote]</code></p>','ffd');
}

function ffd_multipilote_base_price_callback() {
	$settings = (array) get_option( 'ffd_multipilote' );
	$field = esc_attr( $settings['base_price'] );
	echo "<input type='text' name='ffd_multipilote[base_price]' value='$field' /> €";
}

function ffd_multipilote_reduced_price_callback() {
	$settings = (array) get_option( 'ffd_multipilote' );
	$field = esc_attr( $settings['reduced_price'] );
	echo "<input type='text' name='ffd_multipilote[reduced_price]' value='$field' /> €";
}

// GENERATE OUTPUT
function ffd_options_page() { ?>
	<div class="wrap">
	<h2><?php _e('FFD extra configurations','ffd'); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'ffd_multipilote_group' ); ?>
			<?php do_settings_sections( 'multipilote' ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}

// VARIABLE PRICE SELECT SHORTCODE
////
add_shortcode('ffd_multipilote', 'ffd_multipilote_shortcode');
function ffd_multipilote_shortcode() {
	ffd_multipilote_select();

}

function ffd_multipilote_select() {
	ffd_scripts();
	$settings = (array) get_option( 'ffd_multipilote' );
	$base = esc_attr( $settings['base_price'] );
	$reduced = esc_attr( $settings['reduced_price'] );

	$multipilote_options = '
		<script>
		var ffdBasePrice = '.$base.';
		var ffdReducedPrice = '.$reduced.';
		</script>
	';
	echo $multipilote_options;

}
?>
