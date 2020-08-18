<?php
/*
Template Name: Fil d'actualités
Template Post Type: page
*/

if(!is_user_logged_in()) {
    header('Location: https://'.$_SERVER['HTTP_HOST']);
    exit();
}

get_header();

$scope = getScope($_GET);
$current_user_id = get_current_user_id();
$lowest_scope_level = getScopeFormat($scope["scope"]);
set_query_var('scope', $scope);
set_query_var('scope_string', getScopeString($_GET));



// Display help bubbles if the cookies are not set
if(!isset($_COOKIE["hide-helps"]) || $_COOKIE["hide-helps"] != 1) {
    get_template_part( 'partials/content/content', 'helpgeo' );
    get_template_part( 'partials/content/content', 'helpfil' );
    get_template_part( 'partials/content/content', 'helpcatalogue' );
    get_template_part( 'partials/content/content', 'helpajoutproduit' );
    get_template_part( 'partials/content/content', 'helpmap' );
}

?>
<div id="topBanner" class="top">
	<a href="#">
      <img src="<?php echo get_template_directory_uri().'/assets/images/banners/pb4.jpg' ?>" alt="pub4">
    </a>
</div>
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
                $user_scope = getUserScope($user_id, $scope);
                if($user_scope == 'scope') {
                    array_push($membres, $user_id);
                }
            }
        }
    }
?>
<div class="membre_block">
  <?php
     if(!empty($membres)) {
        echo "<h4>Membres connectées dans ta ville</h4>";
	 }
  ?>
  <div id="membresHome">
  <?php 
	
	if(!empty($membres)) {
        foreach($membres as $membre){
            set_query_var( 'user', $membre );
			get_template_part( 'partials/content/content', 'membrehome' );
        }
        set_query_var('scope_lvl', getLowestScopeLevel($_GET));
        set_query_var('category', 'membres');
    } else {
		echo " Tu est l’une des premières membres dans ta ville, reste connectée pour voir les prochaines";
    }
    ?>
  </div>
</div>
<div class="top">
    <h2 class="h2">Vos actualités à <span class="scope-toggle"><span class="scope"><img src="<?php echo get_template_directory_uri().'/assets/images/loader.gif' ?>" alt="" class="little-spinner"></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span></h2>
    <?php get_template_part( 'partials/form/scope', 'change'); ?>
    <div class="alert-notice">
        <a href="/recherche-avancee">Ajoute rapidement ton dressing : Swap et vends dans ta villes avec tes amies</a>
    </div>
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

    // We construct the postlist first, retrieving all of the posts and comments matching the scope and placing them in the corresponding level

    $posts = array();
    $product_nbr = 0;
    $swapplaces = array(
        "scope" => array(),
        "more" => array(),
        "even_more" => array()
    );

    $args = array (
        'post_type' => array('produits', 'swapplaces'),
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'author__not_in' => array($current_user_id),
        'nopaging' => true
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $post_id = get_the_id();
            $post_type = get_post_type();
            if(get_field('is_coup_de_coeur', $post_id) == 1 && checkFeaturedPostCity($post_id, $scope)) {
                $postlist["featured"]["cdc"] = $post_id;
                $product_nbr ++;
            } else {
                $post_scope = getPostScope($post_id, $post_type, $scope);
                if($lowest_scope_level == 'postal_code') {
                    // Only show popular item if the scope is on a city
                    if($post_type == 'produits' && $post_scope == "scope") {
                        if($postlist["featured"]["popular"] == null) {
                            $popular_likes = 0;
                        } else {
                            $popular_likes = getLikesNumber($postlist["featured"]["popular"]);
                        }
                        if(getLikesNumber($post_id) > $popular_likes) {
                            $postlist["featured"]["popular"] = $post_id;
                        }
                    }
                }
                if($post_scope != false) {
                    array_push($postlist[$post_scope], array($post_type, $post_id));
                    if($post_type == 'produits' && $post_scope == 'scope') {
                        $product_nbr ++;
                    }
                    if($post_type == 'swapplaces') {
                        array_push($swapplaces[$post_scope], $post_id);
                    }
                }
            }
        }
    }
    wp_reset_postdata();

    $comms = array();
    $args = array(
        'type'           => 'comment',
        'post_status'    => 'publish',
        'post_type'      => array('produits', 'swapplaces', 'dressings'),
        'order'          => 'DESC',
        'orderby'        => 'comment_date',
        'author__not_in' => array($current_user_id),
        'nopaging'       => true
    );

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query( $args );

    if ( !empty( $comments ) ) {
        foreach ( $comments as $comment ) {
        $comment_id = $comment->comment_ID;
            if(!isCommentChild($comment_id)) {
                $post_scope = getCommentScope($comment_id, $scope);
                // Only show comments in the scope
                if($lowest_scope_level == 'postal_code' && $post_scope == 'scope') {
                    array_push($postlist[$post_scope], array('comments', $comment_id));
                }
            }
        }
    }

    $args = array(
        'role' => 'contributor',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true,
        'exclude' => array( '-'.$current_user_id )
    );
    $user_query = new WP_User_Query( $args );
    if (!empty($user_query->results)) {
        foreach ( $user_query->results as $user ) {
            $user_id = $user->ID;
            if(userHasProducts($user_id)) {
                $user_scope = getUserScope($user_id, $scope);
                if($user_scope != false) {
                    array_push($postlist[$user_scope], $user_id);
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

    // Then we sort and finally display the postlist
    displayPosts(sortPosts($postlist, 'distance'));
?>
</div>
<div class="chat-pop"><a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie'; ?>"><img src="<?php echo get_template_directory_uri().'/assets/images/chat.svg'?>" alt=""></a></div>

<?php
    get_template_part( 'partials/content/content', 'end' );
    get_footer(); 
?>