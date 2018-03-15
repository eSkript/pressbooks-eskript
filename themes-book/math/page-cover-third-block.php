			<section class="third-block-wrap">
				<div class="third-block clearfix">
				<h2><?php _e('Table of Contents', 'pressbooks'); ?></h2>
					<ul class="table-of-content" id="table-of-content">
<?php echo eskript_toc(); ?>
					</ul><!-- end #toc -->

				</div><!-- end .third-block -->
			</section> <!-- end .third-block -->

<?php
//make sure plugin is installed and activated
if (function_exists('prog_cover')) {
    prog_cover();
}
?>
