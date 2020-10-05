<?php 
    $post_id = get_query_var('post'); 
    $user = get_field('proprietaire', $post_id);
    if(get_field('is_coup_de_coeur')){
        $featured = true;
    } else {
        $featured = false;
    }
    
    $categorie = get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['label'];
    $sous_categorie = get_field('sous_categorie_'.get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['value'], $post_id)['label'];  
    $is_liked = isPostLiked($post_id);
?>

<div data-id="<?php echo $post_id ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $post_id ); ?>" data-type="produit" class="produit<?php if($featured) echo ' cdc' ?><?php if($is_liked) echo ' liked' ?>">
    <div class="produit-thumbnail">
        <?php echo get_the_post_thumbnail($post_id) ?>
    </div>
    <div class="infos-wrapper">
        <h3 class="h1"><?php echo generateProductTitle($post_id) ?></h3>
        <div class="user">
            <img src="<?php echo get_field('photo_profil', 'user_'.$user['ID']) ?>" alt="">
            <p><a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user['ID'])) ?>"><?php echo ucfirst($user['display_name']) ?></a>, <?php echo get_field('ville', 'user_'.$user['ID']) ?></p>
        </div>
        <div class="infos">
            <p class="swaporsell">
                <b><?php 
                    $action = get_field('action', $post_id);
                    if( $action[0] == 'À vendre' && count($action) == 1) {
                        echo 'À vendre : '.get_field('prix', $post_id).'€'; 
                    } elseif($action[1]) {
                        echo 'À swaper ou à vendre : '.get_field('prix', $post_id).'€';
                    } else {
                        echo 'À swaper';
                    }
                ?></b>
            </p>
            <p><span><?php echo $sous_categorie ?></span><span class="mini"> <?php echo $categorie ?></span></p>
            <?php if($size = getProductSize($post_id)) echo '<p>'.$size.'</p>' ?>
        </div>
        <?php 
            set_query_var('post_type', 'produits');
            set_query_var('post_id', $post_id);
            set_query_var('is_liked', $is_liked);
            get_template_part( 'partials/test/social', 'post' );
        ?>
    </div>
</div>