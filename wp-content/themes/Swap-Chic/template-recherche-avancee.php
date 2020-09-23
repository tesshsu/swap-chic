<?php
/*
Template Name: Recherche avancée
Template Post Type: page
*/

get_header();

if($_GET) { ?>

    <div id="search-results">
        <?php displayAdvancedSearchPosts(getAdvancedSearchPosts(get_current_user_id(), $_GET)); ?>
    </div>
<?php
}
?>

<form method="get" id="advanced-search-form">
        <p class="h1">Sélectionne la zone d'affichage des articles :</p>
        <label for="asf-city">
            <input type="radio" value="city" name="asf_scope" id="asf-city" checked>Dans ta ville
        </label>
        <label for="asf-department">
            <input type="radio" value="department" name="asf_scope" id="asf-department" >Dans ton département
        </label>
        <label for="asf-region">
            <input type="radio" value="region" name="asf_scope" id="asf-region" >Dans ta région
        </label>
        <label for="asf-elsewhere">
            Ailleurs : <input type="text" name="asf_scope" placeholder="Nom de ville, code département, région, etc...">
        </label>
    </div>
    <div class="asf-actions">
        <div class="asf-submit btn">Lancer la recherche</div>
    </div>
    <div class="asf-produits">
        <p class="h1">Si tu le souhaites, affine ta recherche d'articles grâce aux options ci-dessous :</p>
        <div class="drawer closed asf-action">
            <p class="h2">Action <span class="check-all">Cocher tout</span></p>
            <label for="asf-swap">
                <input type="checkbox" value="swap" name="action[]" id="asf-swap" >À swaper
            </label>
            <label for="asf-sell">
                <input type="checkbox" value="sell" name="action[]" id="asf-sell" >À vendre
            </label>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-price" style="display:none">
            <p class="h2">Prix</p>
            <input type="text" name="prix_min" placeholder="Prix minimum">
            <input type="text" name="prix_max" placeholder="Prix maximum">
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-target">
            <p class="h2">Pour</p>
            <label for="asf-femme">
                <input type="radio" value="femme" name="target" id="asf-femme" >Femme
            </label>
            <label for="asf-enfant">
                <input type="radio" value="enfant" name="target" id="asf-enfant" >Enfant
            </label>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        
    </div>
    <div class="asf-actions">
        <div class="asf-submit btn">Lancer la recherche</div>
    </div>
</form>

<?php

get_footer();

?>