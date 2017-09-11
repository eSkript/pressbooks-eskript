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
 * Replace pre-installed latex handler with our own solution.
 */
add_action( 'eskript_overrides', function() {
	remove_shortcode( 'latex' );
	remove_shortcode( 'katex' );
	add_filter( 'the_content', function( $content ) {
		return eskript_shortcode_handler( $content, array( 'latex', 'katex' ), 'eskript_latex_shortcode' );
	}, 8 );
} );

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
