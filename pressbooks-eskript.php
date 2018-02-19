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

//show today's data if tag looks like [date today], otherwise pass original shortcode tag
function todaysdate_shortcode( $atts, $content, $tag ){
	if ($atts[0] == 'today')
		return date('d.m.Y');
	
	$attrString = '';
	foreach ($atts as $key => $value)
        $attrString .= ' ' . $key . '="' . $value . '"';
	return '[ '.$tag.' '.$attrString.']';
}
add_shortcode( 'date', 'todaysdate_shortcode' );

/**
 * Remove references to pressbooks.com..
 */
$GLOBALS['PB_SECRET_SAUCE']['TURN_OFF_FREEBIE_NOTICES_EPUB'] = 'not_created_on_pb_com';
$GLOBALS['PB_SECRET_SAUCE']['TURN_OFF_FREEBIE_NOTICES_PDF'] = 'not_created_on_pb_com';
