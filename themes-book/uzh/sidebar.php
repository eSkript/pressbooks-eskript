<?php global $blog_id; ?>
	<?php if (get_option('blog_public') == '1' || (get_option('blog_public') == '0' && current_user_can_for_blog($blog_id, 'read'))): ?>

	<div id="sidebar">

		<ul id="booknav">
		<!-- If Logged in show ADMIN -->
			<?php global $blog_id; ?>
			<?php if (current_user_can_for_blog($blog_id, 'edit_posts') || is_super_admin()): ?>
				<li class="admin-btn"><a href="<?php echo get_option('home'); ?>/wp-admin"><?php _e('Admin', 'pressbooks'); ?></a></li>
			<?php endif; ?>
		
				<li class="home-btn"><a href="<?php echo get_option('home'); ?>"><?php _e('Home', 'pressbooks'); ?></a></li>

		<!-- TOC button always there -->
				<li class="toc-btn"><a href="<?php echo get_option('home'); ?>/table-of-contents"><?php _e('Table of Contents', 'pressbooks'); ?></a></li>
			</ul>

		<!-- Pop out TOC only on READ pages -->
		<?php if (is_single()): ?>
		<div id="toc">
			<a href="#" class="close"><?php _e('Close', 'pressbooks'); ?></a>
<?php

$options = get_option( 'pressbooks_theme_options_global' );
$levels = @$options['toc_levels'];

if ($levels > 1) {
	$toc = eskript_post_toc(null, $levels);
	echo "<ul>$toc</ul>\n<hr>\n";
}

?>
			<ul>
<?php echo eskript_toc(1); ?>
			</ul>
		</div><!-- end #toc -->
		<?php endif; ?>


	</div><!-- end #sidebar -->
	<?php endif; ?>
