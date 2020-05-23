<?php if(!isset($_COOKIE["hide-help-addproduct"]) || $_COOKIE["hide-help-addproduct"] != 1){ ?>
    <div class="help" id="help-addproduct">
        <img src="<?php echo get_template_directory_uri().'/assets/images/close-white.svg'; ?>" alt="">
        <p class="h2">Images</p>
        <p><b style="color:#8B0000; text-transform:uppercase">Pour la première photo, le produit ne doit pas être porté : </b>prends une photo bien cadrée, avec l'article centré au premier plan et sur un fond d'une couleur différente de celle de l'article.</p>
        <label for="hide-help-addproduct"><input type="checkbox" name="hide-help-addproduct" id="hide-help-addproduct">Cacher les aides</label>
    </div>
<?php } ?>