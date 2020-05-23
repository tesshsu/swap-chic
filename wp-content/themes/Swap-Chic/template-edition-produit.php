<?php
    /*
    Template Name: Édition de produit
    Template Post Type: page
    */
    $current_user_id = get_current_user_id(); 
    $dressing_url = get_permalink(get_field('dressing', 'user_'.$current_user_id));
    if(!isset($_GET['produit'])) {
        header($dressing_url);
        exit();
    } else {
        $post_id = $_GET['produit'];
        if(get_field('proprietaire', $post_id)[ID] != $current_user_id){
            header($dressing_url);
            exit();
        }
    }

    if($_POST) {
        if($_POST['type'] == 'images') {
            $new_array = array();
            foreach(get_field('images', $post_id) as $url) {
                $new_array[] = attachment_url_to_postid($url);
            } 

            if(isset($_POST['image-front']) && $_POST['image-front'] != "") {
                $image_front = array($_POST['image-front']);
                $image_front_id = saveNewProductImages($post_id, $image_front);
                $new_array[0] = $image_front_id[0];
            }
            if(isset($_POST['image-back']) && $_POST['image-back'] != "") {
                $image_back = array($_POST['image-back']);
                $image_back_id = saveNewProductImages($post_id, $image_back);
                $new_array[1] = $image_back_id[0];
            }
            if(isset($_POST['image-label']) && $_POST['image-label'] != "") {
                $image_label = array($_POST['image-label']);
                $image_label_id = saveNewProductImages($post_id, $image_label);
                $new_array[2] = $image_label_id[0];
            }

            update_field('images', $new_array, $post_id);

        } elseif($_POST['type'] == 'action') {
            update_field('action', $_POST['action'], $post_id);
            update_field('prix', $wpdb->escape($_POST['price']), $post_id);
        } elseif($_POST['type'] == 'etat') {
            update_field('etat', $_POST['etat'], $post_id);
        } elseif($_POST['type'] == 'taille-vetements-femme') {
            update_field('taille-vetements-femme', $_POST['taille'], $post_id);
        } elseif($_POST['type'] == 'taille-chaussures-femme') {
            update_field('taille-chaussures-femme', $_POST['taille'], $post_id);
        } elseif($_POST['type'] == 'taille-vetements-enfant') {
            update_field('taille-vetements-enfant', $_POST['taille'], $post_id);
        } elseif($_POST['type'] == 'taille-chaussures-enfant') {
            update_field('taille-chaussures-enfant', $_POST['taille'], $post_id);
        } elseif($_POST['type'] == 'couleur') {
            update_field('couleur', $_POST['couleur'], $post_id);            
        } elseif($_POST['type'] == 'imprime') {
            update_field('imprime', $_POST['imprime'], $post_id);            
        } elseif($_POST['type'] == 'matiere') {
            update_field('matiere', $_POST['matiere'], $post_id);
        } elseif($_POST['type'] == 'saison') {
            update_field('saison', $_POST['saison'], $post_id);            
        } elseif($_POST['type'] == 'desc') {
            $my_post = array(
                'ID'           => $post_id,
                'post_title'   => get_the_title($post_id),
                'post_content' => $_POST['desc'],
            );          
            wp_update_post( $my_post );
        }
       header('Location: https://'.$_SERVER['HTTP_HOST'].'/editer-produit/?produit='.$post_id);
        exit();
    } else {

    get_header(); 
?>

<div class="edit-title">
    <a href="<?php echo $dressing_url ?>" class="edit-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <h1 class="h1">Éditer <?php echo get_the_title(post_id) ?></h1>
</div>


<?php if( get_post_status($post_id) == 'draft') { ?>
    <form method="post" id="edit-product-pictures">
        <div class="images required">
        <p class="h2">Images</p>
        <fieldset class="image">
            <label for="addproduct-image-front" class="custom-file-upload">
                <div id="image-front" class="hasBefore">
                    <input type="hidden" name="image-front" value="" required>
                    <img src="<?php echo get_field('images', $post_id)[0]?>" alt="">
                </div>
                <span>Photo 1</span>
                <input type="file" name="addproduct-image-front" id="addproduct-image-front" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
        <fieldset class="image">
            <label for="addproduct-image-back" class="custom-file-upload">
                <div id="image-back" class="hasBefore">
                    <input type="hidden" name="image-back" value="" required>
                    <img src="<?php echo get_field('images', $post_id)[1]?>" alt="">
                </div>
                <span>Photo 2</span>     
                <input type="file" name="addproduct-image-back" id="addproduct-image-back" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
        <fieldset class="image">
            <label for="addproduct-image-label" class="custom-file-upload">
                <div id="image-label" class="hasBefore">
                    <input type="hidden" name="image-label" value="" required>
                    <img src="<?php echo get_field('images', $post_id)[2]?>" alt="">
                </div>
                <span>Photo 3</span>     
                <input type="file" name="addproduct-image-label" id="addproduct-image-label" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
        </div>
        <input type="hidden" name="type" value="images">
        <input type="submit" value="Valider" class="btn">        
    </form>
<?php } ?>

<form method="post" id="edit-product-action">
    <p class="h2">Action</p>
    <label for="addproduct-swap"><input type="checkbox" value="swap" name="action[]" id="addproduct-swap" <?php if( in_array('À swaper', get_field('action', $post_id))) echo 'checked' ?> >Le swaper</label>
    <label for="addproduct-sell"><input type="checkbox" value="sell" name="action[]" id="addproduct-sell" <?php if( in_array('À vendre', get_field('action', $post_id))) echo 'checked' ?> >Le vendre</label>
    <p class="h2 price-title <?php if( !in_array('À vendre', get_field('action', $post_id))) echo 'hidden' ?>">Prix</p>
    <input type="text" name="price" id="addproduct-price" value="<?php echo get_field('prix', $post_id) ?>" class="<?php if( !in_array('À vendre', get_field('action', $post_id))) echo 'hidden' ?>">
    <input type="hidden" name="type" value="action">
    <input type="submit" value="Valider" class="btn">        
</form>

<?php if(strtolower(get_field('categorie-parente', $post_id)) == 'femme' && get_field('categorie-femme', $post_id)[value] == 'vetements') { ?>
    <form method="post" id="edit-product-taille-vetements-femme">
        <p class="h2">Taille</p>
        <select name="taille" id="addproduct-taille-vetements-femme">
            <option value="" disabled selected>Selectionne une valeur...</option>
            <?php foreach(get_field_object('field_5dea230373218')['choices'] as $key => $value) { ?>
                <option value="<?php echo $value ?>" <?php if( get_field('taille-vetements-femme', $post_id) == $value) echo 'selected' ?>><?php echo $value ?></option>
            <?php } ?>
        </select>
        <input type="hidden" name="type" value="taille-vetements-femme">
        <input type="submit" value="Valider" class="btn">        
    </form>
<?php } ?>

<?php if(strtolower(get_field('categorie-parente', $post_id)) == 'femme' && get_field('categorie-femme', $post_id)[value] == 'chaussures') { ?>
    <form method="post" id="edit-product-taille-chaussures-femme">
        <p class="h2">Taille</p>
        <select name="taille" id="addproduct-taille-chaussures-femme">
            <option value="" disabled selected>Selectionne une valeur...</option>
            <?php foreach(get_field_object('field_5dea2846bb04f')['choices'] as $key => $value) { ?>
                <option value="<?php echo $value ?>" <?php if( get_field('taille-chaussures-femme', $post_id) == $value) echo 'selected' ?>><?php echo $value ?></option>
            <?php } ?>
        </select>
        <input type="hidden" name="type" value="taille-chaussures-femme">
        <input type="submit" value="Valider" class="btn">        
    </form>
<?php } ?>

<?php if(strtolower(get_field('categorie-parente', $post_id)) == 'enfant' && get_field('categorie-enfant', $post_id)[value] == 'vetements') { ?>
    <form method="post" id="edit-product-taille-vetements-enfant">
        <p class="h2">Taille</p>
        <select name="taille" id="addproduct-taille-vetements-enfant">
            <option value="" disabled selected>Selectionne une valeur...</option>
            <?php foreach(get_field_object('field_5dea28d3bb050')['choices'] as $key => $value) { ?>
                <option value="<?php echo $value ?>" <?php if( get_field('taille-vetements-enfant', $post_id) == $value) echo 'selected' ?>><?php echo $value ?></option>
            <?php } ?>
        </select>
        <input type="hidden" name="type" value="taille-vetements-enfant">
        <input type="submit" value="Valider" class="btn">        
    </form>
<?php } ?>

<?php if(strtolower(get_field('categorie-parente', $post_id)) == 'enfant' && get_field('categorie-enfant', $post_id)[value] == 'chaussures') { ?>
    <form method="post" id="edit-product-taille-chaussures-enfant">
        <p class="h2">Taille</p>
        <select name="taille" id="addproduct-taille-chaussures-enfant">
            <option value="" disabled selected>Selectionne une valeur...</option>
            <?php foreach(get_field_object('field_5dea29e3bb051')['choices'] as $key => $value) { ?>
                <option value="<?php echo $value ?>" <?php if( get_field('taille-chaussures-enfant', $post_id) == $value) echo 'selected' ?>><?php echo $value ?></option>
            <?php } ?>
        </select>
        <input type="hidden" name="type" value="taille-chaussures-enfant">
        <input type="submit" value="Valider" class="btn">        
    </form>
<?php } ?>

<form method="post" id="edit-product-etat">
    <p class="h2">État</p>
    <label for="addproduct-Neuf-avec-étiquette"><input type="radio" value="Neuf avec étiquette" name="etat" id="addproduct-Neuf-avec-étiquette" <?php if( get_field('etat', $post_id) == 'Neuf avec étiquette') echo 'checked' ?>>Neuf avec étiquette</label>
    <label for="addproduct-Neuf-sans-étiquette"><input type="radio" value="Neuf sans étiquette" name="etat" id="addproduct-Neuf-sans-étiquette" <?php if( get_field('etat', $post_id) == 'Neuf sans étiquette') echo 'checked' ?>>Neuf sans étiquette</label>
    <label for="addproduct-Quasi-neuf"><input type="radio" value="Quasi-neuf" name="etat" id="addproduct-Quasi-neuf" <?php if( get_field('etat', $post_id) == 'Quasi-neuf') echo 'checked' ?>>Quasi-neuf</label>
    <label for="addproduct-Bon"><input type="radio" value="Bon" name="etat" id="addproduct-Bon" <?php if( get_field('etat', $post_id) == 'bon') echo 'Bon' ?>>Bon</label>
    <label for="addproduct-Correct"><input type="radio" value="Correct" name="etat" id="addproduct-Correct" <?php if( get_field('etat', $post_id) == 'Correct') echo 'checked' ?>>Correct</label>
    <input type="hidden" name="type" value="etat">
    <input type="submit" value="Valider" class="btn">        
</form>


<form method="post" id="edit-product-couleur">
    <p class="h2">Couleur</p>
    <select name="couleur" id="addproduct-couleur">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea219f73216')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>" <?php if( get_field('couleur', $post_id) == $key) echo 'selected' ?>><?php echo $value ?></option>
        <?php } ?>
    </select>
    <input type="hidden" name="type" value="couleur">
    <input type="submit" value="Valider" class="btn">        
