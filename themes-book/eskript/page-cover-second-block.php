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
				 /**
					* @author Brad Payne <brad@bradpayne.ca>
					* @copyright 2014 Brad Payne
					* @since 3.8.0
					*/

					$files = \Pressbooks\Utility\latest_exports();
					$site_option = get_site_option( 'pressbooks_sharingandprivacy_options', array( 'allow_redistribution' => 0 ) );
					$option = get_option( 'pbt_redistribute_settings', array( 'latest_files_public' => 0 ) );
					if ( ! empty( $files ) && ( true == $site_option['allow_redistribution'] ) && ( true == $option['latest_files_public'] ) ) { ?>
						<div class="alt-formats">
							<h4><?php _e( 'Download in the following formats:', 'pressbooks' ); ?></h4>
							<?php foreach ( $files as $filetype => $filename ) :
								$filename = preg_replace( '/(-\d{10})(.*)/ui', "$1", $filename );

								// Rewrite rule
								$url = home_url( "/open/download?type={$filetype}" );

								// Tracking event defaults to Google Analytics (Universal).
								// Filter like so (for Piwik):
								// add_filter('pressbooks_download_tracking_code', function( $tracking, $filetype ) {
								//  return "_paq.push(['trackEvent','exportFiles','Downloads','{$filetype}']);";
								// }, 10, 2);
								// Or for Google Analytics (Classic):
								// add_filter('pressbooks_download_tracking_code', function( $tracking, $filetype ) {
								//  return "_gaq.push(['_trackEvent','exportFiles','Downloads','{$file_class}']);";
								// }, 10, 2);
								$tracking = apply_filters( 'pressbooks_download_tracking_code', "ga('send','event','exportFiles','Downloads','{$filetype}');", $filetype );
							?>
								<link itemprop="bookFormat" href="http://schema.org/EBook">
									<a rel="nofollow" onclick="<?= $tracking; ?>" itemprop="offers" itemscope itemtype="http://schema.org/Offer" href="<?= $url; ?>">
										<span class="export-file-icon small <?= $filetype; ?>" title="<?= esc_attr( $filename ); ?>"></span>
										<meta itemprop="price" content="$0.00">
										<link itemprop="availability" href="http://schema.org/InStock">
									</a>
							<?php endforeach; ?>
						</div>
					<?php }
				?>
				</section> <!-- end .secondary-block -->
