<?php
/**
 * The template for displaying the footer
**/

?>
	</main>
	<?php if(displayFooter(getPath())) { 
	      $share_title = "Partager swap-chic pour Green life";
		  if ( is_home() && ! is_front_page() ) {
			  $share_link = 'https://'.$_SERVER['HTTP_HOST'].'/';
		  }else{
			   $share_link = get_permalink(get_the_ID());
		  }
	;?>
		<footer class="mobile social">
		    <div class="social-close" onclick="closeSocial(this)"><i class="fas fa-times-circle"></i></div>		   
			<div class="share">
                Invite tes amies
                <i class="fas fa-share-alt"></i>

                <span></span>

                <div class="addtoany-wrapper">

                    <div class="a2a_kit a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/'; ?>" data-a2a-title="<?php echo get_the_title($share_title) ?>">

                        <a class="a2a_button_facebook"></a>

                        <a class="a2a_button_whatsapp"></a>
						
						<a class="a2a_button_facebook_messenger"></a>
                        
                        <a class="a2a_button_email"></a>
						
						<a class="a2a_button_twitter"></a>

                        <a class="a2a_button_pinterest"></a>

                    </div>
                </div>
            </div>
			<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/on-parler-de-nous-1/' ?>" id="blog">
					On parle de nous&nbsp; 
					<i class="far fa-comments"></i>
			</a>
		</footer>
	<?php } ?>

<?php wp_footer(); ?>

</body>
</html>

