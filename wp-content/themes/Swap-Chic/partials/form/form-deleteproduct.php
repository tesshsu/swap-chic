<?php
    $produit = get_query_var('product');
    $partners = get_query_var('partners');
    $is_published = get_query_var('is_published');
?>

<div class="deleteproduct" data-id="<?php echo $produit?>" class="modal">
    <p class="h1">Suppression de produit</p>
    <div class="confirm-delete">
        <p>Tu es sur le point de supprimer le produit suivant :</p>
        <div class="product">
            <?php if($is_published) { ?>
                <?php echo get_the_post_thumbnail($produit) ?>
            <?php } else { ?>
                <img src="<?php echo get_field('images', $produit)[0] ?>'" alt="">
            <?php } ?>
            <p class="h1"><?php echo get_the_title($produit) ?></p>
        </div>
        <?php if($is_published) { ?>
            <p>Indique nous la raison :</p>
            <div class="delete-actions">
                <div class="delete-swap btn">Échangé</div>
                <div class="delete-sell btn">Vendu</div>
                <div class="delete-none btn" onclick="deleteProduct(<?php echo $produit?>, this)">Autre raison</div>
                <div class="cancel">Annuler</div>
            </div>
        <?php } else { ?>
            <div class="delete-actions">
                <div class="delete-none btn" onclick="deleteProduct(<?php echo $produit?>, this)">Confirmer</div>
                <div class="cancel">Annuler</div>
            </div>
        <?php } ?>
    </div>
    <div class="confirm-swap hidden">
        <p>Avec qui l'as tu échangé ?</p>
        <select name="swap-partners" id="swap-partners">
            <option value="" disabled selected>Selectionne une membre...</option>            
            <?php foreach($partners as $partner) {
                if(userHasProducts($partner[0])){
                    if(!empty($partner[0]) && !empty($partner[1]) && $partner[1] != "" && $partner[1] != null && strlen($partner[1]) > 0)?>
                        <option value="<?php echo $partner[0] ?>"><?php echo $partner[1] ?></option>
                <?php } ?>
            <?php } ?>
        </select>
        <div class="delete-actions">
            <div class="confirm btn">Confirmer</div>
            <div class="cancel">Annuler</div>
        </div>
    </div>
    <div class="confirm-sell hidden">
        <p>À qui l'as tu vendu ?</p>
        <select name="sell-partners" id="sell-partners">
            <option value="" disabled selected>Selectionne une membre...</option>            
            <?php foreach($partners as $partner) { 
                if(!empty($partner[0]) && !empty($partner[1]) && $partner[1] != "" && $partner[1] != null && strlen($partner[1]) > 0)?>
                <option value="<?php echo $partner[0] ?>"><?php echo $partner[1] ?></option>
            <?php } ?>
        </select>
        <div class="delete-actions">
            <div class="confirm btn" onclick="sendSellConfirmation(<?php echo $produit?>, this)">Confirmer</div>
            <div class="cancel">Annuler</div>
        </div>
    </div>
    <div class="confirm-swap-2 hidden">
        <p>Contre quel article l'as tu échangé ?</p>
        <select name="swap-product" id="swap-product">
            <option value="" disabled selected>Selectionne un produit...</option>           
        </select>
        <div class="delete-actions">
            <div class="confirm btn" onclick="sendSwapConfirmation(<?php echo $produit?>, this)">Confirmer</div>
            <div class="cancel">Annuler</div>
        </div>
    </div>
</div>

