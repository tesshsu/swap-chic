<?php
/**
 * The header for our theme
**/

$path = getPath();

if(is_user_logged_in()) {
	$scope = getScope($_GET);
	$user_id = get_current_user_id();
	$user = wp_get_current_user();	

	if($_GET['s']) {

	} elseif( $path[1] == '#' || $path[1] == 'sign-in' || empty($path[1])) {
		header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
		exit();
	}
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg <? if($_SERVER['REQUEST_URI'] != '/' && !isset($_GET['s'])) { echo explode('/', $_SERVER['REQUEST_URI'])[1]; } elseif(isset($_GET['s'])) { echo 'search'; } else { echo 'intro'; } ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-130173498-18"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'UA-130173498-18');
	</script>

	<?php wp_head(); ?>
</head>

<body <?php if(!is_user_logged_in()){ echo "class='user-guest'"; } else { echo 'data-basescope="'.get_field('code_postal', 'user_'.$user_id).'" '; } ?> >
<?php if(displayLoader($path)) { 
	get_template_part( 'partials/content/content', 'loader' );
} ?>
<?php wp_body_open(); ?>
		<header class="<?php if( !displayHeader($path) ) echo 'mobile-hidden' ?>">
			<h4 class="headerSlogan">Ton vide dressing éco-responsable et locale</h4>
			<nav>
				<div class="profil-toggle"><img src="<?php echo get_template_directory_uri().'/assets/images/menu.svg' ?>" alt="">Menu</div>
				<h1 class="logo"><img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg'?>" alt="Swap-Chic"></h1>
                <div class="desktop nav-desktop social">
				    <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
					<div class="share">							

							<img src="<?php echo get_template_directory_uri().'/assets/images/share.svg'?>" alt="">Invites tes amies

							<span></span>

							<div class="addtoany-wrapper">

								<div class="a2a_kit top a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo get_permalink($post_id) ?>" data-a2a-title="<?php echo get_the_title($post_id) ?>">

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
				<div class="desktop secondMenu" id="secondMenu">
					<ul>
						<li>
							<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit' ?>">
								<img src="<?php echo get_template_directory_uri().'/assets/images/ap.svg'; ?>" alt="">Ajoute un produit
							</a>
						</li>
						<li>
							<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/swap-places' ?>">
							   <img id="spButton" src="<?php echo get_template_directory_uri().'/assets/images/coffee-cup.svg' ?>" alt="">Swap-places
							</a>
						</li>
						<li>
							<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie' ?>" class="">
							  <img src="<?php echo get_template_directory_uri().'/assets/images/message.svg' ?>" alt="">notifications
							</a>
						</li>
					</ul>
				</div>
				<a class="mobile" href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit' ?>">
					<img src="<?php echo get_template_directory_uri().'/assets/images/addproduct.svg'; ?>" alt="">
				</a>
				<h1 class="logo mobile"><img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg'?>" alt="Swap-Chic"></h1>
				<div class="search-toggle">
				    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/liste-de-souhait' ?>">
						<img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'; ?>" alt="">
					</a>
					<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie'; ?>" class="chat-link">
					   <img src="<?php echo get_template_directory_uri().'/assets/images/env.svg' ?>" alt="Messagerie">
					   <?php 
								$notifs = get_field('notifications', 'user_'.$user_id);
								if($notifs) { ?>
									<span class="notifs"><?php echo count($notifs) ?></span> 
							<?php 
									$notifs_confirmation = array();
									foreach($notifs as $notif) {
										if($notif[event] == 'sell' || $notif[event] == 'swap') {
											$notifs_confirmation[] = $notif;
										}
									}
								} 
							?>
				    </a>
				</div>
			</nav>
		</header>
		<?php 
			if(!empty($notifs_confirmation)) {
				//set_query_var('notifs', $notifs_confirmation);
				//get_template_part('partials/content/content', 'notifconf');
			}
		?>
		
	<aside class="profil">
		<div class="user">
		    <img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg'?>" style="width: 200px;" alt="">
			<?php if(is_user_logged_in()) { ?>
			  <div class="inside-part">
				<div class="pp"><img src="<?php echo get_field('photo_profil', 'user_'.$user->ID) ?>" alt=""></div>
				<p class="h1"><a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/editer-profil' ?>"><?php echo ucfirst($user->data->display_name) ?><img src="<?php echo get_template_directory_uri().'/assets/images/cog.svg'; ?>" alt=""></a></p>
			 </div>
			<?php } ?>
		</div>
		<div class="website">
			<img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg'?>" alt="">
		</div>
		<div class="links">
			<div class="profil-links social">
			    <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
				<?php if(is_user_logged_in()) { ?>
					<?php if(in_array('administrator', $user->roles)) { ?>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/swap-places-2' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/coffee-cup.svg'; ?>" alt="">Swap-places
						</a>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/articles-a-valider' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/dressing.svg'; ?>" alt="">Produits à valider
						</a>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/coups-de-coeur' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/dressing.svg'; ?>" alt="">Coups de coeur
						</a>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/stats' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/parrainnage.svg'; ?>" alt="">Statistiques
						</a>
					<?php } else { ?>
					   
						
						<a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user->ID)) ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/dressing.svg'; ?>" alt="">Ton Dressing
						</a>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/liste-de-souhait' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/lds.svg'; ?>" alt="">Wishlist
						</a>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/membres-suivies' ?>">
							<img src="<?php echo get_template_directory_uri().'/assets/images/ms.svg'; ?>" alt="">Membres suivies
						</a>
                        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/on-parler-de-nous-1' ?>" id="blog">
						   <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'; ?>" alt="">On parle de nous
				        </a>						
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/blog' ?>" id="blog">
							<img src="<?php echo get_template_directory_uri().'/assets/images/blog.svg'; ?>" alt="">Notre blog
						</a>
					<?php } 
						$logout_element = wp_loginout( 'https://'.$_SERVER['HTTP_HOST'], false);
						preg_match('/href=".*">/', $logout_element, $logout_tag);
						$logout_url = substr($logout_tag[0], 6, strlen($logout_tag[0]) - 8);
					?>
					<a href="<?php echo $logout_url ?>">
						<img src="<?php echo get_template_directory_uri().'/assets/images/logout.svg'; ?>" alt="">Déconnexion
					</a>
				<?php } else { ?>
					<div class="not-connected">
						<p>Connectez vous pour accéder à toutes les fonctionnalités de Swap-Chic.</p>
						<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="btn">Connexion</a>
					</div>
				<?php } ?>
			</div>
			<div class="website-links">
			<?php
				if ( has_nav_menu( 'story' ) ){
					print '<p class="website-links-title">Notre histoire</p>';
					wp_nav_menu ( array ('theme_location' => 'story' ) );
				}
			?>
			<?php
				if ( has_nav_menu( 'news' ) ){
					print '<p class="website-links-title">News</p>';
					wp_nav_menu ( array ('theme_location' => 'news' ) );
				}
			?>
			<?php
				if ( has_nav_menu( 'about' ) ){
					print '<p class="website-links-title">À propos</p>';
					wp_nav_menu ( array ('theme_location' => 'about' ) );
				}
			?>
			<?php
				if ( has_nav_menu( 'sitemap' ) ){
					print '<p class="website-links-title">Plan du site</p>';
					wp_nav_menu ( array ('theme_location' => 'sitemap' ) );
				}
			?>
			<?php
				if ( has_nav_menu( 'contact' ) ){
					print '<p class="website-links-title">Contact</p>';
					wp_nav_menu ( array ('theme_location' => 'contact' ) );
				}
			?>
			</div>
		</div>
		<div class="links-switch">
			<div class="profil-links-switch">
				<span class="active"><img src="<?php echo get_template_directory_uri().'/assets/images/profil.svg'; ?>" alt="">Mon profil</span>
			</div>
			<div class="website-links-switch">
				<span><img src="<?php echo get_template_directory_uri().'/assets/images/wl.svg'; ?>" alt="">Liens utiles</span>
			</div>
		</div>
		<div class="rs">
			<a href="https://www.okpal.com/swap-chic/#/" target="_blank" class="okpal"><img src="<?php echo get_template_directory_uri().'/assets/images/okpal.svg'; ?>" alt=""></a>
			<a href="https://www.instagram.com/swapchic/" target="_blank" class="insta"><img src="<?php echo get_template_directory_uri().'/assets/images/insta.svg'; ?>" alt=""></a>
			<a href="http://www.facebook.com/swaptobechic" target="_blank" class="fb"><img src="<?php echo get_template_directory_uri().'/assets/images/fb.svg'; ?>" alt=""></a>
			<a href="http://www.pinterest.com/swapchic/" target="_blank" class="pinterest"><img src="<?php echo get_template_directory_uri().'/assets/images/pinterest.svg'; ?>" alt=""></a>
			<a href="https://twitter.com/swap_chic" target="_blank" class="twitter"><img src="<?php echo get_template_directory_uri().'/assets/images/twitter.svg'; ?>" alt=""></a>
		</div>
		<p class="copyright">Copyright © 2019 Swap-Chic</p>
		<div class="overlay"></div>
	</aside>

	<main 
		<?php
			if($_SERVER['REQUEST_URI'] != '/' && !isset($_GET['s'])) {
				echo 'class="'.explode('/', $_SERVER['REQUEST_URI'])[1].'" ';
			}
		?>>
	<?php 
		if(isset($_GET['s'])) {
			set_query_var('results', $wp_query->posts);
			get_template_part( 'partials/listing', 'results' );
		}
	?>