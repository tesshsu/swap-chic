<?php 
    $comments_array = get_query_var('comments_array'); 
    set_query_var( 'post', $comment->comment_post_ID );

    foreach($comments_array as $comment) {
        $is_liked = isCommentLiked($post_id);
        $user = get_userdata($comment->user_id); ?>
        <div data-type="comment" data-id="<?php echo $comment->comment_ID ?>" class="comment<?php if($is_liked) echo ' liked' ?>">
            <div class="user">
                <img src="<?php echo get_field('photo_profil', 'user_'.$user->ID) ?>" alt="">
                <p><a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user->ID)) ?>"><?php echo ucfirst($user->data->display_name) ?></a></p>
            </div>
            <p class="content">
                <?php echo $comment->comment_content ?>
            </p>
            <div class="social">
                <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
                <div class="likes" onclick="likeComment(<?php echo '\''.$comment->comment_post_ID.'\', \''.$comment->comment_ID.'\'' ?>, this)">
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
        </div> <?php
    }
?>
