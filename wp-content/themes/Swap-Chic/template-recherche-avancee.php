<?php
/*
Template Name: Recherche avancée
Template Post Type: page
*/

get_header();
?>

<form method="get" id="advanced-search-form">       
    <div class="asf-produits">                                    
        <input class="checkbox-tools" type="radio" value="femme" name="target" id="asf-femme">
						<label class="for-checkbox-tools" for="asf-femme">
							<img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'; ?>" alt="">
							Femme
						</label><!--
						-->
        <input class="checkbox-tools" type="radio" value="enfant" name="target" id="asf-enfant">
						<label class="for-checkbox-tools" for="asf-enfant">
							<img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'; ?>" alt="">
							Enfant
						</label><!--
						-->
        <input class="checkbox-tools" type="radio" value="vip" name="target" id="asf-vip">
						<label class="for-checkbox-tools" for="asf-vip">
							<img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'; ?>" alt="">
							VIP
						</label>
        
		
					
    </div>
    <div class="asf-actions">
        <div class="asf-submit btn">Lancer la recherche</div>
    </div>
</form>
<?php if($_GET) { ?>

    <div id="search-results">
        <?php displayAdvancedSearchPosts(getAdvancedSearchPosts(get_current_user_id(), $_GET)); ?>
    </div>
<?php
} ?>

<?php

get_footer();

?>