</form>


<form method="post" id="edit-product-imprime">
    <p class="h2">Imprimé</p>
    <select name="imprime" id="addproduct-imprime">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f3060688f0')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>" <?php if( get_field('imprime', $post_id) == $key) echo 'selected="selected"' ?>><?php echo $value ?></option>
        <?php } ?>
    </select>
    <input type="hidden" name="type" value="imprime">
    <input type="submit" value="Valider" class="btn">        
</form>


<form method="post" id="edit-product-matiere">
    <p class="h2">Matière</p>
    <select name="matiere" id="addproduct-matiere">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f2efd94455')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>" <?php if( get_field('matiere', $post_id) == $key) echo 'selected="selected"' ?>><?php echo $value ?></option>
        <?php } ?>
    </select>
    <input type="hidden" name="type" value="matiere">
    <input type="submit" value="Valider" class="btn">        
</form>

<!-- <form method="post" id="edit-product-saisons">
    <p class="h2">Saisons</p>
    <label for="addproduct-printemps"><input type="checkbox" value="printemps" name="saison[]" id="addproduct-printemps" <?php if(in_array('printemps', get_field('saison', $post_id))) echo 'checked' ?>>Printemps</label>
    <label for="addproduct-été"><input type="checkbox" value="ete" name="saison[]" id="addproduct-été" <?php if(in_array('ete', get_field('saison', $post_id))) echo 'checked' ?>>Été</label>
    <label for="addproduct-automne"><input type="checkbox" value="automne" name="saison[]" id="addproduct-automne" <?php if(in_array('automne', get_field('saison', $post_id))) echo 'checked' ?>>Automne</label>
    <label for="addproduct-hiver"><input type="checkbox" value="hiver" name="saison[]" id="addproduct-hiver" <?php if(in_array('hiver', get_field('saison', $post_id))) echo 'checked' ?>>Hiver</label>
    <input type="hidden" name="type" value="saisons">
    <input type="submit" value="Valider" class="btn">        
</form> -->


<form method="post" id="edit-product-desc">
    <p class="h2">Détails supplémentaires</p>
    <textarea name="desc" id="addproduct-desc" value="<?php echo get_the_content($post_id); ?>"></textarea>
    <input type="hidden" name="type" value="desc">    
    <input type="submit" value="Valider" class="btn">        
</form>

<?php
  }
get_footer();
?>