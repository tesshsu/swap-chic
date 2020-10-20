<?php
    /*
    Template Name: Swap places
    Template Post Type: page
    */
    $scope = getScope($_GET);

	//$current_user_id = get_current_user_id();

	//$lowest_scope_level = getScopeFormat($scope["scope"]);

	//set_query_var('scope', $scope);

	//set_query_var('scope_string', getScopeString($_GET));
}
    get_header(); 
?>

<div class="top" style="margin-top: 120px;>
	 <h2 class="h2">Swap places</h2>
</div>
<div id="thread">
    <?php
	     $postlist = array(
			"featured" => array(
				"cdc" => null, // Featured product
				"popular" => null, // Most liked product
				"map" => null // swap places to display
			),
			"scope" => array(),
			"more" => array(),
			"even_more" => array()
		);
		
		$posts = array();
		$swapplaces = array(
			"scope" => array(),
			"more" => array(),
			"even_more" => array()
		);
		
		$args = array (
			'post_type' => 'swapplaces',
			'post_status' => 'publish',
			'orderby' => 'date',
			'order' => 'DESC',
			'author__not_in' => array($current_user_id),
			'posts_per_page' => 400,
			'nopaging' => false
			
		);
		$the_query = new WP_Query( $args );
		
		if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $post_id = get_the_id();
            $post_type = get_post_type();
			$post_scope = getPostScope($post_id, $post_type, $scope);
            if($post_scope != false) {
                    array_push($postlist[$post_scope], array($post_type, $post_id));
                    if($post_type == 'swapplaces') {
                        array_push($swapplaces[$post_scope], $post_id);
                    }
                }
        }
    }
    wp_reset_postdata();
	
	if(!empty($swapplaces['scope'])) {
        $postlist["featured"]["map"] = $swapplaces['scope'];
    }
    if(!empty($swapplaces['more'])) {
		array_unshift($postlist["more"], array('map', $swapplaces['more']));
    } 
    if(!empty($swapplaces['even_more'])) {		
		array_unshift($postlist["even_more"], array('map', $swapplaces['even_more']));
    }

    // Then we sort and finally display the postlist
    displayPosts(sortPosts($postlist, 'distance'));
	?>
</div>

<?php
    get_footer();
?>