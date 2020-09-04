<?php
/**
 * The template for displaying single discussion
**/

    get_header(); 

    if(!is_user_logged_in()) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
        exit();
    }


    $post_id = get_the_id(); 
    $current_user_id = get_current_user_id();
    
    // We check that the current user is part of this discussion
    if(get_field('utilisateur_1', $discussion) == $current_user_id) {
        $partner_id = get_field('utilisateur_2', $discussion);
    } elseif(get_field('utilisateur_2', $discussion) == $current_user_id) {
        $partner_id = get_field('utilisateur_1', $discussion);
    } else {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/messagerie');
        exit();
    }

    // We delete the new message notification if it exists
	$notifs = get_field('notifications', 'user_'.$current_user_id);
	foreach($notifs as $key => $notif) {
		if($notif[notification] == ucfirst(get_userdata($partner_id)->data->display_name).' t\'a envoyé un message.') {
            delete_row('field_5e010aea5c731', $key + 1, 'user_'.$current_user_id);
            break;
		}
    }
    
    $is_online = false;
    if(get_user_meta($partner_id, 'asdb-loggedin')[0] == 1) {
        $is_online = true;
    }
    $partner = get_userdata($partner_id);

?>

<div class="discussion-title <?php if($is_online) echo 'online' ?>">
    <a href="javascript:history.back()" class="discussion-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <p class="h1">
        <img src="<?php echo get_field('photo_profil', 'user_'.$partner_id) ?>" alt="">
        <a href="<?php echo get_permalink(get_field('dressing', 'user_'.$partner_id)) ?>"><?php echo ucfirst($partner->data->display_name) ?></a>
    </p>
</div>

<div id="page-wrap">
    <div id="chat-wrap">
        <span id="chat-area"></span>
    </div>
    <form id="send-message-area">
        <div id="send-more-toggle"><a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit' ?>"><img src="<?php echo get_template_directory_uri().'/assets/images/plus.svg'; ?>" alt=""></a></div>
        <textarea id="sendie" placeholder="Votre message..." ></textarea>
        <div id="send"><img src="<?php echo get_template_directory_uri().'/assets/images/send.svg'; ?>" alt=""></div>
    </form>
</div>


<?php
get_footer();
?>

<script>
    var ids = [<?php echo $current_user_id;?>, <?php echo $partner_id;?>];
    var post_id = <?php echo $post_id;?>;
    var post_url = "<?php echo get_the_permalink($post_id); ?>";

    var chat =  new Chat();
     jQuery(document).ready(function() {
        window.scrollTo(0,document.body.scrollHeight);
        chat.getState(ids, post_id);
        chat.update(ids, post_id);
        setInterval('chat.update(ids, post_id)', 1000);
        jQuery('#send').click(function() {
            var text = jQuery('#sendie').val();
            if(text.length > 0) {
                jQuery('#sendie').css('height', 'calc(1.25em + 20px)');
                
                chat.send(text, ids, post_id, post_url);  
            }
            jQuery('#sendie').val("");
        });
        jQuery('.send-product').click(function() {
            var text = 'POST_PRODUCT '+jQuery(this).find('input').val();
            chat.send(text, ids, post_id, post_url);
        });
        jQuery('.send-swapplace').click(function() {
            var text = 'POST_SWAPPLACE '+jQuery(this).find('input').val();
            chat.send(text, ids, post_id, post_url);
        });
    });
</script>