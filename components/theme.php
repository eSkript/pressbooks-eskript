<?php
/**
 * Theme management and helper functions.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */
register_theme_directory( ESCRIPT_PLUGIN_DIR . 'themes-book' );
/**
 * Add reference numbers to the content.
 *
 * Use a low priority so it gets executed after the shortcodes.
 */
add_filter( 'the_content', 'eskript_content_filter', 111 );
function eskript_content_filter( $content ) {
	$options = get_option( 'pressbooks_theme_options_global' );
	if ( ! empty( $options['chapter_numbers'] ) ) {
		$content = eskript_enumerate( $content );
	}
	return $content;
}
/**
 * Add theme scripts.
 */
add_action( 'wp_enqueue_scripts', function() {
	$o = get_option( 'eskript_settings', array() );
	// NOTE: Disabled subchapters (for now).
	// if ( ! empty( $o['subchapterize'] ) ) {
	// 	wp_enqueue_script( 'eskript_subchapters', ESCRIPT_PLUGIN_URL . 'assets/js/subchapters.js', array( 'jquery' ) );
	// }
	if ( ! empty( $o['geogebra'] ) ) {
		wp_enqueue_script( 'eskript_geogebra', 'https://cdn.geogebra.org/apps/deployggb.js' );
	}
	wp_enqueue_script( 'eskript_fixes', ESCRIPT_PLUGIN_URL . 'assets/js/fixes.js', array( 'jquery' ) );
});
/**
 * Limit selectable themes.
 */
add_filter( 'allowed_themes', 'eskript_add_themes', 11 );
function eskript_add_themes( $themes ) {
	if ( \Pressbooks\Book::isBook() ) {
		$themes = array(); // remove all other themes
		$themes['eskript'] = 1;
		$themes['uzh'] = 1;
	}
	return $themes;
}
/**
 * Disable native chapter_numbers and parse_subsections calls.
 *
 * The native functions don't ignore headers inside boxes.
 * eskript_enumerate replaces their functionality.
 */
add_filter( 'option_pressbooks_theme_options_global', 'eskript_option_pressbooks_theme_options_global' );
function eskript_option_pressbooks_theme_options_global( $value ) {
	if ( is_array( $value ) ) {
		$value['toc_levels'] = empty( $value['parse_subsections'] ) ? 1 : 2;
		if ( basename( $_SERVER['SCRIPT_FILENAME'] ) != 'themes.php' ) {
			$value['parse_subsections'] = 0; // messes with things we don't want
		}
	}
	return $value;
}
/**
 * Shortcode to allow portable references of resources inside the upload directory. 
 */
add_shortcode( 'upload_dir_url', function() {
	$dir = wp_upload_dir();
	return $dir['baseurl'];
});
/**
 * Shortcode for including interactive GeoGebra content.
 */
add_shortcode( 'geogebra', function ( $atts ) {
	$id = $atts['id'];
	$conf = (object) $atts;
	unset( $conf->id );
	$json = json_encode( $conf );
	$out = "<div id=\"$id\"></div>\n";
	$out .= "<script>\n";
	$out .= "window.addEventListener(\"load\", function() {\n";
	$out .= "	let applet = new GGBApplet($json, true);\n";
	$out .= "	applet.inject('$id');\n";
	$out .= "});\n";
	$out .= "</script>\n";
	return $out;
});
/**
 * Add theme options.
 */
add_action( 'admin_init', function() {
	register_setting(
		'pressbooks_theme_options_global',
		'eskript_settings',
		'eskript_settings_sanitizer' // input sanitizer
	);
	// NOTE: Disabled subchapters (for now).
	/***
	add_settings_field(
		'subchapterize',
		__( 'Chapter Subdivision', 'ethskript' ),
		function( $args ) {
			$o = get_option( 'eskript_settings', array() );
			?><input id="subchapterize" name="eskript_settings[subchapterize]" type="checkbox" value="1"<?php echo empty( $o['subchapterize'] ) ? '' : ' checked'; ?>/>
			<input type="hidden" name="eskript_settings[booleans][]" value="subchapterize">
			<label for="subchapterize"><?php echo __( 'Put sections on their own page', 'ethskript' ); ?></label>
			<?php
		},
		'pressbooks_theme_options_global',
		'global_options_section'
	);
	***/
	add_settings_field(
		'geogebra',
		__( 'GeoGebra', 'ethskript' ),
		function( $args ) {
			$o = get_option( 'eskript_settings', array() );
			?><input id="geogebra" name="eskript_settings[geogebra]" type="checkbox" value="1"<?php echo empty( $o['geogebra'] ) ? '' : ' checked'; ?>/>
			<input type="hidden" name="eskript_settings[booleans][]" value="geogebra">
			<label for="geogebra"><?php echo __( 'Enable interactive GeoGebra content.', 'ethskript' ); ?></label>
			<?php
		},
		'pressbooks_theme_options_global',
		'global_options_section'
	);
}, 11);
function eskript_settings_sanitizer( $in ) {
	$in = $in ? $in : array();
	$out = get_option( 'eskript_settings', array() );
	foreach ( $in as $k => $v ) {
		$out[ $k ] = $v;
	}
	if ( ! empty( $in['booleans'] ) ) {
		foreach ( $in['booleans'] as $k ) {
			$out[ $k ] = ! empty( $in[ $k ] );
		}
	}
	return $out;
}
/**
 * Add index numbers to headers and image captions.
 *
 * TODO: Handle tables.
 */
