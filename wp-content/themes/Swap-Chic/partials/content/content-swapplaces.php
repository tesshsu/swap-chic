<?php 
    $post_id = get_query_var('post'); 
    $images = get_field('images', $post_id);
	$instagram = get_field('instagram', $post_id);
	if( $instagram ) {
		$instagram_url = $instagram['url'];
		$instagram_title = $instagram['title'];
		$instagram_target = $instagram['target'] ? $link['target'] : '_self';
    }
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
		<div class="social">
            <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'like('.'\'swapplaces\', \''.$post_id.'\''.', this)'?>">
                <?php if(!$is_liked) { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
                <? } else { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
                <?php } ?>
                <span><?php echo getLikesNumber($post_id) ?></span>
            </div>
            <div class="comments" onclick="getComments(<?php echo '\'swapplaces\', \''.$post_id.'\'' ?>, this)">
                <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
                <span><?php echo getCommentsNumber($post_id) ?></span>
            </div>
			<?php if($instagram) { ?>
			<div class="instagram_block">
			    <a href="<?php echo esc_url( $instagram ); ?>" target="<?php echo esc_attr( $instagram ); ?>">
                  <img src="<?php echo get_template_directory_uri().'/assets/images/instagram.svg'?>" alt="">
				</a>
            </div>
			<?php } ?>
            <div class="share">
                <img src="<?php echo get_template_directory_uri().'/assets/images/share_social.svg'?>" alt="">
                <span></span>
                <div class="addtoany-wrapper">
                    <div class="a2a_kit a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo get_permalink($post_id) ?>" data-a2a-title="<?php echo get_the_title($post_id) ?>">
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
                set_query_var('post_type', 'swapplaces');
                set_query_var('post_id', $post_id);
                get_template_part( 'partials/content/content', 'commentthread' ); 
            ?>
        </div>
    </div>
</div>