<?php
/*
Template Name: Fil Test
Template Post Type: page
*/

// This was a test of a more optimized WP_Query
// but it does not seem to improve performance


get_header();
?>

<div id="thread-test">
<?php

$postlist = array(
    "featured" => array(
        "cdc" => null,
        "popular" => null,
        "vip" => null,
        "map" => null
    ),
    "scope" => array(),
    "more" => array(),
    "even_more" => array()
);

$swapplaces = array(
    "scope" => array(),
    "more" => array(),
    "even_more" => array()
);

$comments_parents = array();
$product_nbr = 0;
$exclude = array();

foreach($scope as $scope_lvl => $scope_locations) {
    $format = getScopeFormat($scope_locations);

    if(!empty($exclude)) {
        $meta_query = array(
            'relation'		=> 'AND'
        );
        foreach($exclude as $exclude_item){
            $meta_query[] = array(
                'key'		=> 'zip',
                'value'		=> $exclude_item,
                'compare'	=> '!='
            );                
        }
        $exclude = array();
    } else {
        $meta_query = array(
            'relation'		=> 'OR'
        );
    }

    if($format == 'postal_code') {
        $lvl = 'zip';
    } else {
        $lvl = 'dpt';
    }

    foreach($scope_locations as $scope_item) {
        $meta_query[] = array(
            'key'		=> $lvl,
            'value'		=> $scope_item,
            'compare'	=> '='
        );
    }

    $args = array (
        'post_type' => array('produits', 'swapplaces', 'dressings'),
        'author__not_in' => array($current_user_id),
        'nopaging' => true,
        'meta_query' => $meta_query
    );
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
        while ( $the_query->have_posts() ) {
            $the_query->the_post();
            $post_id = get_the_id();
            if($lvl == 'zip') {
                $comments_parents[] = $post_id;
            }
            $post_type = get_post_type();
            if($post_type != 'dressing') {
                if(get_field('is_coup_de_coeur', $post_id) == 1) {
                    if(checkFeaturedPostCity($post_id, $scope)) {
                        $postlist["featured"]["cdc"] = $post_id;
                        $product_nbr ++;
                    }
                } else {
                    $post_scope = getPostScope($post_id, $post_type, $scope);
                    if($lowest_scope_level == 'postal_code') {
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
                    array_push($postlist[$scope_lvl], array($post_type, $post_id));
                    if($post_type == 'produits' && $post_scope == 'scope') {
                        $product_nbr ++;
                    }
                    if($post_type == 'swapplaces') {
                        array_push($swapplaces[$scope_lvl], $post_id);
                    }
                }
            }
        }
    }
    wp_reset_postdata();

    if($lvl == 'zip') {
        $exclude = $scope_locations;

        $args = array(
            'type'           => 'comment',
            'post_status'    => 'publish',
            'post__in'       => $comments_parents,
            'author__not_in' => array($current_user_id),
            'nopaging'       => true
        );

        $comments_query = new WP_Comment_Query;
        $comments = $comments_query->query( $args );

        if ( !empty( $comments ) ) {
            foreach ( $comments as $comment ) {
                $comment_id = $comment->comment_ID;
                if(!isCommentChild($comment_id)) {
                    array_push($postlist['scope'], array('comments', $comment_id));
                }
            }
        }
    }
}

$args = array(
    'role' => 'contributor',
    'orderby' => 'date',
    'order' => 'DESC',
    'nopaging' => true,
    'meta_key' => 'is_influenceuse',
    'meta_value' => true
);
$user_query = new WP_User_Query( $args );

if (!empty($user_query->results)) {
    foreach ( $user_query->results as $user ) {
        $user_id = $user->ID;
        if(checkUserRegion($user_id, $scope)){
            $postlist["featured"]["vip"] = $user_id;
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

if($product_nbr == 0) {
    array_unshift($postlist['scope'], "nodata");
}

displayPosts(sortPosts($postlist, 'distance'));
?>
</div>

<?php
    get_template_part( 'partials/content/content', 'end' );
    get_footer(); 
?>