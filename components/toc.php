<?php
/**
 * Table of content functions.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

/**
 * Table of Content shortcode.
 *
 * Adds a ToC of the whole book.
 */
add_shortcode( 'toc', 'escript_toc' );
function escript_toc( $att ) {
	$levels = isset( $att['levels'] ) ? (int) $att['levels'] : null;
	$toc = eskript_toc( $levels );
	return "<ul style=\"font-size: 0.875em; list-style: none; padding-left: 0;\">$toc</ul>\n";
}

/**
 * Mini Table of Content shortcode (per post).
 *
 * Adds a ToC of the current post / chapter.
 */
add_shortcode( 'posttoc', 'escript_posttoc' );
function escript_posttoc( $att ) {
	$levels = isset( $att['levels'] ) ? (int) $att['levels'] + 1 : null;
	$toc = eskript_post_toc( null, $levels );
	return "<ul style=\"font-size: 0.875em; list-style: none; padding-left: 0;\">$toc</ul>\n";
}

/**
 * Helper functions.
 */

function eskript_toc_item_for_posts( $posts, $levels, $class = '', $indexed = true ) {
	$out = '';
	foreach ( $posts as $n => $post ) {
		if ( ! eskript_can_read_post( $post ) ) {
			continue;
		}
		$pid = $post['ID'];
		$type = pb_get_section_type( get_post( $pid ) );
		$ref_id = "p-$pid";
		$sub = eskript_toc_section_items( $pid, $levels, $indexed );
		$out .= eskript_toc_item_for_post( $ref_id, "$class $type", $indexed, $sub );
	}
	return $out;
}

function eskript_toc_item_for_post( $ref_id, $class = '', $indexed = true, $sub = '' ) {
	$ref = eskript_reference_for_id( $ref_id );
	$href = get_permalink( $ref['post'] );
	if ( $ref['type'] != 'post' ) {
		$href .= '#' . $ref['id'];
	}
	$index = $indexed ? eskript_index_for_reference( $ref_id ) . ' – ' : '';
	$out = "<li class=\"$class\">\n"
		. "<a href=\"$href\">$index$ref[title]</a>\n"
		. $sub
		. "</li>\n";
	return $out;
}

function eskript_toc_section_items( $post_id, $levels, $indexed = true ) {
	$out = '';
	$sections = array();
	$refs = eskript_references_for_post( $post_id );
	foreach ( $refs as $ref ) {
		if ( $ref['type'][0] != 'h' ) {
			continue;
		}
		if ( ! $ref['list'] ) {
			continue;
		}
		if ( count( $ref['index'] ) > $levels -1 ) {
			continue;
		}
		$sections [] = $ref;
	}
	if ( count( $sections ) == 0 ) {
		return $out;
	}
	$out .= "<ul class=\"sections\">\n";
	foreach ( $sections as $ref ) {
		$out .= eskript_toc_item_for_post( $ref['id'], 'section', $indexed ) . "\n";
	}
	$out .= "</ul>\n";
	return $out;
}

function eskript_toc( $levels = null ) {
	// $levels = 3;
	$options = get_option( 'pressbooks_theme_options_global' );
	$indexed = ! empty( $options['chapter_numbers'] );
	if ( $levels === null ) {
		$levels = @$options['toc_levels'];
		if ( empty( $levels ) ) {
			$levels = 1;
		}
	}

	$book = pb_get_book_structure();
	$out = '';

	$out .= "<li>\n<ul class=\"front-matter\">\n";
	$out .= eskript_toc_item_for_posts( $book['front-matter'], $levels, 'front-matter', $indexed );
	$out .= "</ul>\n</li>\n";

	foreach ( $book['part'] as $part ) {
		$title = pb_strip_br( $part['post_title'] );
		if ( count( $book['part'] ) > 1  && get_post_meta( $part['ID'], 'pb_part_invisible', true ) !== 'on' ) {
			$out .= "<li>\n<h4>$title</h4>\n</li>\n";
		}

		$out .= "<li>\n<ul>\n";
		$out .= eskript_toc_item_for_posts( $part['chapters'], $levels, 'chapter', $indexed );
		$out .= "</ul>\n</li>\n";
	}

	$out .= "<li>\n<ul class=\"back-matter\">\n";
	$out .= eskript_toc_item_for_posts( $book['back-matter'], $levels, 'back-matter', $indexed );
	$out .= "</ul>\n</li>\n";

	return $out;
}

function eskript_post_toc( $post = null, $levels = null ) {
	if ( $post === null ) {
		$post = get_post( null, 'ARRAY_A' );
	}
	if ( $levels === null ) {
		$levels = 2;
	}
	$options = get_option( 'pressbooks_theme_options_global' );
	$indexed = ! empty( $options['chapter_numbers'] );
	$out = '';
	$out .= "<ul>\n";
	$out .= eskript_toc_section_items( $post['ID'], $levels, $indexed );
	$out .= "</ul>\n";
	return $out;
}
