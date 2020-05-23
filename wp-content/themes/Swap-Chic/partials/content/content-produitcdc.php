<?php 
    $post_id = get_query_var('post')->ID; 
    $city = get_query_var('city'); 
    $user = get_field('proprietaire', $post_id);
    $images = get_field('images', $post_id);
    $featured = get_field('is_coup_de_coeur', $post_id);
?>

<div class="produitcdc setcdc <?php if($featured) echo ' cdc' ?>" data-id="<?php echo $post_id ?>" onclick="setCoupDeCoeur(<?php echo $post_id ?>, this)" >
    <div class="produit-thumbnail">
        <?php echo get_the_post_thumbnail($post_id) ?>
    </div>
</div>