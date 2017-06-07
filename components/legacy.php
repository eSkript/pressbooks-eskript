<?php
/**
 * Contains stuff that should be rewritten or replaced in future versions.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

add_action( 'admin_init', function() {
	// List management (not really used anymore, but might still contain some dependencies).
	add_editor_style( ESCRIPT_PLUGIN_URL . 'assets/css/pbmanagelists.css' );
	add_filter('mce_external_plugins', function ( $plugin_array ) {
		$plugin_array['pbmanagelists'] = ESCRIPT_PLUGIN_URL . 'assets/js/pbmanagelists.js';
		return $plugin_array;
	});
	// Add custom textboxes to editor.
	add_editor_style( ESCRIPT_PLUGIN_URL . 'assets/css/editor.css' );
	add_filter( 'mce_external_plugins', function () {
		$plugin_array['textboxes'] = ESCRIPT_PLUGIN_URL . 'assets/scripts/textboxes.js';
		return $plugin_array;
	}, 11 );
});

/**
 * The ref shortcode was at one time called rev. This can be removed after
 * all instances are replaced in the database. 
 */
add_shortcode( 'rev', 'escript_ref' );

/**
 * Migrate old theme.
 *
 * Can be removed after all old books have been updated to use the new theme.
 */
add_filter( 'option_template', 'eskript_theme_migration' );
add_filter( 'option_stylesheet', 'eskript_theme_migration' );
add_filter( 'option_stylesheet_root', 'eskript_theme_migration' );
function eskript_theme_migration( $v ) {
	$subst = array(
		'ethskripts' => 'eskript',
		'/plugins/eth-skripts/themes-book' => '/plugins/pressbooks-eskript/themes-book',
	);
	return isset( $subst[ $v ] ) ? $subst[ $v ] : $v;
}

/**
 * Include h5p from production server so they can be displayed on local test servers.
 */
if ( defined( 'ESCRIPT_LOCAL_TEST' ) && ESCRIPT_LOCAL_TEST ) {
	// Call late to override existing shortcode.
	add_action( 'wp_loaded', function () {
		$blog = get_blog_details();
		$path = explode( '/', trim( $blog->path, '/' ) );
		$path = array_pop( $path );
		add_shortcode('h5p', function ( $atts ) use ( $path ) {
			return '<iframe src="https://eskript.ethz.ch/' . $path . '/wp-admin/admin-ajax.php?action=h5p_embed&id=' . $atts['id'] . '" width="718" height="465" frameborder="0" allowfullscreen="allowfullscreen">';
		});
		wp_enqueue_script( 'hp5-resizer', 'https://eskript.ethz.ch/' . $path . '/wp-content/plugins/h5p/h5p-php-library/js/h5p-resizer.js', [], null, true );
	} );
}

/**
 * Allow download of most recent exports.
 *
 * Most functions are ported from the Pressbooks Textbook plugin. Since this functionality
 * is now included in the main Pressbooks plugin, this part should be removed in future versions.
 */

require_once( 'pbt-rewrite.php' );

add_action('init', function () {
	add_rewrite_endpoint( 'open', EP_ROOT );
	// Flush, if we haven't already
	\PBT\Rewrite\flusher();
});

add_action( 'template_redirect', '\PBT\Rewrite\do_open', 0 );

add_action( 'admin_init', function() {
	register_setting(
		'privacy_settings',
		'pbt_redistribute_settings',
		'eskript_redistribute_absint_sanitize'
	);
	add_settings_field(
		'latest_files_public',
		__( 'Share Latest Export Files', 'ethskript' ),
		'eskript_latest_files_public_callback',
		'privacy_settings',
		'privacy_settings_section'
	);
}, 11);

function eskript_latest_files_public_callback( $args ) {
	$options = get_option( 'pbt_redistribute_settings' );

	// add default if not set
	if ( ! isset( $options['latest_files_public'] ) ) {
		$options['latest_files_public'] = 0;
	}

	$html = '<input type="radio" id="files-public" name="pbt_redistribute_settings[latest_files_public]" value="1"' . checked( 1, $options['latest_files_public'], false ) . '/> ';
	$html .= '<label for="files-public"> ' . __( 'Yes. I would like the latest export files to be available on the homepage for free, to everyone.', 'pressbooks-textbook' ) . '</label><br />';
	$html .= '<input type="radio" id="files-admin" name="pbt_redistribute_settings[latest_files_public]" value="0" ' . checked( 0, $options['latest_files_public'], false ) . '/> ';
	$html .= '<label for="files-admin"> ' . __( 'No. I would like the latest export files to only be available to administrators. (Pressbooks default)', 'pressbooks-textbook' ) . '</label>';
	echo $html;
}

function eskript_redistribute_absint_sanitize( $input ) {
	$options = get_option( 'pbt_redistribute_settings' );
	// Radio buttons.
	foreach ( array( 'latest_files_public' ) as $val ) {
		$options[ $val ] = absint( $input[ $val ] );
	}
	return $options;
}
