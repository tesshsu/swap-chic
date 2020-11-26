<div class="end social">
    <p class="h2">Rien de plus à <span class="scope-toggle"><span class="scope"><?php echo $scope_location?></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span> et ses alentours pour le moment...</p>
    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit/' ?>" class="btn add-product">Ajoute un produit</a>
    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/inviter-amies/' ?>" class="btn desktop add-member">Partager le concept</a>
	<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/proposer-swap-place' ?>" class="btn proposer-sp">Propose une swap-place</a>
	<div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
	<div class="share btn desktop">							
						   
							<img src="<?php echo get_template_directory_uri().'/assets/images/share.svg'?>" alt="">Invites tes amies
			 
							<span></span>

							<div class="addtoany-wrapper">

								<div class="a2a_kit top a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/actualites/'; ?>" data-a2a-title="<?php echo get_the_title($post_id) ?>">

									<a class="a2a_button_facebook"></a>

									<a class="a2a_button_whatsapp"></a>
									
									<a class="a2a_button_facebook_messenger"></a>
									
									<a class="a2a_button_email"></a>
									
									<a class="a2a_button_twitter"></a>

									<a class="a2a_button_pinterest"></a>

								</div>

							</div>

						</div>				
</div>