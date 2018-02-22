<?php
/*
Plugin Name: FFD website extra configurations
Description: This plugin adds some common functionalities and configurations to FFD website.
Version: 0.1
Author: Montera34
Author URI: https://montera34.com
License: GPLv3
Domain Path: /lang/
 */

$v = '0.1';

// TEXT DOMAIN AND STRING TRANSLATION
add_action( 'plugins_loaded', 'ffd_load_textdomain' );
function ffd_load_textdomain() {
	load_plugin_textdomain( 'ffd', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
}

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

// remove contact methods
add_filter( 'user_contactmethods', 'ffd_user_profile_contact_methods' );
function ffd_user_profile_contact_methods( $user_contactmethods ) {
    unset( $user_contactmethods['aim'] );
    unset( $user_contactmethods['yim'] );
    unset( $user_contactmethods['jabber'] );
}

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

// SIGN UP FORM
////
add_action( 'init', 'ffd_signup_form_extra_fields' );
function ffd_signup_form_extra_fields() {
	//don't break if Register Helper is not loaded
	if( ! function_exists ( 'pmprorh_add_registration_field' ) ) {
		return false;
	}
	$fields_after_email = array();
	$fields_checkout_boxes = array();

	// web site for
	// individual pilot, multipilot company, others
	$fields_after_email[] = new PMProRH_Field( // web site
		'url',
		'text',
		array (
			'name' => 'url',
			'label' => __('Website','ffd'),
			'levels' => array(1,3,5,6),
			'memberslistcsv' => true,
			'profile' => false
		)
	);

	// company fields for
	// individual pilot, multipilot company, big group, learning org, others
	$fields_checkout_boxes[] = new PMProRH_Field( // company section tit
		'_user_company_tit',
		'readonly',
		array (
			'label' => __('Company','ffd'),
			'levels' => array(1,3,4,5,6),
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // company name
		'_user_company',
		'text',
		array (
			'name' => '_user_company',
			'label' => __('Company name','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // company siret number
		'_user_siret',
		'text',
		array (
			'name' => '_user_siret',
			'label' => __('SIRET number','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	// first and last name fields for
	// individual pilot
	$fields_checkout_boxes[] = new PMProRH_Field( // last name
		'last_name',
		'text',
		array (
			'name' => 'last_name',
			'label' => __('Last name','ffd'),
			'levels' => array(2),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // first name
		'first_name',
		'text',
		array (
			'name' => 'first_name',
			'label' => __('First name','ffd'),
			'levels' => array(2),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	// contact fields for
	// all member types
	$fields_checkout_boxes[] = new PMProRH_Field( // address
		'_user_address',
		'textarea',
		array (
			'name' => '_user_address',
			'label' => __('Address','ffd'),
			'levels' => array(1,2,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // zip code
		'_user_zip_code',
		'text',
		array (
			'name' => '_user_zip_code',
			'label' => __('ZIP Code','ffd'),
			'levels' => array(1,2,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // city
		'_user_city',
		'text',
		array (
			'name' => '_user_city',
			'label' => __('City','ffd'),
			'levels' => array(1,2,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // phone
		'_user_phone',
		'text',
		array (
			'name' => '_user_phone',
			'label' => __('Phone','ffd'),
			'levels' => array(1,2,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	// photo field
	// individual pilot
	$fields_checkout_boxes[] = new PMProRH_Field( // photo
		'_user_photo',
		'file',
		array (
			'name' => '_user_photo',
			'label' => __('Photo','ffd'),
			'levels' => array(2),
			'memberslistcsv' => true,
			'profile' => true,
			'hint' => __('Profile photo, ID document type. This photo will be used for your membership card.','ffd')
		)
	);
	// legal contact fields
	// for individual pilot, multipilot company, big group, learning org, others
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact section tit
		'_user_legal_contact',
		'readonly',
		array (
			'label' => __('Legal contact','ffd'),
			'levels' => array(1,3,4,5,6),
			'divclass' => 'signup_section_head',
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact last name
		'_user_legal_contact_lastname',
		'text',
		array (
			'name' => '_user_legal_contact_lastname',
			'label' => __('Last name','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact first name
		'_user_legal_contact_firstname',
		'text',
		array (
			'name' => '_user_legal_contact_firstname',
			'label' => __('First name','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact occupation
		'_user_legal_contact_occupation',
		'text',
		array (
			'name' => '_user_legal_contact_occupation',
			'label' => __('Occupation','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact phone
		'_user_legal_contact_phone',
		'text',
		array (
			'name' => '_user_legal_contact_phone',
			'label' => __('Phone','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // legal contact email
		'_user_legal_contact_mail',
		'text',
		array (
			'name' => '_user_legal_contact_mail',
			'label' => __('Email','ffd'),
			'levels' => array(1,3,4,5,6),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	// pilot fields
	// for individual pilot
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot section tit
		'_user_pilot_1',
		'readonly',
		array (
			'label' => __('Pilot','ffd'),
			'levels' => array(1),
			'divclass' => 'signup_section_head',
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot autofill option
		'_user_pilot_equal_contact',
		'checkbox',
		array (
			'name' => '_user_pilot_equal_contact',
			'label' => __('Copy legal contact informations for pilot','ffd'),
			'levels' => array(1),
			'profile' => false
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // photo
		'_user_pilot_1_photo',
		'file',
		array (
			'name' => '_user_pilot_1_photo',
			'label' => __('Photo','ffd'),
			'levels' => array(1),
			'memberslistcsv' => true,
			'profile' => true,
			'hint' => __('Profile photo, ID document type. This photo will be used for your membership card.','ffd')
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot last name
		'_user_pilot_1_lastname',
		'text',
		array (
			'name' => '_user_pilot_1_lastname',
			'id' => '_user_pilot_1_lastname',
			'label' => __('Last name','ffd'),
			'levels' => array(1),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot first name
		'_user_pilot_1_firstname',
		'text',
		array (
			'name' => '_user_pilot_1_firstname',
			'id' => '_user_pilot_1_firstname',
			'label' => __('First name','ffd'),
			'levels' => array(1),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot ed number
		'_user_pilot_1_ed',
		'text',
		array (
			'name' => '_user_pilot_1_ed',
			'label' => __('ED number','ffd'),
			'levels' => array(1),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	$fields_checkout_boxes[] = new PMProRH_Field( // pilot theoretical number
		'_user_pilot_1_theory',
		'text',
		array (
			'name' => '_user_pilot_1_theory',
			'label' => __('Theoretical number (ULM, PPL...)','ffd'),
			'levels' => array(1),
			'memberslistcsv' => true,
			'profile' => true
		)
	);
	// pilots fields
	// for multipilot company, others
	for ($i = 1; $i <= 2; $i++ ) {
		$next = $i + 1;
		$levels = array(3,6);
		$tit = ( $i == 1 ) ? __('Pilot','ffd') : sprintf(__('Pilot %s','ffd'),$i);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot section tit
			'_user_pilot_'.$i,
			'readonly',
			array (
				'label' => $tit,
				'levels' => $levels,
				'divclass' => 'signup_section_head',
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // photo
			'_user_pilot_'.$i.'_photo',
			'file',
			array (
				'name' => '_user_pilot_'.$i.'_photo',
				'label' => __('Photo','ffd'),
				'levels' => $levels,
				'memberslistcsv' => true,
				'profile' => true,
				'hint' => __('Profile photo, ID document type. This photo will be used for your membership card.','ffd'),
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot last name
			'_user_pilot_'.$i.'_lastname',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_lastname',
				'label' => __('Last name','ffd'),
				'levels' => $levels,
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot first name
			'_user_pilot_'.$i.'_firstname',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_firstname',
				'label' => __('First name','ffd'),
				'levels' => $levels,
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot phone
			'_user_pilot_'.$i.'_phone',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_phone',
				'label' => __('Phone','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot email
			'_user_pilot_'.$i.'_mail',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_mail',
				'label' => __('Email','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot ed number
			'_user_pilot_'.$i.'_ed',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_ed',
				'label' => __('ED number','ffd'),
				'levels' => $levels,
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot theoretical number
			'_user_pilot_'.$i.'_theory',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_theory',
				'label' => __('Theoretical number (ULM, PPL...)','ffd'),
				'levels' => $levels,
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
	} // end for

	for($i=3; $i <= 50; $i++) {
		$prev = $i -1;
		$next = $i + 1;
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot section tit
			'_user_pilot_'.$i,
			'readonly',
			array (
				'label' => sprintf(__('Pilot %s','ffd'),$i),
				'levels' => array(3,6),
				'divclass' => 'signup_section_head',
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot photo
			'_user_pilot_'.$i.'_photo',
			'file',
			array (
				'name' => '_user_pilot_'.$i.'_photo',
				'label' => __('Photo','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
				'hint' => __('Profile photo, ID document type. This photo will be used for your membership card.','ffd'),
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot last name
			'_user_pilot_'.$i.'_lastname',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_lastname',
				'label' => __('Last name','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot first name
			'_user_pilot_'.$i.'_firstname',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_firstname',
				'label' => __('First name','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot phone
			'_user_pilot_'.$i.'_phone',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_phone',
				'label' => __('Phone','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot email
			'_user_pilot_'.$i.'_mail',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_mail',
				'label' => __('Email','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot ed number
			'_user_pilot_'.$i.'_ed',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_ed',
				'label' => __('ED number','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
		$fields_checkout_boxes[] = new PMProRH_Field( // pilot theoretical number
			'_user_pilot_'.$i.'_theory',
			'text',
			array (
				'name' => '_user_pilot_'.$i.'_theory',
				'label' => __('Theoretical number (ULM, PPL...)','ffd'),
				'levels' => array(3,6),
				'memberslistcsv' => true,
				'profile' => true,
			)
		);
//		$fields_checkout_boxes[] = new PMProRH_Field( // new pilot button
//			'_user_pilot_'.$i.'_add',
//			'checkbox',
//			array (
//				'name' => '_user_pilot_'.$i.'_add',
//				'label' => __('Add pilot '.$next,'ffd'),
//				'levels' => array(3,6),
//				'memberslistcsv' => false,
//				'profile' => true,
//			)
//		);
	} // end for

	foreach ( $fields_after_email as $f ) {
		pmprorh_add_registration_field('after_email',$f);
	}
	foreach ( $fields_checkout_boxes as $f ) {
		pmprorh_add_registration_field('checkout_boxes',$f);
	}

}
?>
