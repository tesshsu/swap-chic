<?php
/**
 * The template for displaying single dressing
**/

get_header(); 

$post_id = get_the_id(); 
$current_user_id = get_current_user_id();
$user = get_field('proprietaire', $post_id);
$produits = get_field('produits', $post_id);
$produits_publish = 0;
$is_liked = isPostLiked($post_id);

if($user['ID'] == $current_user_id) {
    // If the current user is the owner of the dressing, we create a list of all the other users it currently have a discussion with.
    // Beacause theratically, a user must have been in contact with another through a discussion if they were to sell/buy or swap items
    // So to delete a product, we ask if it was sold or swapped and if so, we ask to whom, and that 'whom' must be one of the discussion partners
    $is_owner = true;
    $partners = array();

    $args = array(
        'post_type' => 'discussions',
        'nopaging' => true,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'utilisateur_1',
                'value'   => $current_user_id,
                'compare' => '='
            ),
            array(
                'key'     => 'utilisateur_2',
                'value'   => $current_user_id,
                'compare' => '='
            )
        ),
    );
    
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) { 
            $the_query->the_post();
            $post_id = get_the_id();
            if(get_field('utilisateur_1', $post_id) == $current_user_id) {
                $partner_id = get_field('utilisateur_2', $post_id);
            } else {
                $partner_id = get_field('utilisateur_1', $post_id);
            }
            array_push($partners, array($partner_id, ucfirst(get_userdata($partner_id)->data->display_name)));
        } 
    }
} else {
    $is_owner = false;
}
?>

<div class="dressing-single <?php if($is_owner) echo 'profile'?>" data-type="dressing" data-id="<?php echo $post_id ?>">
    <div class="dressing-title">
        <a href="javascript:history.back()" class="dressing-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    </div>
    <div class="pp">
        <img src="<?php echo get_field('photo_profil', 'user_'.$user['ID'])?>" alt="">
    </div>
    <h1 class="h1"><?php echo ucfirst($user['display_name']) ?></h1>
    <p class="location"><?php echo get_field('ville', 'user_'.$user['ID'])?></p>
    <div class="content">
        <?php if(!empty($produits)) {
            foreach($produits as $produit) { ?>
                <?php if( get_post_status($produit) == 'publish') {
                    $produits_publish ++;
                    $product_is_liked = isPostLiked($produit); ?>
                    <div class="produit-min <?php if(get_field('pending_deletion', $produit) == 1) echo 'pending-delete' ?>" data-id="<?php echo $produit ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $produit ); ?>" data-type="produit">
                        <?php echo get_the_post_thumbnail($produit) ?>
                        <p><?php echo get_the_title($produit) ?></p>
                        <div class="likes">
                            <?php if(!$product_is_liked) { ?>
                                <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
                            <? } else { ?>
                                <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
                            <?php } ?>                            
                            <span><?php echo getLikesNumber($produit) ?></span>
                        </div>
                        <?php if($is_owner) { ?>
                            <div class="product-actions">
                                <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/editer-produit/?produit='.$produit ?>" class="edit">Modifier</a>
                                <div class="delete">Supprimer</div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php 
                        if($is_owner) {    
                            set_query_var('partners', $partners);
                            set_query_var('product', $produit);
                            set_query_var('is_published', true);
                            get_template_part('partials/form/form', 'deleteproduct');
                        }
                    ?>
                <?php } elseif($is_owner && get_post_status($produit) == 'draft') { 
                    // Check that the tiltle of the product is correctly set
                    if(get_the_title($produit) != $title = generateProductTitle($produit)) {
                        $update_produit = array(
                            'ID'           => $produit,
                            'post_title'   => $title
                        );
                        wp_update_post( $update_produit );
                    } ?>
                    <div class="produit-min <?php if(get_field('pending_deletion', $produit) == 1) echo 'pending-delete' ?>" data-id="<?php echo $produit ?>">
                        <img src="<?php echo get_field('images', $produit)[0] ?>" alt="">
                        <p><?php echo get_the_title($produit) ?></p>
                        <div class="pending">
                            <span>En attente...</span>
                        </div>
                        <div class="product-actions">
                            <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/editer-produit/?produit='.$produit ?>" class="edit">Modifier</a>
                            <div class="delete">Supprimer</div>
                        </div>
                    </div>
                    <?php 
                        set_query_var('partners', $partners);
                        set_query_var('product', $produit);
                        set_query_var('is_published', false);
                        get_template_part('partials/form/form', 'deleteproduct');
                    ?>
            <?php } 
            }
            if($produits_publish == 0 && $is_owner == false ) { ?>
                <p>Aucun produit dans ce dressing pour le moment...</p>
            <?php }
        } else if($is_owner) { ?>
            <p>Aucun produit dans votre dressing pour le moment...</p>
            <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit/' ?>" style="margin: 0 auto" class="btn add-product">Ajouter un produit</a>
        <?php } else { ?>
            <p>Aucun produit dans ce dressing pour le moment...</p>
        <?php } ?>
    </div>
    <div class="bottom-dressing">
        <?php if(!is_user_logged_in()) {
            // Nothing
        } else if(!$is_owner) { ?>
            <div data-userid="<?php echo $user['ID'] ?>" class="openChat btn" onclick="openChat(<?php echo get_current_user_id().', '.$user['ID'] ?>)">Contacter<img src="<?php echo get_template_directory_uri().'/assets/images/chat-white.svg'; ?>" alt=""></div>
        <?php } else { ?>
            <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit' ?>" class="btn">Ajouter</a>
        <?php } ?>
        <div class="social">
            <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
            <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'like('.'\'dressings\', \''.$post_id.'\''.', this)'?>">
                <?php if(!$is_liked) { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
                <? } else { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
                <?php } ?>           
                <span><?php echo getLikesNumber($post_id) ?></span>
            </div>
            <div class="comments" onclick="getComments(<?php echo '\'dressings\', \''.$post_id.'\'' ?>, this)">
                <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
                <span><?php echo getCommentsNumber($post_id) ?></span>
            </div>
            <div class="share">
                <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg';?>" alt="">
                <span></span>
                <div class="addtoany-wrapper">
                    <div class="a2a_kit a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo get_permalink($dressing) ?>" data-a2a-title="<?php echo get_post_field( 'post_name', get_post($dressing)) ?>">
                        <a class="a2a_button_facebook"></a>
                        <a class="a2a_button_twitter"></a>
                        <a class="a2a_button_pinterest"></a>
                        <a class="a2a_button_email"></a>
                        <a class="a2a_button_whatsapp"></a>
                        <a class="a2a_button_facebook_messenger"></a>
                    </div>
                </div>
            </div>
            <?php  
                set_query_var('post_type', 'produits');
                set_query_var('post_id', $post_id);
                get_template_part( 'partials/content/content', 'commentthread' ); 
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
