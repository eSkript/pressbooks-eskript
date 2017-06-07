			<section class="second-block-wrap">
				<div class="second-block clearfix">
						<div class="description-book-info">
							<?php $metadata = pb_get_book_information();?>
							<h2><?php _e('Book Description', 'pressbooks'); ?></h2>
								<?php if ( ! empty( $metadata['pb_about_unlimited'] ) ): ?>
									<p><?php
										$about_unlimited = pb_decode( $metadata['pb_about_unlimited'] );
										$about_unlimited = preg_replace( '/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', $about_unlimited ); // Make valid HTML by removing first <p> and last </p>
										echo $about_unlimited; ?></p>
								<?php endif; ?>

									<div id="share">
									</div>
						</div>

								<?php	$args = $args = array(
										    'post_type' => 'back-matter',
										    'tax_query' => array(
										        array(
										            'taxonomy' => 'back-matter-type',
										            'field' => 'slug',
										            'terms' => 'about-the-author'
										        )
										    )
										); ?>


								<div class="author-book-info">

		      						<?php $loop = new WP_Query( $args );
											while ( $loop->have_posts() ) : $loop->the_post(); ?>
										    <h4><a href="<?php the_permalink(); ?>"><?php the_title();?></a></h4>
											<?php  echo '<div class="entry-content">';
										    the_excerpt();
										    echo '</div>';
											endwhile; ?>

<?php

function pbr_latest_exports() {
	$suffix = array(
	    '._3.epub',
	    '.epub',
	    '.pdf',
	    '.mobi',
	    '.hpub',
	    '.icml',
	    '.html',
	    '.xml',
	    '._vanilla.xml',
	    '._oss.pdf',
	    '.odt',
	);

	$dir = \Pressbooks\Modules\Export\Export::getExportFolder();

	$files = array();

	// group by extension, sort by date newest first 
	foreach ( \Pressbooks\Utility\scandir_by_date( $dir ) as $file ) {
		// only interested in the part of filename starting with the timestamp
		preg_match( '/-\d{10,11}(.*)/', $file, $matches );
		// grab the first captured parenthisized subpattern
		$ext = $matches[1];
		$files[$ext][] = $file;
	}

	// get only one of the latest of each type
	$latest = array();

	foreach ( $suffix as $value ) {
		if ( array_key_exists( $value, $files ) ) {
			$latest[$value] = $files[$value][0];
		}
	}
	
	if (isset($latest['._oss.pdf'])) unset($latest['.pdf']);
	// @TODO filter these results against user prefs

	return $latest;
}

$files = pbr_latest_exports();
$options = get_option( 'pbt_redistribute_settings' );
// $options['latest_files_public'] = true; // for debugging 
if ( ! empty( $files ) && ( true == $options['latest_files_public'] ) ) {
	echo '<div class="alt-formats" style="clear: both;">';
	echo '<h4>Download in the following formats:</h4>';
	
	$dir = \Pressbooks\Modules\Export\Export::getExportFolder();
	foreach ( $files as $ext => $filename ) {
		$file_extension = substr( strrchr( $ext, '.' ), 1 );

		switch ( $file_extension ) {
			case 'html':
				$file_class = 'xhtml';
				break;
			case 'xml':
				$pre_suffix = strcmp( $ext, '._vanilla.xml' );
				$file_class = ( 0 === $pre_suffix) ? 'vanillawxr' : 'wxr';
				break;
			case 'epub':
				$pre_suffix = strcmp( $ext, '._3.epub' );
				$file_class = ( 0 === $pre_suffix ) ? 'epub3' : 'epub';
				break;
			case 'pdf':
				$pre_suffix = strcmp( $ext, '._oss.pdf' );
				$file_class = ( 0 === $pre_suffix ) ? 'mpdf' : 'pdf';
				break;
			default:
				$file_class = $file_extension;
				break;
		}

		$filename = preg_replace( '/(-\d{10})(.*)/ui', "$1", $filename );
		// rewrite rule
		$url = "open/download?filename={$filename}&type={$file_class}";
		// for Google Analytics (classic), change to: 
		// $tracking = "_gaq.push(['_trackEvent','exportFiles','Downloads','{$file_class}']);";
		// for Google Analytics (universal), change to:
		// $tracking = "ga('send','event','exportFiles','Downloads','{$file_class}');";
		// Piwik Analytics event tracking _paq.push('trackEvent', category, action, name)
		$tracking = "_paq.push(['trackEvent','exportFiles','Downloads','{$file_class}']);";

		echo '<link itemprop="bookFormat" href="http://schema.org/EBook">'
		. '<a rel="nofollow" onclick="' . $tracking . '" itemprop="offers" itemscope itemtype="http://schema.org/Offer" href="' . $url . '">'
		. '<span class="export-file-icon small ' . $file_class . '" title="' . esc_attr( $filename ) . '"></span>'
		. '<meta itemprop="price" content="$0.00"><link itemprop="availability" href="http://schema.org/InStock"></a>';
	}
	// end .alt-formats
	echo "</div>";
}

?>

								</div>
					</div><!-- end .secondary-block -->
				</section> <!-- end .secondary-block -->
