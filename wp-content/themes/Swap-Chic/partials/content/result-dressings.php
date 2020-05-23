<?php 
    $dressing = get_query_var('dressing');
    $user_id = get_field('proprietaire', $dressing)['ID'];
    $user = get_userdata($user_id);
    $products = get_field('produits', $dressing);
    $is_liked = isPostLiked($dressing);
?>

<div data-id="<?php echo $dressing ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $dressing ); ?>" data-type="dressing" class="dressing<?php if($is_liked) echo ' liked' ?>">
    <div class="user">
        <img src="<?php echo get_field('photo_profil', 'user_'.$user_id) ?>" alt="">
        <p><a href="<?php echo get_permalink($dressing) ?>"><?php echo ucfirst($user->data->display_name) ?></a>, <?php echo get_field('ville', 'user_'.$user_id) ?></p>
    </div>
    <div class="dressing-products">
    <?php 
        if(count($products) > 6) {
            $i = 0;
            foreach($products as $product) {
                if($i < 5) { ?>
                    <a href="<?php echo get_permalink($product['produit']); ?>">
                        <img src="<?php echo get_field('images', $product['produit'])[0] ?>" alt="">
                    </a> <?php 
                    $i++;
                }
            } ?>
            <a href="<?php echo get_permalink($dressing); ?>">Voir son dressing complet</a><?php
        } else {
            foreach($products as $product) { ?>
                <a href="<?php echo get_permalink($product['produit']); ?>">
                    <img src="<?php echo get_field('images', $product['produit'])[0] ?>" alt="">
                </a> <?php 
            }
        } ?>
    </div>
    <div class="social">
        <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
        <div class="likes" onclick="like(<?php echo '\'dressings\', \''.$post_id.'\'' ?>, this)">
        <?php if(!$is_liked) { ?>
            <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
        <? } else { ?>
            <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
        <?php } ?>
            <span><?php echo getLikesNumber($dressing) ?></span>
        </div>
        <div class="comments" onclick="getComments(<?php echo '\'dressings\', \''.$dressing.'\'' ?>, this)">
            <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
            <span><?php echo getCommentsNumber($dressing) ?></span>
        </div>
        <div class="share">
            <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg'?>" alt="">
            <span>Partager</span>
        </div>
        <?php 
            set_query_var('post_type', 'dressings');
            set_query_var('post_id', $dressing);
            get_template_part( 'partials/content/content', 'commentthread' ); 
        ?>
    </div>
</div>