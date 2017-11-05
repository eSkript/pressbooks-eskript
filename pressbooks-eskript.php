<?php
/*
Plugin Name: Pressbooks eskript
Plugin URI: http://eskript.ethz.ch
Description: ETH eskript additions for Pressbooks
Version: 0.1
Author: Lukas Kaiser, Stephan Müller et al.
Copyright: © 2017, ETH Zurich, D-HEST, Stephan J. Müller, Lukas Kaiser, Dominic Michel, Lorin Mühlebach
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Enable errors and warnings for debugging.
if ( false ) {
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ALL );
}

// TODO: analyze pb_get_chapter_number (options, no number of drafts)
define( 'ESCRIPT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ESCRIPT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( 'components/helpers.php' );
require_once( 'components/legacy.php' );
require_once( 'components/latex.php' );
require_once( 'components/references.php' );
require_once( 'components/theme.php' );
require_once( 'components/toc.php' );
require_once( 'components/user.php' );
require_once( 'components/admin.php' );

/**
 * Trigger eskript_overrides action after all other plugins are initialized; allows to replace existing shortcodes.
 *
 * Also needs to be called on check_admin_referer, so it is triggered while exporting PDFs.
 */
add_action( 'wp_loaded', 'eskript_overrides' );
add_action( 'check_admin_referer', 'eskript_overrides' );
function eskript_overrides() {
	// NOTE: Might be triggered more than once.
	do_action( 'eskript_overrides' );
}

//add_action( 'init', 'eskript_mcebuttons' );
function eskript_mcebuttons() {
    add_filter( "mce_external_plugins", "eskript_add_buttons" );
    add_filter( 'mce_buttons_3', 'eskript_register_buttons' );
	wp_register_style( 'eskript-mce-buttons', plugin_dir_url( __FILE__ ) . 'assets/css/eskript-mce-buttons.css' );
    wp_enqueue_style( 'eskript-mce-buttons' );
}
function eskript_add_buttons( $plugin_array ) {
    $plugin_array['pbeskript'] = plugin_dir_url( __FILE__ ) . 'assets/js/eskript-mce-buttons.js';
    return $plugin_array;
}
function eskript_register_buttons( $buttons ) {
    array_push( $buttons, 'toggleinlist', 'code' ); 
    return $buttons;
}


/**
 * Remove references to pressbooks.com..
 */
$GLOBALS['PB_SECRET_SAUCE']['TURN_OFF_FREEBIE_NOTICES_EPUB'] = 'not_created_on_pb_com';
$GLOBALS['PB_SECRET_SAUCE']['TURN_OFF_FREEBIE_NOTICES_PDF'] = 'not_created_on_pb_com';
