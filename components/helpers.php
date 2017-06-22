<?php
/**
 * Generic helper functions that not specific to one single purpose.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

/**
 * Determines whether current user can read a post.
 *
 * @param array $post dictionary as returned by pb_get_book_structure().
 * @return boolean
 */
function eskript_can_read_post( $post ) {
	static $read_private = null; // Simple cache.
	if ( is_admin() && isset( $_GET['page'] ) && 'pb_export' === $_GET['page'] ) {
		return $post['export'];
	}
	if ( 'publish' === $post['post_status'] ) {
		return true;
	}
	// post_status is 'private'.
	if ( null === $read_private ) {
		$blog_id = get_current_blog_id();
		$read_private = current_user_can_for_blog( $blog_id, 'read_private_posts' );
		$read_private |= current_user_can_for_blog( $blog_id, 'read' )
			&& 1 === absint( get_option( 'permissive_private_content' ) );
	}
	return $read_private;
}

/**
 * Get a dictionary from a string of XML attributes.
 *
 * @param string $str String with instances of foo="bar".
 * @return array
 */
function eskript_get_attributes( $str ) {
	$out = array();
	// TODO: support single quotes?
	preg_match_all( '#(\w+)="(.+?)"#', $str, $att, PREG_SET_ORDER );
	foreach ( $att as $a ) {
		$out[ $a[1] ] = $a[2];
	}
	return $out;
}

/**
 * Get the description for a ref type.
 *
 * Here is the place for internationalization.
 * Capitalize names only if it would be capitalized mid sentence.
 *
 * @param string $type Usually a tag name.
 * @param string $capitalize Capitalize first letter.
 * @return string Display name.
 */
function eskript_description_for_ref_type( $type, $capitalize = false ) {
	$type = strtolower( $type );
	if ( 'h' === $type{0} ) {
		$type = 'header';
	}
	$desc = array(
		'post' => 'chapter',
		'header' => 'section',
		'img' => 'figure',
		'table' => 'table',
		'latex' => 'equation',
	);
	$desc = array(
		'post' => 'Kapitel',
		'header' => 'Abschnitt',
		'img' => 'Abbildung',
		'table' => 'Tabelle',
		'latex' => 'Gleichung',
	);
	$out = isset( $desc[ $type ] ) ? $desc[ $type ] : 'reference';
	if ( $capitalize ) {
		$out = ucfirst( $out );
	}
	return $out;
}

/**
 * Generates letter sequences (e.g. for lists).
 *
 * @param int    $integer Position, starting at 1.
 * @param string $upcase Capitalize letters.
 * @return string
 */
function eskript_numToAlpha( $integer, $upcase = true ) {
	$out = '';
	$integer -= 1;
	while ( $integer >= 0 ) {
		$out = chr( $integer % 26 + 0x41 ) . $out;
		$integer = intval( $integer / 26 ) - 1;
	}
	return $upcase ? $out : strtolower( $out );
}

/**
 * Generates roman number sequences (e.g. for lists).
 *
 * @param int    $integer Position, starting at 1.
 * @param string $upcase Capitalize letters.
 * @return string
 */
function eskript_decimalToRoman( $integer, $upcase = true ) {
	$table = array(
		'M' => 1000,
		'CM' => 900,
		'D' => 500,
		'CD' => 400,
		'C' => 100,
		'XC' => 90,
		'L' => 50,
		'XL' => 40,
		'X' => 10,
		'IX' => 9,
		'V' => 5,
		'IV' => 4,
		'I' => 1,
	);
	$out = '';
	while ( $integer > 0 ) {
		foreach ( $table as $rom => $arb ) {
			if ( $arb > $integer ) {
				continue;
			}
			$integer -= $arb;
			$out .= $rom;
			break;
		}
	}
	return $upcase ? $out : strtolower( $out );
}

/**
 * Content filter to do our own shortcode substitutions.
 *
 * @param string $content Input.
 * @param string $code Shortcode name or array.
 * @param string $handler Shortcode handler function.
 * @return string
 */
function eskript_shortcode_handler( $content, $code, $handler ) {
	// $pattern = get_shortcode_regex(array('latex', 'katex'));
	// Hardcode pattern in case 'get_shortcode_regex' changes.
	if ( is_array( $code ) ) {
		$code = implode( '|', $code );
	}
	$pattern = '\[(\[?)(' . $code . ')(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
	$content = preg_replace_callback("/$pattern/", function ( $m ) use ( $handler ) {
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
		$content = isset( $m[5] ) ? $m[5] : null;
		$output = $m[1] . call_user_func( $handler, $attr, $content, $tag ) . $m[6];
		return $output;
	}, $content);
	return $content;
}
