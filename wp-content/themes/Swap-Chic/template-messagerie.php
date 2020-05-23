<?php
/*
Template Name: Messagerie
Template Post Type: page
*/

get_header(); 

if(is_user_logged_in()) {
    
    $current_user_id = get_current_user_id();

    $discussions = array();

    // We look for all the discussions the current user is part of
    $args = array(
        'post_type' => 'discussions',
        'orderby' => 'modified',
        'order' => 'DESC',
        'nopaging' => true,
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'utilisateur_1',
                'value'   => $current_user_id,
                'compare' => '='
            ),
            array(
                'key'     => 'utilisateur_2',
                'value'   => $current_user_id,
                'compare' => '='
            )
        ),
    );
    
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) { 
            $the_query->the_post();
            $post_id = get_the_id();
            if(strlen(get_field('discussion', $post_id)) > 0) {
                // If the discussion has content
                $discussions[] = $post_id;
            }
        } 
    }
    
    $notifs = get_field('notifications', 'user_'.$current_user_id);
?>

<div class="messagerie-toggle-wrapper">
    <div id="messagerie-discussions" class="bold">
        Discussions
        <span class="notifs notifs-msg"></span> 
    </div>
    <div id="messagerie-notifications">
        Notifications
        <?php 
            $notifs_count = getNotifNumber($current_user_id);
            if($notifs_count != 0) {
        ?>
            <span class="notifs"><?php echo $notifs_count ?></span> 
        <?php } ?>
    </div>
</div>

<div id="discussions">
    <?php
        if(count($discussions) > 0) { 
            foreach( $discussions as $discussion) {

                $discussion_text =  explode("\n", get_field('discussion', $discussion));

                if(get_field('utilisateur_1', $discussion) == $current_user_id) {
                    $partner_id = get_field('utilisateur_2', $discussion);
                } else {
                    $partner_id = get_field('utilisateur_1', $discussion);
                }

                $is_online = false;
                if(get_user_meta($partner_id, 'asdb-loggedin')[0] == 1) {
                    $is_online = true;
                }
                $is_discussion_read = isDiscussionRead($partner_id)?>

                <div data-id="<?php echo $discussion?>" data-userid="<?php echo $partner_id ?>" class="discussion <?php if(!$is_discussion_read){ echo 'unread'; } if($is_online){ echo 'online'; }?>" onclick="openChat(<?php echo get_current_user_id().', '.$partner_id ?>)">
                    <div class="user">
                        <img src="<?php echo get_field('photo_profil', 'user_'.$partner_id) ?>" alt="">
                        <p><a><?php echo ucfirst(get_userdata($partner_id)->data->display_name) ?></a></p>
                    </div>
                    <div class="last">
                        <?php 
                         // We display the last message of the discussion, 
                        echo $discussion_text[count($discussion_text) - 2] ?>
                        <div id="from">
                            <?php  
                                // We display the sender of the last message, 
                                $discussion_string = htmlentities($discussion_text[count($discussion_text) - 2]);
                                preg_match("/data-from='([0-9]*)'/", $discussion_string, $matches);
                                $last_sender_id = $matches[1];
                                if($last_sender_id != get_current_user_id()) {
                                    echo ucfirst(get_userdata($last_sender_id)->data->display_name).'&nbsp;:&nbsp;';
                                } else {
                                    echo 'Vous&nbsp;:&nbsp;';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            <?php }
        } else { ?>
            <div class="nodata">Aucune discussion démarrée</div>
    <?php }
    ?>
    <div data-link="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie/nouvelle-discussion/'?>" class="openChat btn">Nouvelle discussion<img src="<?php echo get_template_directory_uri().'/assets/images/chat-white.svg'; ?>" alt=""></div>
</div>
<div id="notifications" style="display:none">
    <?php if(!empty($notifs)) {
        foreach( $notifs as $notif) { ?>
            <div class="notif">
                <a data-href="<?php echo $notif["lien"]?>" onclick="deleteNotif(this, false)"><?php echo $notif["notification"]?></a>
                <img onclick="deleteNotif(this, true)" src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt="">
            </div>
        <?php }
        } else { ?>
        <div class="nodata">Aucune notification</div>
    <?php } ?>
</div>

<?php } else { ?>
    <div class="not-connected">
        <p>Connectez vous pour accéder à toutes les fonctionnalités de Swap-Chic.</p>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="btn">Connexion</a>
    </div>
<?php }
get_footer();