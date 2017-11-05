<section class="third-block-wrap">
	<div class="third-block clearfix">
		<h2>
			<?php _e('Table of Contents', 'pressbooks'); ?>
		</h2>
		<ul class="table-of-content" id="table-of-content">
			<?php echo eskript_toc(); ?>
		</ul>
		<!-- end #toc -->
		
		
	
	<div id="plugin_page_cover">
		
	</div>
	
	<div id="user_settings" style="display:none;">
		<h2>Options</h2>
		<?php
		if(is_user_logged_in()){
			$o = get_option( 'eskript_settings', array() );
			if ( ! empty( $o['subchapterize'] ) ) {
				$escript_usersettings = get_user_meta(get_current_user_id(),'escript_subchapters',true);
				$checked = "";
				if($escript_usersettings){
					$checked = "checked";
				}
			
				echo '<form id="section_display" action="'.admin_url( 'admin-post.php' ).'" method="post">
		  <input type="hidden" name="action" value="save_user_settings">
		  <input type="hidden" name="data" value="foobarid">
		  <label><input type="checkbox" name="sections" onchange="document.getElementById(\'section_display\').submit()" '.$checked.'>put subchapters on their own page</label>
		</form>';
			}
		}
		?>
	</div>
		
	</div>
	<!-- end .third-block -->
</section>
<!-- end .third-block -->

<?php
?>
