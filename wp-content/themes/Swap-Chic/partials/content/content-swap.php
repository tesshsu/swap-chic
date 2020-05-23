<?php 
    $notif = get_query_var('notif');
    $notif[sujet] = explode(',', $notif[sujet]);
?>

<div class="confirmation confirmation-swap">
    <p class="h1">Confirmation d'échange</p>
    <p><?php echo ucfirst(get_userdata($notif[provenance])->data->display_name) ?> nous a informé que vous avez échangé les produits suivants : </p>
    <div class="products">
        <div class="product">
            <?php echo get_the_post_thumbnail($notif[sujet][0]) ?>
            <p class="h1"><?php echo get_the_title($notif[sujet][0]) ?></p>
        </div>
        <p> contre </p>
        <div class="product">
            <?php echo get_the_post_thumbnail($notif[sujet][1]) ?>
            <p class="h1"><?php echo get_the_title($notif[sujet][1]) ?></p>
        </div>
    </div>
    <p>Peux-tu nous confirmer que vous avez bien échangé ces produits ? <b>Cela supprimera les deux produits de leurs dressings respectifs.</b></p>
    <div class="swap-popup-action">
        <div class="confirm btn" onclick="confirmSwap(<?php echo $notif[sujet][1]?>, <?php echo $notif[sujet][0]?>, <?php echo $notif[provenance]?>, this)">Oui</div>
        <div class="deny btn" onclick="denySwap(<?php echo $notif[sujet][1]?>, <?php echo $notif[sujet][0]?>, <?php echo $notif[provenance]?>, this)">Non</div>
    </div>
</div>