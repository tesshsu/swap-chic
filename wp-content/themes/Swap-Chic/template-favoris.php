<?php
/*
Template Name: Favoris
Template Post Type: page
*/

get_header();

$path = getPath();
$user = wp_get_current_user();

// Depending on the path we display the right post type

if($path[1] == 'liste-de-souhait') {
    $data = get_field('produits', 'user_'.$user->ID); ?>
    <div class="h2">Votre liste de souhait</div>
    <div id="listing">
        <?php
            if(!empty($data)) {
                foreach($data as $produit){
                    set_query_var( 'post', $produit );
                    get_template_part( 'partials/content/content', 'produits' );
            ?> 
                    <div class="delete-favori" onclick="deleteFavori(<?php echo $produit ?>, 'produits', this)">Supprimer</div>
            <?php
                }
            } else {
                print '<p style="width: 100%; text-align:center; padding: 40px 20px 0;"> Aucun produit dans la liste de souhait pour le moment...';
            }
        ?>
    </div>

<?php } elseif($path[1] == 'swap-places-favorites') {
    $data = get_field('swap-places', 'user_'.$user->ID); ?>
    <div class="h2">Vos Swap-places favorites</div>
    <div id="listing">
        <?php
            if(!empty($data)) {
                foreach($data as $swapplace){
                    set_query_var( 'post', $swapplace );
                    get_template_part( 'partials/content/content', 'swapplaces' );
            ?> 
                    <div class="delete-favori" onclick="deleteFavori(<?php echo $swapplace ?>, 'swapplaces', this)">Supprimer</div>
            <?php
                }
            } else {
                print '<p style="width: 100%; text-align:center; padding: 40px 20px 0;"> Aucune swap-place favorite pour le moment...';
            }
        ?>
    </div>

<?php } elseif($path[1] == 'membres-suivies'){
    $data = get_field('utilisateurs', 'user_'.$user->ID); ?>
    <div class="h2">Vos membres suivies</div>
    <div id="listing">
        <?php
            if(!empty($data)) {
                foreach($data as $dressing_id){
                    $user = get_field('proprietaire', $dressing_id);
                    set_query_var( 'user', $user['ID'] );
                    get_template_part( 'partials/content/content', 'dressings' );
            ?> 
                    <div class="delete-favori" onclick="deleteFavori(<?php echo $dressing_id ?>, 'dressings', this)">Supprimer</div>
            <?php
                }
            } else {
                print '<p style="width: 100%; text-align:center; padding: 40px 20px 0;"> Aucune membre suivie pour le moment...';
            }
        ?>
    </div>
<?php 
    }
    get_footer(); 
?>