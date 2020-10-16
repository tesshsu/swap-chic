<div class="filter-open"><p>Filtrer...</p>
    <div class="filter-close" style="display:none"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
    <div class="filters" style="display:none">
        <div id="filterform">
            <div class="filters-produits" style="display:none">
                <p class="h3">Filtres produits :</p>
                <div class="filter-action">
                    <p class="h4">Action : <span class="check-all">Cocher tout</span></p>
                    <label for="filter-swap">
                        <input type="checkbox" value="À swaper" data-name="action" name="action[]" id="filter-swap" <?php if(in_array( 'swap', $filters['action'])) echo 'checked' ?>>À swaper
                    </label>
                    <label for="filter-sell">
                        <input type="checkbox" value="À vendre" data-name="action" name="action[]" id="filter-sell" <?php if(in_array( 'sell', $filters['action'])) echo 'checked' ?>>À vendre
                    </label>
                </div>
                <?php /* <div class="filter-price" style="display:none">
                    <p class="h4">Prix :</p>
                    <input type="text" name="prix_min" data-name="prix" placeholder="Prix minimum">
                    <input type="text" name="prix_max" data-name="prix" placeholder="Prix maximum">
                </div> */ ?>
                <div class="filter-target">
                    <p class="h4">Pour : <span class="check-all">Cocher tout</span></p>
                    <label for="filter-femme">
                        <input type="checkbox" value="femme" name="target[]" data-name="categorie-parente" id="filter-femme" <?php if(in_array( 'femme', $filters['target'])) echo 'checked' ?>>Femme
                    </label>
                    <label for="filter-enfant">
                        <input type="checkbox" value="enfant" name="target[]" data-name="categorie-parente" id="filter-enfant" <?php if(in_array( 'enfant', $filters['target'])) echo 'checked' ?>>Enfant
                    </label>
                </div>
                <div class="filter-category-femme" style="display:none">
                    <p class="h4">Catégorie (femme) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea25e37321a')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="category[]" data-name="categorie-femme" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['category'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-category-enfant" style="display:none">
                    <p class="h4">Catégorie (enfant) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea279d7321b')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="category[]" data-name="categorie-enfant" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['category'])) echo 'checked' ?> ><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-accessoires" style="display:none">
                    <p class="h4">Sous-catégorie (accessoires) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f3a54d0e29')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_accessoires" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-vetements" style="display:none">
                    <p class="h4">Sous-catégorie (vetements) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f3451baa36')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_vetements" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-chaussures" style="display:none">
                    <p class="h4">Sous-catégorie (chaussures) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f351de3768')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_chaussures" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-sacs" style="display:none">
                    <p class="h4">Sous-catégorie (sacs) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f365da956f')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_sacs" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-bijoux" style="display:none">
                    <p class="h4">Sous-catégorie (bijoux) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f370a97371')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_bijoux" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-lingerie" style="display:none">
                    <p class="h4">Sous-catégorie (lingerie et maillots de bain) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e41030931e71')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_lingerie" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-makeup" style="display:none">
                    <p class="h4">Sous-catégorie (produits de beauté) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e41038e4d41f')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_makeup" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-subcategory-sports" style="display:none">
                    <p class="h4">Sous-catégorie (sports et loisirs) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e41042fbb42c')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="subcategory[]" data-name="sous_categorie_sports" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['subcategory'])) echo 'checked' ?> ><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-taille-femme-vetements" style="display:none">
                    <p class="h4">Taille (vetements femmes) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea230373218')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="taille[]" data-name="taille-vetements-femme" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['taille'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-taille-enfant-vetements" style="display:none">
                    <p class="h4">Taille (vetements enfants) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea28d3bb050')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="taille[]" data-name="taille-vetements-enfant" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['taille'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-taille-femme-chaussures" style="display:none">
                    <p class="h4">Taille (chaussures femmes) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea2846bb04f')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="taille[]" data-name="taille-chaussures-femme" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['taille'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-taille-enfant-chaussures" style="display:none">
                    <p class="h4">Taille (chaussures enfants) : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea29e3bb051')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="taille[]" data-name="taille-chaussures-enfant" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['taille'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-etat">
                    <p class="h4">État : <span class="check-all">Cocher tout</span></p>
                    <label for="filter-Neuf-avec-étiquette">
                        <input type="checkbox" value="Neuf avec étiquette" name="etat[]" data-name="etat" id="filter-Neuf-avec-étiquette" <?php if(in_array( 'Neuf avec étiquette', $filters['etat'])) echo 'checked' ?>>Neuf avec étiquette
                    </label>
                    <label for="filter-Neuf-sans-étiquette">
                        <input type="checkbox" value="Neuf sans étiquette" name="etat[]" data-name="etat" id="filter-Neuf-sans-étiquette" <?php if(in_array( 'Neuf sans étiquette', $filters['etat'])) echo 'checked' ?>>Neuf sans étiquette
                    </label>
                    <label for="filter-Quasi-neuf">
                        <input type="checkbox" value="Quasi-neuf" name="etat[]" data-name="etat" id="filter-Quasi-neuf" <?php if(in_array( 'Quasi-neuf', $filters['etat'])) echo 'checked' ?>>Quasi-neuf
                    </label>
                    <label for="filter-Bon">
                        <input type="checkbox" value="Bon" name="etat[]" data-name="etat" id="filter-Bon" <?php if(in_array( 'Bon', $filters['etat'])) echo 'checked' ?>>Bon
                    </label>
                    <label for="filter-Correct">
                        <input type="checkbox" value="Correct" name="etat[]" data-name="etat" id="filter-Correct" <?php if(in_array( 'Correct', $filters['etat'])) echo 'checked' ?>>Correct
                    </label>
                </div>
                <div class="filter-couleur">
                    <p class="h4">Couleur : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea219f73216')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="couleur[]" data-name="couleur" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['couleur'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-imprime">
                    <p class="h4">Imprimé : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f3060688f0')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="imprime[]" data-name="imprime" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['imprime'])) echo 'checked' ?>><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-matiere">
                    <p class="h4">Matière : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5e0f2efd94455')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="matiere[]" data-name="matiere" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['matiere'])) echo 'checked' ?> ><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
                <div class="filter-saison">
                    <p class="h4">Saison : <span class="check-all">Cocher tout</span></p>
                    <label for="filter-Automne">
                        <input type="checkbox" value="Automne" name="saison[]" data-name="saison" id="filter-Automne" <?php if(in_array( 'Automne', $filters['saison'])) echo 'checked' ?> >Automne
                    </label>
                    <label for="filter-Hiver">
                        <input type="checkbox" value="Hiver" name="saison[]" data-name="saison" id="filter-Hiver" <?php if(in_array( 'Hiver', $filters['saison'])) echo 'checked' ?> >Hiver
                    </label>
                    <label for="filter-Printemps">
                        <input type="checkbox" value="Printemps" name="saison[]" data-name="saison" id="filter-Printemps" <?php if(in_array( 'Printemps', $filters['saison'])) echo 'checked' ?> >Printemps
                    </label>
                    <label for="filter-Été">
                        <input type="checkbox" value="Été" name="saison[]" data-name="saison" id="filter-Été" <?php if(in_array( 'Été', $filters['saison'])) echo 'checked' ?> >Été
                    </label>
                </div>
            </div>
            <div class="filters-membres"  style="display:none">
                <p class="h3">Filtres dressigngs :</p>
                <span>Aucun filtre disponible</span>
            </div>
            <div class="filters-swapplaces" style="display:none">
                <p class="h3">Filtres swap-places :</p>
                <div class="filter-sp-type">
                    <p class="h4">Type : <span class="check-all">Cocher tout</span></p>
                    <?php foreach(get_field_object('field_5dea2d1641cfb')['choices'] as $key => $value) {?>
                        <label for="filter-<?php echo $key ?>">
                            <input type="checkbox" value="<?php echo $key ?>" name="type[]" data-name="type" id="filter-<?php echo $key ?>" <?php if(in_array( $key, $filters['type'])) echo 'checked' ?> ><?php echo $value ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
