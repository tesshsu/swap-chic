<?php 
    $post_id = get_query_var('post_id'); 
    $post_type = get_query_var('post_type'); 
    if($post_type == 'comments') {
        $comment_id = get_query_var('comment_id'); 
    }
?>

<div class="comment-thread-wrapper" data-post="<?php echo $post_id ?>" <?php if(isset($comment_id)) { ?> data-comment="<?php echo $comment_id ?>" <?php } ?> style="display:none">
    <?php if(is_user_logged_in()) {?>
        <form>
            <textarea  placeholder="Votre commentaire..."></textarea>
            <?php if(isset($comment_id)) { ?>
                <div class="btn" onclick="answerComment(<?php echo '\''.$post_type.'\', \''.$post_id.'\', \''.$comment_id.'\''?>, this)">Commenter</div>
            <?php } else { ?>
                <div class="btn" onclick="comment(<?php echo '\''.$post_type.'\', \''.$post_id.'\''?>, this)">Commenter</div>
            <?php } ?>
        </form>
    <?php } ?>
    <div class="comment-thread">

    </div>
</div>