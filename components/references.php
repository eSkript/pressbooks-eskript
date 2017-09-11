<?php

/**
 * Use custom shortcode handler to also handle shortcodes within captions.
 */
add_action( 'eskript_overrides', function() {
	add_filter( 'the_content', function( $content ) {
		// NOTE: The ref shortcode was at one time called rev. This can be removed after all instances are replaced in the database.
		return eskript_shortcode_handler( $content, array( 'ref', 'rev' ), 'escript_ref' );
	}, 8 );
} );

/**
 * Reference shortcode.
 *
 * Options are documented at https://eskript.ethz.ch/lists/chapter/references/
 */
add_shortcode( 'ref', 'escript_ref' );
function escript_ref( $att, $content ) {
	$ref_id = @$att['id'];
	if ( $ref_id === null ) {
		return '';
	}
	$ref = eskript_reference_for_id( $ref_id );
	if ( $ref === null ) {
		return '';
	}
	$o = (isset( $att['d'] )) ? str_split( $att['d'] ) : array();
	$text = array();
	if ( ! in_array( 'a', $o ) ) {
		$text [] = eskript_description_for_ref_type( $ref['type'], true );
	}
	if ( ! in_array( 'n', $o ) ) {
		$text [] = eskript_index_for_reference( $ref_id );
	}
	if ( in_array( 'C', $o ) ) {
		if ( ! $content && isset( $ref['title'] ) ) {
			$text [] = $ref['title'];
		} else {
			$text [] = $content;
		}
	}
	$doLink = ! in_array( 'h', $o );
	$text = implode( ' ', $text );
	if ( $doLink ) {
		$href = get_permalink( $ref['post'] );
		if ( isset( $ref['id'] ) ) {
			$href .= '#' . $ref['id'];
		}
		$alt = isset( $ref['title'] ) ? " alt=\"$ref[title]\"" : '';
		$out = "<a href=\"$href\"$alt>$text</a>";
	} else {
		$out = $text;
	}
	if ( ! in_array( 'b', $o ) ) {
		$out = "($out)";
	}
	return $out;
}

/**
 * Add missing element IDs when saving a post (randomly generated), so they can be referenced.
 */
add_action( 'admin_init', function() {
	add_filter('content_save_pre', function ( $content ) {
		return wp_slash( eskript_add_missing_ids( wp_unslash( $content ) ) );
	});
});


/*
	Helper functions.
*/

/**
 * Add random IDs to referenceable content.
 *
 * Function is applied when saving a post, so all elements be referenced later.
 */
function eskript_add_missing_ids( $content ) {
	// $patterns = array('#<(h\d|img|table|a)(.*?)>#', '#\[(latex)(.*?)\]#');
	$patterns = array( '#<(h\d|img|table|a)(.*?)>#' );
	$positions = array();
	foreach ( $patterns as $pattern ) {
		preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
		foreach ( $matches as $m ) {
			$atts = eskript_get_attributes( $m[2][0] );
			if ( isset( $atts['id'] ) ) {
				continue;
			}
			if ( $m[1][0] == 'a' || isset( $atts['href'] ) ) {
				continue;
			}
			if ( in_array( 'not-in-list', explode( ' ', @$atts['class'] ) ) ) {
				continue;
			}
			// Match needs an ID
			$positions [] = array( $m[2][1], strlen( $m[2][0] ) );
		}
	}
	// Replace from the back to preserve offsets.
	usort( $positions, function( $a, $b ) {
		return $b[0] -$a[0];
	} );
	foreach ( $positions as $pos ) {
		$id = 'z' . substr( md5( rand() ), 0, 12 ); // ID must start with letter.
		$id = count( $pos[1] == 0 ) ? " id=\"$id\"" : "id=\"$id\" ";
		$content = substr_replace( $content, $id, $pos[0], 0 );
	}
	return $content;
}

/**
 * Find referencable content from a HTML string.
 *
 * Shorttags aren't resolved yet.
 * TODO: Create array of regexpr with named tokens, usable to match and replace.
 */
function eskript_find_references( $content ) {
	$out = array();
	$counter = array();
	// preg_match_all('#<(h\d|img|table)(.*?)(/>|>(.*?)</(h\d|table)>)#', $content, $matches, PREG_SET_ORDER);
	// match all headings
	preg_match_all( '#<(h\d)(.*?)>(.*?)</h\d>#', $content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $m ) {
		$atts = eskript_get_attributes( $m[2] );
		$title = wp_strip_all_tags( pb_strip_br( $m[3] ), true );
		$item = eskript_create_reference_item( $counter, $m[1], $atts, $title );
		if ( $item !== false ) {
			$out [] = $item;
		}
	}
	// match other html tags
	preg_match_all( '#<(img|table)(.*?)>#', $content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $m ) {
		$atts = eskript_get_attributes( $m[2] );
		$item = eskript_create_reference_item( $counter, $m[1], $atts );
		if ( $item !== false ) {
			$out [] = $item;
		}
	}
	// match latex formulas
	preg_match_all( '#\[(latex)(.*?)\]#', $content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $m ) {
		$atts = eskript_get_attributes( $m[2] );
		$item = eskript_create_reference_item( $counter, $m[1], $atts );
		if ( $item !== false ) {
			$out [] = $item;
		}
	}
	// match anchors
	preg_match_all( '#<(a)(.*?)>#', $content, $matches, PREG_SET_ORDER );
	foreach ( $matches as $m ) {
		$atts = eskript_get_attributes( $m[2] );
		if ( ! isset( $atts['id'] ) ) {
			continue;
		}
		if ( isset( $atts['href'] ) ) {
			continue;
		}
		if ( in_array( 'post-ref', explode( ' ', @$atts['class'] ) ) ) {
			$out [] = $ref = array(
				'type' => 'post',
				'id' => $atts['id'],
			);
			continue;
		}
		$item = eskript_create_reference_item( $counter, $m[1], $atts );
		if ( $item !== false ) {
			$out [] = $item;
		}
	}
	return $out;
}

