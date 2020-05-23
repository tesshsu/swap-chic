<?php
$comment = get_query_var('comment');
$is_liked = get_query_var('is_liked');
?>
<div class="social">
    <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
    <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'likeComment(\''.$comment->comment_post_ID.'\', '.'\''.$comment->comment_ID.'\''.', this)'?>">
        <?php if(!$is_liked) { ?>
            <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
        <? } else { ?>
            <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
        <?php } ?>
        <span><?php echo getCommentLikesNumber($comment->comment_ID) ?></span>
    </div>
    <div class="comments" onclick="getCommentAnswers(<?php echo '\''.$comment->comment_post_ID.'\', \''.$comment->comment_ID.'\'' ?>, this)">
        <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
        <span><?php echo getCommentChildNumber($comment->comment_ID) ?></span>
    </div>
    <?php 
        set_query_var('post_type', 'comments');
        set_query_var('post_id', $comment->comment_post_ID);
        set_query_var('comment_id', $comment->comment_ID);
        get_template_part( 'partials/content/content', 'commentthread' ); 
    ?>
</div>