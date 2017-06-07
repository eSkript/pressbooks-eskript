<?php
/**
 * Custom LaTeX shortcode implementation.
 *
 * Fixes some problems with the native implementation.
 * Works with different image providers.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

/**
 * Call eskript_overrides after all other plugins are initialized; allows to replace existing shortcodes.
 */
add_action( 'wp_loaded', 'eskript_overrides' ); // TOOD: still needed when hooking in check_admin_referer?
add_action( 'check_admin_referer', 'eskript_overrides' ); // wp_loaded is not called while exporting PDFs
function eskript_overrides() {
	// Circumvent shortcode system.
	remove_shortcode( 'latex' );
	remove_shortcode( 'katex' );
	add_filter( 'the_content', 'eskript_latex_filter', 8 );
}

/**
 * Content filter to do our own shortcode substitution.
 */
function eskript_latex_filter( $content ) {
	// $pattern = get_shortcode_regex(array('latex', 'katex'));
	// Hardcode pattern in case 'get_shortcode_regex' changes.
	$pattern = '\[(\[?)(latex|katex)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
	$content = preg_replace_callback("/$pattern/", function ( $m ) {
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
		$content = isset( $m[5] ) ? $m[5] : null;
		$output = $m[1] . call_user_func( 'eskript_latex_shortcode', $attr, $content, $tag ) . $m[6];
		return $output;
	}, $content);
	return $content;
}

/**
 * Imitates the original pb-latex shortcode.
 */
function eskript_latex_shortcode( $atts, $latex ) {
	$latex = preg_replace( array( '#<br\s*/?>#i', '#</?p>#i' ), ' ', $latex );
	$latex = html_entity_decode( $latex, ENT_QUOTES | ENT_HTML401 );
	$latex = preg_replace( '#\s+#', ' ', $latex );
	$url = 'https://s.wordpress.com/latex.php?bg=T&';
	if ( defined( 'ESCRIPT_LATEX_URL' ) ) {
		$url = ESCRIPT_LATEX_URL;
	}
	$url .= 'latex=' . rawurlencode( $latex );
	if ( isset( $atts['color'] ) ) {
		$url .= '&color=' . rawurlencode( $atts['color'] );
	}
	$url = esc_url( $url );
	$alt = esc_attr( $latex );
	$class = 'latex';
	if ( isset( $atts['class'] ) ) {
		$class .= ' ' . $atts['class'];
	}
	$id = isset( $atts['id'] ) ? " id='{$atts['id']}'" : '';
	$img = "<img$id src='$url' style='padding: 0;' alt='$alt' title='$alt' class='$class' />";
	if ( isset( $atts['id'] ) && ! in_array( 'not-in-list', explode( ' ', $class ) ) ) {
		$attr = array();
		$attr['caption'] = '&nbsp;';
		$attr['width'] = '620';
		$attr['align'] = 'aligncenter';
		return img_caption_shortcode( $attr, $img );
	} else {
		return $img;
	}
}