/**
 * eskript_find_references helper.
 */
function eskript_create_reference_item( &$counter, $type, $atts, $title = false ) {
	$item = array();
	if ( ! isset( $atts['id'] ) ) {
		return false;
	}
	$item['id'] = $atts['id'];
	$item['type'] = $type;
	if ( $title !== false ) {
		$item['title'] = $title;
	}
	$item['list'] = ! in_array( 'not-in-list', explode( ' ', @$atts['class'] ) );
	// if ($item['list']==false) return false; // leave in for now (possible interface to move back into list)
	if ( $item['list'] ) {
		@$counter[ $type ] += 1;
		if ( $type[0] == 'h' ) {
			$level = intval( $type[1] );
			$item['index'] = array();
			for ( $i = 1; $i <= 6; $i++ ) {
				if ( $i <= $level ) {
					$item['index'] [] = @$counter[ "h$i" ];
				} else {
					$counter[ "h$i" ] = 0;
				}
			}
		} else {
			$item['index'] = array( $counter[ $type ] );
		}
	}
	return $item;
}

/**
 * Returns a reference array for the requested ref_id, or a lookup array if ref_id === false.
 *
 * TODO: Cache this in DB; flush if any content or generating code changes.
 */
function eskript_reference_for_id( $ref_id = false ) {
	static $cache = null;
	if ( $cache === null ) {
		$cache = array();
		$book = pb_get_book_structure();
		// Collect all posts.
		$posts = array();
		foreach ( $book['part'] as $part ) {
			if ( count( $part['chapters'] ) == 0 ) {
				continue;
			}
			$posts = array_merge( $posts, $part['chapters'] );
			// references to part must link to first chapter
			$firstPost = $part['chapters'][0]['ID'];
			$postRef = array(
				'type' => 'part',
				'post' => $firstPost,
				'index' => array(),
			);
			$postRef['title'] = pb_strip_br( $part['post_title'] );
			$cache[ "p-$part[ID]" ] = $postRef;
		}
		$posts = array_merge( $book['front-matter'], $posts, $book['back-matter'] );
		// Add references.
		foreach ( $posts as $post ) {
			$postRef = array(
				'type' => 'post',
				'post' => $post['ID'],
				'index' => array(),
			);
			$postRef['title'] = pb_strip_br( $post['post_title'] );
			$cache[ "p-$post[ID]" ] = $postRef;
			$content = get_post( $post['ID'] )->post_content;
			$data = eskript_find_references( $content );
			foreach ( $data as $d ) {
				$d['post'] = $post['ID'];
				$cache[ $d['id'] ] = $d['type'] == 'post' ? $postRef : $d;
			}
		}
	}
	if ( $ref_id === false ) {
		return $cache;
	} else {
		return @$cache[ $ref_id ];
	}
}

/**
 * Get all all references within a certain post.
 *
 * Returns a dictionary of ref dictionaries by ref_id.
 */
function eskript_references_for_post( $post_id ) {
	static $references_by_post = null;
	if ( $references_by_post === null ) {
		$references_by_post = array();
		foreach ( eskript_reference_for_id() as $id => $ref ) {
			$post = $ref['post'];
			@$references_by_post[ $post ][ $id ] = $ref;
		}
	}
	return $references_by_post[ $post_id ];
}

/**
 * Get the proper index string for a reference.
 *
 * NOTE: Not globally cacheable. Can be different for every user because of post visibility.
 */
function eskript_index_for_reference( $ref_id ) {
	$ref = eskript_reference_for_id( $ref_id );
	if ( $ref === null ) {
		return '';
	}
	if ( ! isset( $ref['index'] ) ) {
		return '';
	}
	$post_id = $ref['post'];
	$index = $ref['index'];
	array_unshift( $index, eskript_index_for_post( $post_id ) );
	return implode( '.', $index );
}

/**
 * Returns the proper index string for a given post.
 *
 * NOTE: Chapter 2 might be chapter 1 for a user who can't access the first chapter.
 */
function eskript_index_for_post( $post_id ) {
	static $cache = null;
	if ( $cache === null ) {
		// eskript_can_read_post
		$book = pb_get_book_structure();
		$n = 1;
		foreach ( $book['front-matter'] as $post ) {
			$can_read = eskript_can_read_post( $post );
			$cache[ $post['ID'] ] = $can_read ? eskript_decimalToRoman( $n++ ) : '';
		}
		$n = 1;
		foreach ( $book['part'] as $part ) {
			foreach ( $part['chapters'] as $post ) {
				$can_read = eskript_can_read_post( $post );
				$cache[ $post['ID'] ] = $can_read ? strval( $n++ ) : '';
			}
		}
		$n = 1;
		foreach ( $book['back-matter'] as $post ) {
			$can_read = eskript_can_read_post( $post );
			$cache[ $post['ID'] ] = $can_read ? eskript_numToAlpha( $n++ ) : '';
		}
	}
	if ( ! isset( $cache[ $post_id ] ) ) {
		return '';
	}
	return $cache[ $post_id ];
}
