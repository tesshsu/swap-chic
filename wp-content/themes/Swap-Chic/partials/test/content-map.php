<?php 
    $swapplaces = get_query_var('swapplaces'); 
    $scope_lvl = get_query_var('map_scope'); 
    $scope_lowest_lvl =  getLowestScopeLevel($_GET); 

    if($scope_lowest_lvl == 'ville') {
        if($scope_lvl == 'scope') {
            $scope_name = 'ta ville';
            $map_lvl = 'ville';
        } elseif($scope_lvl == 'more') {
            $scope_name = 'ton département';
            $map_lvl = 'departement';
        } else {
            $scope_name = 'ta région';
            $map_lvl = 'region';
        }
    } elseif($scope_lowest_lvl == 'departement') {
        if($scope_lvl == 'scope') {
            $scope_name = 'ton département';
            $map_lvl = 'departement';
        } elseif($scope_lvl == 'more') {
            $scope_name = 'ta région';
            $map_lvl = 'region';
        }
    } else {
        if($scope_lvl == 'scope') {
            $scope_name = 'ta région';
            $map_lvl = 'region';
        }
    }

    $adresses = array();
    foreach($swapplaces as $post) {
        $adresses[] = array($post, get_field('adresse', $post));
    }
?>

<div class="map" data-level="<?php echo $map_lvl ?>">
    <p>Nous avons trouvé <b><?php echo count($swapplaces) ?> swap-places</b> dans <?php echo $scope_name ?> :</p>
    <div class="map-iframe" id="map-<?php echo $scope_lvl ?>"></div>
    <div class="map-locate btn"><img src="<?php echo get_template_directory_uri().'/assets/images/target.svg' ?>" alt=""></div>
    <div class="map-draw btn">Voir le trajet</div>
    <div class="map-reset btn">Réinitialiser</div>
    <div class="map-bottom">
        <div class="swapplaces-caroussel">
            <?php foreach($adresses as $adresse) { ?>
                <div data-id="<?php echo $adresse[0] ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $adresse[0] ); ?>">
                    <img src="<?php echo get_field('images', $adresse[0])[0] ?>" alt="">
                </div> 
            <?php } ?>
        </div>
        <div class="swapplaces-caroussel-infos"> 
            <?php foreach($adresses as $adresse) { ?>
                <div data-id="<?php echo $adresse[0] ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $adresse[0] ); ?>">
                    <p class="h1"><?php echo get_the_title($adresse[0]) ?></p>
                    <p><?php echo $adresse[1] ?></p>
                    <p class="href">En savoir plus...</p>
                </div> 
            <?php } ?>
        </div>
    </div>
</div>