<?php
/**
 * The template for displaying all single posts
**/

get_header(); 
global $post;
$post_id = get_the_id(); 
$content = apply_filters('the_content', $post->post_content); 
$is_liked = isPostLiked($post_id);
?>

<div class="post single-post">
<?php 
    print '<p class="h1">'.apply_filters('the_title', $post->post_title).'</p>';
    print $content;
    if(get_post_status($post_id) == 'publish' && is_user_logged_in()) { ?>
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
            <div class="comments" onclick="getComments(<?php echo '\'posts\', \''.$post_id.'\'' ?>, this)">
                <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
                <span><?php echo getCommentsNumber($post_id) ?></span>
            </div>
            <div class="share">
                <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg';?>" alt="">
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
                set_query_var('post_type', 'posts');
                set_query_var('post_id', $post_id);
                get_template_part( 'partials/content/content', 'commentthread' ); 
            ?>
        </div>
    <?php } ?>
</div>

<?php
get_footer();
?>