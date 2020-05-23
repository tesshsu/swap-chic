<div id="scope-modal" class="modal">
    <p class="h2">Changer de zone géographique</p>
    <p>Saisissez une région, un département, une ville ou un code postal&nbsp;:</p>
    <form action="" method="get">
        <input type="text" name="scope" placeholder="" required>
        <input type="submit" value="Changer" class="btn">
        <div id="scope-close">Annuler</div>
    </form>
    <div class="geoauto">
        <p>Ou géolocalisez vous automatiquement :</p>
        <div class="btn" id="geolocalisation"><img src="<?php echo get_template_directory_uri().'/assets/images/target.svg' ?>" alt=""></div>
    </div>
</div>