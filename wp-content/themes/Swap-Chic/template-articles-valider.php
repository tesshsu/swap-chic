<?php
/*
Template Name: Articles à valider
Template Post Type: page
*/

    // Restrict acces to admin
    if(!is_user_logged_in()) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/');
        exit();
    } else{
        $user = wp_get_current_user();
        if(!in_array('administrator', $user->roles)) {
            header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
            exit();
        }
    }

    get_header(); 
?>

<p class="h2">Artilcles en attente de validation</p>
<div id="pending">
    <?php 
        $args = array (
            'post_type' => 'produits',
            'post_status' => 'draft',
            'orderby' => 'modified',
            'order' => 'DESC',
            'nopaging' => true
        );
        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) {
            foreach($the_query->posts as $post) { 
                $post_id = $post->ID;
                // Last_check is registered as an american format date so we turn it to european format
                $last_checked = str_replace('/', '-', get_field('last_checked', $post_id));
                $last_modified = get_the_modified_date('d-m-Y H:i:s', $post_id);
                $last_checked_date = date('d-m-Y H:i:s', strtotime($last_checked));
                $last_modified_date = date('d-m-Y H:i:s', strtotime($last_modified));

                if($last_checked != null) {
                    if($last_checked_date < $last_modified_date) {
                        // If the product was modified after the last check
                        set_query_var( 'post', $post_id );
                        get_template_part( 'partials/content/content', 'produitsavailder' );
                    }
                } else {
                    set_query_var( 'post', $post_id );
                    get_template_part( 'partials/content/content', 'produitsavailder' );
                }
            }
        } else {
            print 'Aucun article à valider...';
        }
    ?>
</div>


<?php 
    get_footer();
?>