function eskript_enumerate( $content ) {
	if ( strlen( $content ) == 0 ) {
		// Would return xml preamble with empty content otherwise.
		return '';
	}
	// NOTE: shortcodes are already resolved at this point
	// Preserves LaTeX source code (since shortcodes are already resolved).
	// Prevent the XML parser to remove the space between tags like <img ...> <b>foo</b>.
	$content = str_replace( '> <', '>&#32;<', $content );
	libxml_use_internal_errors( true );
	$doc = new \DOMDocument();
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $content );
	$references = eskript_reference_for_id();
	for ( $i = 1; $i <= 6; $i++ ) {
		$sections = $doc->getElementsByTagName( "h$i" );
		foreach ( $sections as $node ) {
			$id = $node->getAttribute( 'id' );
			$ref = @$references[ $id ];
			if ( $ref === null ) {
				continue;
			}
			if ( ! $ref['list'] ) {
				// TODO: don't create references for !in_list or no id?
				continue;
			}
			$index = eskript_index_for_reference( $id );
			$node->insertBefore( new \DOMText( "$index – " ), $node->firstChild );
		}
	}
	$sections = $doc->getElementsByTagName( 'img' );
	foreach ( $sections as $node ) {
		$id = $node->getAttribute( 'id' );
		$ref = @$references[ $id ];
		if ( $ref === null ) {
			continue;
		}
		if ( ! $ref['list'] ) {
			continue;
		}
		$index = eskript_index_for_reference( $id );
		$caption = eskript_find_caption_node( $node );
		if ( ! $caption ) {
			continue;
		}
		$desc = eskript_description_for_ref_type( $ref['type'], true );
		// if ($captionText===hex2bin('c2a0')) $captionText = ''; // &nbsp;
		$prefix = "$desc $index";
		// Use unicode flag to also capture non-breaking whitespace.
		if ( ! preg_match( '/^\s*$/u', $caption->nodeValue ) ) {
			$prefix .= ' – ';
		}
		$caption->insertBefore( new DOMText( $prefix ), $caption->firstChild );
	}
	$html = $doc->saveHTML( $doc->documentElement ); // DEBUG: replaces ' with ’ -> problematic for latex shortcodes
	$html = str_replace( array( '<html>', '</html>', '<body>', '</body>' ), '', $html );
	$html = preg_replace( '#^<!DOCTYPE.+?>#', '', $html );
	$errors = libxml_get_errors();
	libxml_clear_errors();
	return $html;
}
/**
 * Find and return the caption node for a given img node.
 *
 * @param DOMElement $img_node 
 * @return DOMElement caption node, or null if no caption node is found.
 */
function eskript_find_caption_node( $img_node ) {
	$node = $img_node;
	while ( true ) {
		$node = $node->parentNode;
		if ( ! is_a( $node, 'DOMElement' ) ) {
			return null;
		}
		$class = $node->getAttribute( 'class' );
		if ( in_array( 'wp-caption', explode( ' ', $class ) ) ) {
			break;
		}
	}
	$out = null;
	foreach ( $node->childNodes as $n ) {
		if ( ! is_a( $n, 'DOMElement' ) ) {
			continue;
		}
		if ( $n->tagName != 'p' ) {
			continue;
		}
		$class = $n->getAttribute( 'class' );
		if ( ! in_array( 'wp-caption-text', explode( ' ', $class ) ) ) {
			continue;
		}
		$out = $n;
		break;
	}
	return $out;
}
