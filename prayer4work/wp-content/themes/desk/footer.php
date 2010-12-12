<?php 
/* @package WordPress
 * @subpackage Desk
 */
?>
<div class="clear" style="height: 35px">
</div>
</div>
<!-- /Content --></div>
<!-- /Wrapper -->
<div id="footer">
	<div id="gradfoot"></div>
		
	<div id="centfoot">	
	<div id="aligner">
		<ul id="footbar-left" class="footbar"><li></li>
		<?php dynamic_sidebar(2); ?>	
		</ul>
				<ul id="footbar-center" class="footbar"><li></li>
		<?php dynamic_sidebar(3); ?>	
		</ul>
	</div>
		<ul id="footbar-right" class="footbar"><li></li>
		<?php dynamic_sidebar(4); ?>	
		</ul>

		
		<p class="permfooter">
			
			 </p>
	</div>	
	
</div>
</div>
<!-- /Container -->
<?php if (get_option('desk_analytics_code') != '') { ?>
<?php echo(stripslashes (get_option('desk_analytics_code')));?>
<?php } ?>
<?php wp_footer(); ?>

</body>

</html>
