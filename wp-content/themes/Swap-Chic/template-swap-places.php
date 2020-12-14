<?php
    /*
    Template Name: Swap places
    Template Post Type: page
    */
	get_header();

$scope = getScope($_GET);

$lowest_scope_level = getScopeFormat($scope["scope"]);
set_query_var('scope', $scope);
set_query_var('scope_string', getScopeString($_GET));

$current_user_id = get_current_user_id();

?>


<div class="top">
    <h2 class="h2">Tu recherches une swap-place dans <span class="scope-toggle"><span class="scope"><?php echo $scope?></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span></h2>
    
    <div class="alert-notice">
        <a href="">Navigues sur la map, clic sur l’une d’entres elles et découvres le lieux.</br>la remise en main propre reste le moyen le plus écologique pour éviter les envois de colis et faire de belles rencontres, alors prêtes à te lancer ?</a>
    </div>
	<?php get_template_part( 'partials/form/scope', 'change'); ?>
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
    $product_nbr = 0;
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
        //'posts_per_page' => 150,
        'nopaging' => true
		
    );
    $swapplaces_query = new WP_Query( $args );
	
	if ( $swapplaces_query->have_posts() ) {
        while ( $swapplaces_query->have_posts() ) {
            $swapplaces_query->the_post();
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
	
	if(!empty($swapplaces['scope'])) {
        $postlist["featured"]["map"] = $swapplaces['scope'];
    }
    if(!empty($swapplaces['more'])) {
        array_unshift($postlist["more"], array('map', $swapplaces['more']));
    } 
    if(!empty($swapplaces['even_more'])) {
        array_unshift($postlist["even_more"], array('map', $swapplaces['even_more']));
    }
     
	displayPosts(sortPosts($postlist, 'distance'));
   ?>
</div>

<?php
    get_footer();
?>