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
add_action( 'wp_enqueue_scripts', 'ffd_scripts',100 );
function ffd_scripts() {
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

?>
