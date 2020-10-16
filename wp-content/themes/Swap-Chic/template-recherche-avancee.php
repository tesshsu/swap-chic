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
        <div class="drawer asf-category-femme locked">
            <p class="h2">Catégories (femmes)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea25e37321a')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="radio" value="<?php echo $key ?>" name="category[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
        </div>
        <div class="drawer asf-category-enfant locked">
            <p class="h2">Catégories (enfants)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea279d7321b')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="radio" value="<?php echo $key ?>" name="category[]" id="asf-<?php echo $key ?>"  ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-accessoires" style="display:none">
            <p class="h2">Sous-catégories (accessoires)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f3a54d0e29')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-accessoires[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-vetements" style="display:none">
            <p class="h2">Sous-catégories (vêtements)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f3451baa36')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-vetements[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-chaussures" style="display:none">
            <p class="h2">Sous-catégories (chaussures)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f351de3768')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-chaussures[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-sacs" style="display:none">
            <p class="h2">Sous-catégories (sacs)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f365da956f')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-sacs[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-bijoux" style="display:none">
            <p class="h2">Sous-catégories (bijoux)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f370a97371')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-bijoux[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-lingerie" style="display:none">
            <p class="h2">Sous-catégories (lingerie / maillots de bain)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e41030931e71')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-lingerie[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-makeup" style="display:none">
            <p class="h2">Sous-catégories (produits de beauté)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e41038e4d41f')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-makeup[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-subcategory asf-subcategory-sports" style="display:none">
            <p class="h2">Sous-catégories (sports / loisirs)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e41042fbb42c')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="subcategory-sports[]" id="asf-<?php echo $key ?>"  ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-taille asf-taille-femme-vetements" style="display:none">
            <p class="h2">Taille (vêtements femmes)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea230373218')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="taille[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-taille asf-taille-enfant-vetements" style="display:none">
            <p class="h2">Taille (vêtements enfants)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea28d3bb050')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="taille[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-taille asf-taille-femme-chaussures" style="display:none">
            <p class="h2">Taille (chaussures femmes)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea2846bb04f')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="taille[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-taille asf-taille-enfant-chaussures" style="display:none">
            <p class="h2">Taille (chaussures enfants)<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea29e3bb051')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="taille[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-etat">
            <p class="h2">État<span class="check-all">Cocher tout</span></p>
            <label for="asf-Neuf-avec-étiquette">
                <input type="checkbox" value="Neuf avec étiquette" name="etat[]" id="asf-Neuf-avec-étiquette" >Neuf avec étiquette
            </label>
            <label for="asf-Neuf-sans-étiquette">
                <input type="checkbox" value="Neuf sans étiquette" name="etat[]" id="asf-Neuf-sans-étiquette" >Neuf sans étiquette
            </label>
            <label for="asf-Quasi-neuf">
                <input type="checkbox" value="Quasi-neuf" name="etat[]" id="asf-Quasi-neuf" >Quasi-neuf
            </label>
            <label for="asf-Bon">
                <input type="checkbox" value="Bon" name="etat[]" id="asf-Bon" >Bon
            </label>
            <label for="asf-Correct">
                <input type="checkbox" value="Correct" name="etat[]" id="asf-Correct" >Correct
            </label>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-couleur">
            <p class="h2">Couleur<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5dea219f73216')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="couleur[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-imprime">
            <p class="h2">Imprimé<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f3060688f0')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="imprime[]" id="asf-<?php echo $key ?>" ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-matiere">
            <p class="h2">Matière<span class="check-all">Cocher tout</span></p>
            <?php foreach(get_field_object('field_5e0f2efd94455')['choices'] as $key => $value) {?>
                <label for="asf-<?php echo $key ?>">
                    <input type="checkbox" value="<?php echo $key ?>" name="matiere[]" id="asf-<?php echo $key ?>"  ><?php echo $value ?>
                </label>
            <?php } ?>
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
        </div>
        <div class="drawer closed asf-saison">
            <p class="h2">Saison<span class="check-all">Cocher tout</span></p>
            <label for="asf-Automne">
                <input type="checkbox" value="Automne" name="saison[]" id="asf-Automne"  >Automne
            </label>
            <label for="asf-Hiver">
                <input type="checkbox" value="Hiver" name="saison[]" id="asf-Hiver"  >Hiver
            </label>
            <label for="asf-Printemps">
                <input type="checkbox" value="Printemps" name="saison[]" id="asf-Printemps"  >Printemps
            </label>
            <label for="asf-Été">
                <input type="checkbox" value="Été" name="saison[]" id="asf-Été"  >Été
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