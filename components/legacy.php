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
	// List management (mostly depricated, but still contains some dependencies).
	add_editor_style( ESCRIPT_PLUGIN_URL . 'assets/css/pbmanagelists.css' );
	add_filter( 'mce_external_plugins', function ( $plugin_array ) {
		$plugin_array['pbmanagelists'] = ESCRIPT_PLUGIN_URL . 'assets/js/pbmanagelists.js';
		return $plugin_array;
	});
	// Add custom textboxes to editor.
	add_editor_style( ESCRIPT_PLUGIN_URL . 'assets/css/editor.css' );
	add_filter( 'mce_external_plugins', function ( $plugin_array ) {
		$plugin_array['textboxes'] = ESCRIPT_PLUGIN_URL . 'assets/scripts/textboxes.js';
		return $plugin_array;
	}, 11 );
});

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
