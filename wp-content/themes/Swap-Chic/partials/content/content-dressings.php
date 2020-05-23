<?php 
    $user_id = get_query_var('user');
    $user = get_userdata($user_id);
    $dressing = get_field('dressing', 'user_'.$user_id);
    $is_liked = isPostLiked($post_id);
    $products = array();
    $products_raw = get_field('produits', $dressing);
    foreach($products_raw as $product_raw) {
        if(get_post_status($product_raw) == 'publish'){
            $products[] = $product_raw;        
        }
    }
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
                    <a href="<?php echo get_permalink($product); ?>">
                    <?php echo get_the_post_thumbnail($product) ?>
                    </a> <?php 
                    $i++;
                }
            } ?>
            <a href="<?php echo get_permalink($dressing); ?>">Voir son dressing complet</a><?php
        } else {
            foreach($products as $product) { ?>
                <a href="<?php echo get_permalink($product); ?>">
                <?php echo get_the_post_thumbnail($product) ?>
                </a> <?php 
            }
        } ?>
    </div>
    <div class="social">
        <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
        <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'like('.'\'dressings\', \''.$post_id.'\''.', this)'?>">
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
            set_query_var('post_type', 'dressings');
            set_query_var('post_id', $dressing);
            get_template_part( 'partials/content/content', 'commentthread' ); 
        ?>
    </div>
</div>