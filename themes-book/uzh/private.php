<?php 

// in order for this to work, change 'pb_private()' in functions.php from the base template
// to contain 'get_template_part('private');'

$bloginfourl = get_bloginfo('url'); 
$pageURL = get_permalink();
// $loginurl = wp_login_url($pageURL);
$loginurl = $bloginfourl.'/wp-login.php?redirect_to='.urlencode($pageURL).'&action=shibboleth';

?>
<div <?php post_class(); ?>>

			<h2 class="entry-title denied-title"><?php _e('Access Denied', 'pressbooks'); ?></h2>
			<?php if (is_user_logged_in()): ?>
			<div class="entry_content denied-text"><p><?php echo __( 'Sorry, you are not allowed to access this page.' ) ?> You can try to <a href="<?php echo wp_logout_url($loginurl); ?>" class="login">logout</a> and login as a different user.</p></div>
			<?php else: ?>
			<div class="entry_content denied-text"><p>This book is private, and accessible only to registered users. If you have an account you can <a href="<?php echo $loginurl; ?>" class="login">login here</a></p></div>
			<?php endif ?>
		</div><!-- #post-## -->
