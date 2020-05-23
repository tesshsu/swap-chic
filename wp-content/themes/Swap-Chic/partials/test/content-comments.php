<?php 
    $comment = get_comment(get_query_var('comm')); 
    $user = get_userdata($comment->user_id);
    $is_liked = isCommentLiked($comment->comment_post_ID, $comment->comment_ID);
?>

<div data-posttype="<?php echo get_post_field( 'post_type', get_post($comment->comment_post_ID)) ?>" data-id="<?php echo $comment->comment_post_ID ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $comment->comment_post_ID ); ?>" class="comment inthread<?php if($is_liked) echo ' liked' ?>">
    <div class="user">
        <img src="<?php echo get_field('photo_profil', 'user_'.$user->ID) ?>" alt="">
        <p><a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user->ID)) ?>"><?php echo ucfirst($user->data->display_name) ?></a> a commenté sur <?php print getCommentPost($comment->comment_post_ID) ?></p>
    </div>
    <p class="content">
        <?php echo $comment->comment_content ?>
    </p>
    <?php 
        set_query_var('comment', $comment);
        set_query_var('is_liked', $is_liked);
        get_template_part( 'partials/test/social', 'comment' );
    ?>
</div>