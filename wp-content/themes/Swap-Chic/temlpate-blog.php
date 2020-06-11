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
	$totalpost = $the_query->found_posts; 

    if ( $the_query->have_posts() ) {
		if( $totalpost <= 2){
			foreach($the_query->posts as $post) {
				get_template_part( 'partials/content/content', 'posts' );
			}
		}else{
			foreach($the_query->posts as $post) {
				get_template_part( 'partials/content/content', 'posts' );
			}
		    next_post_link();
			/*the_posts_pagination( array(
				'mid_size'  => 2,
				'prev_text' => __( 'Back', 'textdomain' ),
				'next_text' => __( 'Onward', 'textdomain' ),
			) );*/
			
		}
    } else {
		// no posts found
		echo '<h1 class="page-title screen-reader-text">No Posts Found</h1>';
    }
    wp_reset_postdata();

    get_footer();
?>