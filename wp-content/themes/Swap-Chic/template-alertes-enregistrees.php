<?php
    /*
    Template Name: Alertes enregistrées
    Template Post Type: page
    */
    get_header(); 
    $alertes = get_field('alertes', 'user_'.get_current_user_id());
?>

<div class="h2">Vos alertes</div>
<div id="alertes">
    <?php if(!empty($alertes)) { 
        foreach($alertes as $key => $alerte) {?>
        <div class="alerte">
            <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/actualites/?'.substr($alerte['alerte'], 1, strlen($alerte['alerte']) - 1) ?>"><?php echo $alerte['alerte_nom']?></a>
            <p>Supprimer</p>
            <div class="modal">
                <p>Voulez vous vraiment supprimer l'alerte <b>"<?php echo $alerte['alerte_nom']?>" ?</b></p>
                <div class="choice">
                    <div onclick="deleteAlerte(this)" class="confirm">Supprimer</div>
                    <div class="btn close">Annuler</div>
                </div>
            </div>
        </div>
        <?php }
    } else { 
        print '<p style="width: 100%; text-align:center; padding: 40px 20px 0;">Aucune alertes enregistrés...</p>';
     } ?>
</div>


<?php
    get_footer(); 
?>