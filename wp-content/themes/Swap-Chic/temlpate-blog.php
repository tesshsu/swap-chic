<?php
/*
Template Name: Blog
Template Post Type: page
*/

    get_header(); 

?>
<p class="h2">Nos articles</p>
<?php 
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : '1';
    $args = array (
       'post_type' => 'post',
        'post_status' => 'publish',
		'cat'         => 18,
		'nopaging'    => false,
        'paged'       => $paged,
		'posts_per_page' => 2,
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => false
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