<?php
/*
Template Name: Nouvelle discussion
Template Post Type: page
*/

get_header(); 

if(!is_user_logged_in()) {
    header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
    exit();
}

$scope_location = getScope($_GET);
$current_user_id = get_current_user_id();
?>

<div class="listing-title">
    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie/' ?>" class="listing-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <p class="h1">Nouvelle discussion</p>
</div>

<div class="top">
    <p class="h2">Les membres à de <span class="scope-toggle"><span class="scope"><?php echo $scope_location?></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span></p>
    <?php get_template_part( 'partials/form/scope', 'change'); ?>
</div>

<div id="membres">
    <?php 
    $membres = array(
        "scope" => array(),
        "more" => array(),
        "even_more" => array()
    );
    $args = array(
        'role' => 'contributor',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true,
	    'exclude' => array( '-'.$current_user_id )
    );
    $user_query = new WP_User_Query( $args );

    // The User Loop
    if ( ! empty( $user_query->results ) ) {
        foreach ( $user_query->results as $user ) {
            $user_id = $user->ID;            
            $user_scope = getUserScope($user_id, $scope_location);
            if($user_scope != false) {
                array_push($membres[$user_scope], $user_id);
            }
        }
    }

    if(!empty($membres)){
        foreach($membres as $key => $scope_level) {
            if($key == 'scope' && empty($scope_level)) {
                print '<p style="text-align:center">Aucun membre à cet endroit...</p>';
            } elseif($key == 'more' && !empty($scope_level)) {
                if(strpos($_GET['more'], ',')) {
                    $nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.substr($_GET['more'], 0, 2).'?fields=region'))->region->nom;
                } else {
                    $nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.$_GET['more'].'?fields=nom'))->nom;
                }
                print '<p class="h2">Plus de membres de <br><span class="scope-level">'.$nom.'</span></p>';
            } elseif($key == 'even_more' && !empty($scope_level)) {
                $nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.substr($_GET['even_more'], 0, 2).'?fields=region'))->region->nom;
                print '<p class="h2">Plus de membres de <br><span class="scope-level">'.$nom.'</span></p>';
            }
            foreach($scope_level as $membre) { 
                $is_online = false;
                if(get_user_meta($membre, 'asdb-loggedin')[0] == 1) {
                    $is_online = true;
                } ?>
                
                <div data-userid="<?php echo $membre ?>" class="user-min <?php if($is_online) echo 'online' ?>" onclick="openChat(<?php echo get_current_user_id().', '.$membre ?>)">
                    <img src="<?php echo get_field('photo_profil', 'user_'.$membre) ?>" alt="">
                    <p><a><?php echo ucfirst(get_userdata($membre)->data->display_name) ?></a>, <?php echo get_field('ville', 'user_'.$membre) ?></p>
                </div>
        <?php }
        }
    } else { ?>
    <div class="nodata">Aucun membre trouvée... <div class="btn scope-toggle">Chercher ailleurs</div></div>
    <?php } ?>
</div>

<?php
get_footer();
