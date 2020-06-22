<?php
/**
 * The template for displaying single discussion
**/

    get_header(); 

    if(!is_user_logged_in()) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
        exit();
    }


    $post_id = get_the_id(); 
    $current_user_id = get_current_user_id();
    
    // We check that the current user is part of this discussion
    if(get_field('utilisateur_1', $discussion) == $current_user_id) {
        $partner_id = get_field('utilisateur_2', $discussion);
    } elseif(get_field('utilisateur_2', $discussion) == $current_user_id) {
        $partner_id = get_field('utilisateur_1', $discussion);
    } else {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/messagerie');
        exit();
    }

    // We delete the new message notification if it exists
	$notifs = get_field('notifications', 'user_'.$current_user_id);
	foreach($notifs as $key => $notif) {
		if($notif[notification] == ucfirst(get_userdata($partner_id)->data->display_name).' t\'a envoyé un message.') {
            delete_row('field_5e010aea5c731', $key + 1, 'user_'.$current_user_id);
            break;
		}
    }
    
    $is_online = false;
    if(get_user_meta($partner_id, 'asdb-loggedin')[0] == 1) {
        $is_online = true;
    }
    $partner = get_userdata($partner_id);


?>
<div class="discussion-title <?php if($is_online) echo 'online' ?>">
    <a href="javascript:history.back()" class="discussion-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <p class="h1">
        <img src="<?php echo get_field('photo_profil', 'user_'.$partner_id) ?>" alt="">
        <a href="<?php echo get_permalink(get_field('dressing', 'user_'.$partner_id)) ?>"><?php echo ucfirst($partner->data->display_name) ?></a>
    </p>
</div>

<div id="page-wrap">
    <div id="chat-wrap">
       <?php //if (function_exists('wise_chat')) { wise_chat(); } ?>
    </div>
    <div id="send-more" style="display:none">
        <div class="produits">
            <p class="h1">Produits</p>
            <div class="partner-dressing drawer closed">
                <p class="h2">Dressing de <?php echo ucfirst($partner->data->display_name) ?></p>
                <?php 
                    $produits = get_field('produits', get_field('dressing', 'user_'.$partner_id));
                    $partner_products = 0;
                    $has_product = false;
                    foreach($produits as $produit) {
                        if( get_post_status($produit) == 'publish') {
                            $partner_products ++;
                            $has_product = true; ?>
                            <div class="produit-min" data-id="<?php echo $produit ?>">
                                <?php echo get_the_post_thumbnail($produit) ?>
                                <div class="infos">
                                    <p><?php echo generateProductTitle($produit) ?></p>
                                    <b><?php 
                                        $action = get_field('action', $produit);
                                        if( $action[0] == 'À vendre' && count($action) == 1) {
                                            echo 'À vendre : '.get_field('prix', $produit).'€'; 
                                        } elseif($action[1]) {
                                            echo 'À swaper ou à vendre : '.get_field('prix', $produit).'€';
                                        } else {
                                            echo 'À swaper';
                                        }
                                    ?></b>
                                </div>
                                <div class="send-product btn">
                                    Envoyer
                                    <input type="hidden" class="post-chat" value="<?php echo productToChat($produit) ?>">
                                </div>
                            </div>
                        <? }
                    }
                ?>
                <p class="number"><?php echo $partner_products; ?></p>
                <div class="expand"><img src="<?php if($partner_products == 0) { echo get_template_directory_uri().'/assets/images/lock.svg'; } else {echo get_template_directory_uri().'/assets/images/arrowbot-white.svg'; } ?>" alt=""></div>
            </div>            
            <div class="user-dressing drawer closed">
               <p class="h2">Votre dressing</p>
                <?php 
                    $produits = get_field('produits', get_field('dressing', 'user_'.$current_user_id));
                    $user_products = 0;
                    foreach($produits as $produit) {
                        if( get_post_status($produit) == 'publish') {
                            $user_products ++; ?>
                            <div class="produit-min" data-id="<?php echo $produit ?>">
                                <?php echo get_the_post_thumbnail($produit) ?>
                                <div class="infos">
                                    <p><?php echo generateProductTitle($produit) ?></p>
                                    <b><?php 
                                        $action = get_field('action', $produit);
                                        if( $action[0] == 'À vendre' && count($action) == 1) {
                                            echo 'À vendre : '.get_field('prix', $produit).'€'; 
                                        } elseif($action[1]) {
                                            echo 'À swaper ou à vendre : '.get_field('prix', $produit).'€';
                                        } else {
                                            echo 'À swaper';
                                        }
                                    ?></b>
                                </div>
                                <div class="send-product btn">
                                    Envoyer
                                    <input type="hidden" class="post-chat" value="<?php echo productToChat($produit) ?>">
                                </div>
                            </div>
                        <? }
                    }
                ?>
                <p class="number"><?php echo $user_products; ?></p>
                <div class="expand"><img src="<?php if($user_products == 0) { echo get_template_directory_uri().'/assets/images/lock.svg'; } else {echo get_template_directory_uri().'/assets/images/arrowbot-white.svg'; } ?>" alt=""></div>
            </div>
        </div>
        <div class="swap-places">
            <p class="h1">Swap-places</p>
            <div class="partner-swap-places drawer closed">
                <p class="h2">Swap-places favorites de <?php echo ucfirst($partner->data->display_name) ?></p>
                <?php 
                    $swapplaces = get_field('swap-places', 'user_'.$partner_id);
                    $partner_sp = 0;
                    if(!empty($swapplaces)) {
                        foreach($swapplaces as $swapplace) {
                            $partner_sp ++; ?>
                            <div class="swapplace-min" data-id="<?php echo $swapplace ?>">
                                <img src="<?php echo get_field('images', $swapplace)[0] ?>'" alt="">
                                <p><?php echo get_the_title($swapplace) ?></p>
                                <div class="send-swapplace btn">
                                    Envoyer
                                    <input type="hidden" class="post-chat" value="<?php echo swapplaceToChat($swapplace) ?>">
                                </div>
                            </div>
                        <?php }
                    }
                ?>
                <p class="number"><?php echo $partner_sp; ?></p>
                <div class="expand"><img src="<?php if($partner_sp == 0) { echo get_template_directory_uri().'/assets/images/lock.svg'; } else {echo get_template_directory_uri().'/assets/images/arrowbot-white.svg'; } ?>" alt=""></div>
            </div>
            <div class="user-swap-places drawer closed">
                <p class="h2">Vos swap-places favorites</p>
                <?php   
                    $swapplaces = get_field('swap-places', 'user_'.$current_user_id);
                    $user_sp = 0;
                    if(!empty($swapplaces)) {
                        foreach($swapplaces as $swapplace) {
                            $user_sp ++; ?>
                            <div class="swapplace-min" data-id="<?php echo $swapplace ?>">
                                <img src="<?php echo get_field('images', $swapplace)[0] ?>'" alt="">
                                <p><?php echo get_the_title($swapplace) ?></p>
                                <div class="send-swapplace btn">
                                    Envoyer
                                    <input type="hidden" class="post-chat" value="<?php echo swapplaceToChat($swapplace) ?>">
                                </div>
                            </div>
                        <?php }
                    }
                ?>
                <p class="number"><?php echo $user_sp; ?></p>
                <div class="expand"><img src="<?php if($user_sp == 0) { echo get_template_directory_uri().'/assets/images/lock.svg'; } else {echo get_template_directory_uri().'/assets/images/arrowbot-white.svg'; } ?>" alt=""></div>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();
?>