<?php 
    $post_id = get_query_var('post')->ID; 
    $city = get_query_var('city'); 
    $proprietaire = get_field('proprietaire', $comment_post_id)['ID'];
	$title = get_the_title($post_id);
    $images = get_field('images', $post_id);
    $featured = get_field('is_coup_de_coeur', $post_id);
	$code_postal = get_field('zip', $post_id);
?>

<div class="produitcdc setfeatured <?php if($featured) echo ' cdc' ?>" data-title="<?php echo $title ?>" data-postal="<?php echo $code_postal; ?>" data-id="<?php echo $post_id ?>" data-proprietaire="<?php echo $proprietaire ?>" >
    <div class="produit-thumbnail">
        <?php echo get_the_post_thumbnail($post_id) ?>
    </div>
</div>