<?php
/**
 * Stuff for admins.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

add_action('admin_menu', function() {
	add_submenu_page( 'tools.php', 'eskript Debug', 'eskript Debug', 'manage_options', 'eskript-debug', 'eskript_debug_screen' );
});

function eskript_debug_screen() {
	echo '<div class="wrap">';
	echo '<h2>eskript Debug</h2>';

	$book = pb_get_book_structure();
	// Collect all posts.
	$posts = array();
	foreach ( $book['part'] as $part ) {
		$posts = array_merge( $posts, $part['chapters'] );
	}
	$posts = array_merge( $book['front-matter'], $posts, $book['back-matter'] );
	// Add references.
	$references = array();
	$cache = array();
	foreach ( $posts as $post ) {
		$ref = array(
			'type' => 'post',
			'post' => $post['ID'],
			'index' => array(),
		);
		$ref['title'] = pb_strip_br( $post['post_title'] );
		$cache[ "p-$post[ID]" ] = array( $ref );
		$content = get_post( $post['ID'] )->post_content;
		$data = eskript_find_references( $content );
		foreach ( $data as $d ) {
			$d['post'] = $post['ID'];
			$cache[ $d['id'] ] [] = $d;
		}
		// Find ref shortcodes.
		preg_match_all( '#\[(ref|rev)(.*?)\]#', $content, $matches, PREG_SET_ORDER );
		foreach ( $matches as $m ) {
			$atts = eskript_get_attributes( $m[2] );
			if ( ! isset( $atts['id'] ) ) {
				continue;
			}
			$references[ $atts['id'] ] [] = $post['ID'];
		}
	}

	// Find duplicates.
	$dups = array();
	foreach ( $cache as $id => $d ) {
		if ( count( $d ) === 1 ) {
			continue;
		}
		$dups [] = $id;
	}

	echo "<h3>Duplicate IDs</h3>\n";
	foreach ( $dups as $id ) {
		echo "<div>\n";
		echo "<h4>ID: $id</h4>\n";
		$posts = array();
		foreach ( $cache[ $id ] as $ref ) {
			$post = $ref['post'];
			$href = get_permalink( $post );
			$posts [] = "<a href=\"$href\">post: $post</a>\n";
		}
		echo 'Appears in: ' . implode( ', ', $posts ) . "<br />\n";
		$posts = array();
		if ( isset( $references[ $id ] ) ) {
			foreach ( $references[ $id ] as $post ) {
				$href = get_permalink( $post );
				$posts [] = "<a href=\"$href\">post: $post</a>\n";
		}
		}
		echo 'Referenced in: ' . implode( ', ', $posts ) . "<br />\n";
		echo "</div>\n";
	}
	// Find dead references.
	echo "<h3>Dead References</h3>\n";
	echo "<div>\n";
	foreach ( $references as $id => $post_ids ) {
		if ( isset( $cache[ $id ] ) ) {
			continue;
		}
		$posts = array();
		foreach ( $post_ids as $post ) {
			$href = get_permalink( $post );
			$posts [] = "<a href=\"$href\">post: $post</a>\n";
		}
		echo "Dead reference to <em>$id</em> in: " . implode( ', ', $posts ) . "<br />\n";
	}
	echo "</div>\n";
}
