<div class="addproduct-title">
    <?php // If the user comes from the sign up page, the close button sends him to the homepage ?>
    <a href="<?php if( $_GET['from_signup'] == 1) { echo 'https://'.$_SERVER['HTTP_HOST'].'/actualites/'; } else { echo 'javascript:history.back()'; } ?>" class="addproduct-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <h1 class="h1">Ajouter un produit</h1>
</div>
<div class="addproduct-wrapper">
    <form method="post" id="addproduct-form">
        <?php
            //get_template_part( 'partials/content/content', 'helpaddproduct' );
            get_template_part( 'partials/form/addproduct', 'required');
            get_template_part( 'partials/form/addproduct', 'optional');
        ?>
        <div class="step step-confirmation locked">
            <p class="h2">Confirmation</p>
            <p class="h3">Ton formulaire est complet !</p>
            <br>
            <p>Nous t'informons qu'avant d'apparaitre dans notre catalogue, ton produit sera vérifié par notre équipe, cette opération peut prendre jusqu'à 48h.<br>En attendant, retrouve le dans ton dressing.<br></p>
            <br>
            <p class="h3">Bon swap <?php echo ucfirst(get_userdata(get_current_user_id())->data->display_name); ?> !</p>
            <input type="submit" value="Confirmer" class="btn">
            <div class="expand"><img src="<?php echo get_template_directory_uri().'/assets/images/lock.svg' ?>" alt=""></div>
        </div>
    </form>
</div>