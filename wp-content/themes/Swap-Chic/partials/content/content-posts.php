<?php
    $post_id = get_query_var('post');
?>

<div data-id="<?php echo $post_id ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $post_id ); ?>" data-type="post" class="post<?php if(has_post_thumbnail($post_id)) echo ' has_thumbnail'?><?php if($is_liked) echo ' liked' ?>">
    <?php if(has_post_thumbnail($post_id)) { ?>
        <img src="<?php echo get_the_post_thumbnail_url($post_id) ?>" alt="">
    <?php } ?>
    <div class="text">
        <p class="post-title h1"><?php echo get_the_title($post_id) ?></p>
        <p class="post-excerpt"><?php echo get_the_excerpt($post_id) ?></p>
    </div>
    <div class="social">
            <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
            <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'like('.'\'posts\', \''.$post_id.'\''.', this)'?>">
                <?php if(!$is_liked) { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
                <? } else { ?>
                    <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
                <?php } ?>
                <span><?php echo getLikesNumber($post_id) ?></span>
            </div>
            <div class="share">
                <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg'?>" alt="">
                <span></span>
                <div class="addtoany-wrapper">
                    <div class="a2a_kit a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo get_permalink($post_id) ?>" data-a2a-title="<?php echo get_the_title($post_id) ?>">
                        <a class="a2a_button_facebook"></a>

                        <a class="a2a_button_whatsapp"></a>
						
						<a class="a2a_button_facebook_messenger"></a>
                        
                        <a class="a2a_button_email"></a>
						
						<a class="a2a_button_twitter"></a>

                        <a class="a2a_button_pinterest"></a>
                    </div>
                </div>
            </div>
            <?php  
                set_query_var('post_type', 'posts');
                set_query_var('post_id', $post_id);
                get_template_part( 'partials/content/content', 'commentthread' ); 
            ?>
    </div>
</div>