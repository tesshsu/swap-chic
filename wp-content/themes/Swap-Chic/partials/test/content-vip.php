<?php 
    $user_id = get_query_var('user');
    $user = get_userdata($user_id);
    $dressing = get_field('dressing', 'user_'.$user_id);
    $products = array();
    $products_raw = get_field('produits', $dressing);
    foreach($products_raw as $product_raw) {
        if(get_post_status($product_raw) == 'publish'){
            $products[] = $product_raw;        
        }
    }
    $url = get_field('lien _insta', 'user_'.$user_id);
    $is_liked = isPostLiked($post_id);
?>

<div data-id="<?php echo $dressing ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $dressing ); ?>" data-type="dressing" class="vip dressing<?php if($is_liked) echo ' liked' ?>">
    <p>Elle a adopté Swap-Chic</p>
    <div class="user">
        <img src="<?php echo get_field('photo_profil', 'user_'.$user_id) ?>" alt="">
        <p><a href="<?php echo get_permalink($dressing) ?>"><?php echo ucfirst($user->data->display_name) ?></a>, <?php echo get_field('ville', 'user_'.$user_id) ?>, <span class='activity'><?php echo get_field('occupation', 'user_'.$user_id) ?></em></p>
    </div>
    <div class="vip-bottom">
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
            } 
        ?>
        </div>
        <?php if(!empty($url)) {?>
            <div class="reseaux-sociaux-vip">
            <a href="<?php echo $url ?>" class="insta">
                <img src="<?php echo get_template_directory_uri().'/assets/images/insta.svg'; ?>" alt="">
                @<?php 
                    $url_array = explode('/', $url);
                    if(!empty($url_array[count($url_array) - 1])) {
                        echo($url_array[count($url_array) - 1]);
                    } else {
                        echo($url_array[count($url_array) - 2]);
                    }
                    ?>
            </a>
            </div>
        <?php }?>
        <?php 
            set_query_var('post_type', 'dressings');
            set_query_var('post_id', $post_id);
            set_query_var('is_liked', $is_liked);
            get_template_part( 'partials/test/social', 'post' );
        ?>
    </div>
</div>