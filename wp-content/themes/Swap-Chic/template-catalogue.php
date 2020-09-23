<?php
/*
Template Name: Catalogue
Template Post Type: page
*/

get_header();

$scope_location = getScope($_GET);

$current_user_id = get_current_user_id();
?>

<div class="top">
    <div class="catalogue-toggle-wrapper">
        <div id="catalogue-produits" class="bold"><a href="#produits">Produits</a></div>
		<div id="catalogue-membres"><a href="#membres">Membres</a></div>
        <div id="catalogue-swap-places"><a href="#swapplaces">Swap-placess</a></div>
    </div>
    <h2 class="h2">Les <span id="data-type">produits</span> à <span class="scope-toggle"><span class="scope"><?php echo $scope_location?></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span></h2>
    <?php get_template_part( 'partials/form/scope', 'change'); ?>
   <div class="filter-sort">
        <?php 
            get_template_part( 'partials/content/content', 'filter');
        ?>
    </div>

</div>

<div id="produits">
    <?php
    wp_reset_query();
    $produits = array();
    $args = array (
        'post_type' => 'produits',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true,
        'author__not_in' => array($current_user_id),
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $post_id = get_the_id();
            // Fill array with the ids of the posts in the scope of the current location
            $post_scope = getPostScope($post_id, 'produits', $scope_location);
            if($post_scope == 'scope') {
                array_push($produits, $post_id);
            }
        }
    }  
    
    if(!empty($produits)) {
        foreach($produits as $produit){
            set_query_var( 'post', $produit );
            get_template_part( 'partials/content/content', 'produits' );
        }
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'produits');
        get_template_part( 'partials/content/content', 'seemore' );
    } else {
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'produits');
        get_template_part( 'partials/content/content', 'noproducts' );
    }

    wp_reset_postdata();
    ?>

</div>

<div id="membres" style="display:none">
    <?php 
    wp_reset_query();
    $membres = array();
    $args = array(
        'role' => 'contributor',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true,
	    'exclude' => array( '-'.$current_user_id )
    );
    $user_query = new WP_User_Query( $args );

    if ( ! empty( $user_query->results ) ) {
        foreach ( $user_query->results as $user ) {
            $user_id = $user->ID;
           if(userHasProducts($user_id)) {
                $user_scope = getUserScope($user_id, $scope_location);
                if($user_scope == 'scope') {
                    array_push($membres, $user_id);
                }
            }
        }
    }

    if(!empty($membres)) {
        foreach($membres as $membre){
            set_query_var( 'user', $membre );
            get_template_part( 'partials/content/content', 'dressings' );
        }
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'membres');
        if(getLowestScopeLevel($_GET) != 'region') {
            get_template_part( 'partials/content/content', 'seemore' );
        }
    } else {
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'membres');
        get_template_part( 'partials/content/content', 'noproducts' );
    }
    ?>
</div>

<div id="swap-places" style="display:none">
    <?php 
    wp_reset_query();
    $swapplaces = array();
    $args = array (
        'post_type' => 'swapplaces',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $post_id = get_the_id();
            if(get_field('images', $post_id).length > 1) {
                print_r($post_id.' '.get_the_title($post_id));
                echo "\n";
            }
            // Fill array with the ids of the posts in the scope of the current location
            $post_scope = getPostScope($post_id, 'swapplaces', $scope_location);
            if($post_scope == 'scope') {
                array_push($swapplaces, $post_id);
            }
        }
    }
    
    if(!empty($swapplaces)) {
        foreach($swapplaces as $swapplace){
            set_query_var( 'post', $swapplace );
            get_template_part( 'partials/content/content', 'swapplaces' );
        }
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'swap-places');
        get_template_part( 'partials/content/content', 'seemore' );
    } else {
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'swap-places');
        get_template_part( 'partials/content/content', 'noproducts' );
    }

    wp_reset_postdata();
    ?>
</div>
<?php 
    get_footer(); 
?>