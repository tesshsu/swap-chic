<?php 
    $notif = get_query_var('notif');
    // print_r($notif);
?>

<div class="confirmation confirmation-sell">
    <p class="h1">Confirmation de vente</p>
    <p><?php echo ucfirst(get_userdata($notif[provenance])->data->display_name) ?> nous a informé que tu lui as acheté le produit suivant : </p>
    <div class="product">
        <?php echo get_the_post_thumbnail($notif[sujet]) ?>
        <p class="h1"><?php echo get_the_title($notif[sujet]) ?></p>
    </div>
    <p>Peux-tu nous confirmer que tu as bien acheté ce produit ?</p>
    <div class="sell-popup-action">
        <div class="confirm btn" onclick="confirmSell(<?php echo $notif[sujet]?>, <?php echo $notif[provenance]?>, this)">Oui</div>
        <div class="deny btn" onclick="denySell(<?php echo $notif[sujet]?>, <?php echo $notif[provenance]?>, this)">Non</div>
    </div>
</div>