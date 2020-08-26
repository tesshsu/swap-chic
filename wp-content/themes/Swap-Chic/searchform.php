<?php
/**
 * Template for displaying search forms
**/

$path = getPath();

?>
<form id="searchform" action="/" method="get" class="<?php if( !displayHeader($path)){ echo 'mobile-hidden hidden';} ?>">
    <div class="search-input">
        <input type="text" name="s" id="search" placeholder="Rechercher..." value="<?php the_search_query(); ?>" />
        <div class="search-submit btn"><img src="<?php echo get_template_directory_uri().'/assets/images/mag-white.svg'; ?>" alt=""></div>
    </div>
    <div class="search-options">
        <div class="checkboxes">
            <label for="search-produits"><input type="checkbox" value="produits" name="post_type[]" id="search-produits" checked />Produits</label>
            <label for="search-dressing"><input type="checkbox" value="dressings" name="post_type[]" id="search-dressing" />Membres</label>
            <label for="search-swapplaces"><input type="checkbox" value="swapplaces" name="post_type[]" id="search-swapplaces" />Swap-places</label>           
		</div>
        <a href="/recherche-avancee">Recherche avancée</a>
        <div class="search-close">
		 <img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt="">
		</div>		
    </div>
	
    </div>
</form>