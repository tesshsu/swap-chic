<?php 
    $post_id = get_query_var('post'); 
    $images = get_field('images', $post_id);
    $is_liked = isPostLiked($post_id);
?>

<div data-id="<?php echo $post_id ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $post_id ); ?>" data-type="swapplace" class="swapplace<?php if($is_liked) echo ' liked' ?>">
    <div class="swapplace-carousel">
        <img src="<?php echo $images[0] ?>" alt="">
    </div>
    <div class="swapplace-map"></div>
    <div class="infos-wrapper">
        <div class="title-action">
            <h3 class="h1"><?php echo get_the_title($post_id) ?></h3>
            <div class="toggle">
                <div class="picture-toggle"><img src="<?php echo get_template_directory_uri().'/assets/images/pics.svg'?>" alt=""></div>
                <div class="map-toggle"><img src="<?php echo get_template_directory_uri().'/assets/images/map.svg'?>" alt=""></div>
            </div>
        </div>
        <p class="adresse"><?php echo get_field('adresse', $post_id); ?></p>
        <?php 
            set_query_var('post_type', 'swapplaces');
            set_query_var('post_id', $post_id);
            set_query_var('is_liked', $is_liked);
            get_template_part( 'partials/test/social', 'post' );
        ?>
    </div>
</div>