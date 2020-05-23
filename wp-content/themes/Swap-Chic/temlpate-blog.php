<?php
/*
Template Name: Blog
Template Post Type: page
*/

    get_header(); 

?>
<p class="h2">Nos articles</p>
<?php 
    $args = array (
        'post_type' => 'post',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        foreach($the_query->posts as $post) {
            set_query_var( 'post', $post->ID );
            get_template_part( 'partials/content/content', 'posts' );
        }
    }
    wp_reset_postdata();

    get_footer();
?>