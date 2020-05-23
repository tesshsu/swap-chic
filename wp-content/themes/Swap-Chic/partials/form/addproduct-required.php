<div class="step step-images required">
    <p class="h2">Images</p>
    <p class="h3">Ajoute 3 photos de ton produit :</p>
    <p class="info"><b>⚠️ Le produit ne doit pas être porté sur la première photo et le fond doit être différent de la couleur de votre article car celle ci sera détourée.</b> Pour réussir tes autres photos juste : reste dans le cadre, bien centré et au premier plan. A toi de jouer 😉</p>
    <div class="images">
        <fieldset class="image">
            <label for="addproduct-image-front" class="custom-file-upload">
                <div id="image-front">
                    <input type="hidden" name="image-front" value="" required>
                </div>
                <span>Photo 1</span>
                <input type="file" name="addproduct-image-front" id="addproduct-image-front" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions hidden">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
        <fieldset class="image">
            <label for="addproduct-image-back" class="custom-file-upload">
                <div id="image-back">
                    <input type="hidden" name="image-back" value="" required>
                </div>
                <span>Photo 2</span>     
                <input type="file" name="addproduct-image-back" id="addproduct-image-back" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions hidden">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
        <fieldset class="image">
            <label for="addproduct-image-label" class="custom-file-upload">
                <div id="image-label">
                    <input type="hidden" name="image-label" value="" required>
                </div>
                <span>Photo 3</span>     
                <input type="file" name="addproduct-image-label" id="addproduct-image-label" onchange="readURL(this);" required>      
            </label>
                <div class="image-actions hidden">
                    <div class="turn-right btn"><img src="<?php echo get_template_directory_uri().'/assets/images/turn-right.svg'?>" alt=""></div>
                </div>
        </fieldset>
    </div>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/arrowbot-white.svg' ?>" alt=""></div>
</div>
<div class="step step-action locked required">
    <p class="h2">Action</p>
    <p class="h3">Que veux tu faire de ce produit ?</p>
    <label for="addproduct-swap"><input type="checkbox" value="swap" name="action[]" id="addproduct-swap"  >Le swaper</label>
    <label for="addproduct-sell"><input type="checkbox" value="sell" name="action[]" id="addproduct-sell"  >Le vendre</label>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-price locked required">
    <p class="h2">Prix</p>
    <p class="h3">Indique son prix :</p>
    <input type="text" name="price" id="addproduct-price" placeholder="Prix sans le symbole '€'">
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-categorie-parente locked required">
    <p class="h2">Pour</p>
    <p class="h3">À qui est destiné ce produit ?</p>
    <label for="addproduct-femme"><input type="radio" value="femme" name="target" id="addproduct-femme">Aux femmes</label>
    <label for="addproduct-enfant"><input type="radio" value="enfant" name="target" id="addproduct-enfant">Aux enfants</label>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-categorie locked femme required">
    <p class="h2">Catégorie</p>
    <p class="h3">Sélectionne une catégorie :</p>
    <select class="categories categorie-femme" name="category" id="addproduct-category-femme">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea25e37321a')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-categorie locked hidden enfant required">
    <p class="h2">Catégorie</p>
    <p class="h3">Sélectionne une catégorie :</p>
    <select class="categories categorie-enfant" name="category" id="addproduct-category-enfant">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea279d7321b')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked accessoires required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-accessoires">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f3a54d0e29')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden vetements required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-vetements">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f3451baa36')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden chaussures required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-chaussures">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f351de3768')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden sacs required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-sacs">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f365da956f')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden bijoux required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-bijoux">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e0f370a97371')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden lingerie required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-lingerie">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e41030931e71')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden makeup required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-makeup">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e41038e4d41f')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-sous-categorie locked hidden sports required">
    <p class="h2">Sous-catégorie</p>
    <p class="h3">Sélectionne une sous-catégorie :</p>
    <select name="subcategory" id="addproduct-subcategory-sport">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5e41042fbb42c')['choices'] as $key => $value) {?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-taille locked vetements required">
    <p class="h2">Taille</p>
    <p class="h3">Indique la taille de ton produit :</p>
    <select name="taille" id="addproduct-taille-vetement-femme">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea230373218')['choices'] as $key => $value) { ?>
            <option value="<?php echo $value ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-taille locked hidden vetements-enfant required">
    <p class="h2">Taille</p>
    <p class="h3">Indique la taille de ton produit :</p>
    <select name="taille" id="addproduct-taille-vetement-enfant">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea28d3bb050')['choices'] as $key => $value) {?>
            <option value="<?php echo $value ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-taille locked hidden chaussures required">
    <p class="h2">Taille</p>
    <p class="h3">Indique la taille de ton produit :</p>
    <select name="taille" id="addproduct-taille-chaussures-femme">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea2846bb04f')['choices'] as $key => $value) {?>
            <option value="<?php echo $value ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-taille locked hidden chaussures-enfant required">
    <p class="h2">Taille</p>
    <p class="h3">Indique la taille de ton produit :</p>
    <select name="taille" id="addproduct-taille-chaussures-enfant">
        <option value="" disabled selected>Selectionne une valeur...</option>
        <?php foreach(get_field_object('field_5dea29e3bb051')['choices'] as $key => $value) {?>
            <option value="<?php echo $value ?>"><?php echo $value ?></option>
        <?php } ?>
    </select>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-etat locked required">
    <p class="h2">État</p>
    <p class="h3">Indique son état :</p>
    <label for="addproduct-Neuf-avec-étiquette"><input type="radio" value="Neuf avec étiquette" name="etat" id="addproduct-Neuf-avec-étiquette">Neuf avec étiquette</label>
    <label for="addproduct-Neuf-sans-étiquette"><input type="radio" value="Neuf sans étiquette" name="etat" id="addproduct-Neuf-sans-étiquette">Neuf sans étiquette</label>
    <label for="addproduct-Quasi-neuf"><input type="radio" value="Quasi-neuf" name="etat" id="addproduct-Quasi-neuf">Quasi-neuf</label>
    <label for="addproduct-Bon"><input type="radio" value="Bon" name="etat" id="addproduct-Bon">Bon</label>
    <label for="addproduct-Correct"><input type="radio" value="Correct" name="etat" id="addproduct-Correct">Correct</label>
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>
<div class="step step-marque locked required">
    <p class="h2">Marque</p>
    <p class="h3">Indique sa marque :</p>
    <input type="text" name="marque" id="addproduct-marque">
    <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
</div>