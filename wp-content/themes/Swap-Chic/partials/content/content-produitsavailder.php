<?php 
    $post_id = get_query_var('post'); 
    $user = get_field('proprietaire', $post_id);
    $last_checked = get_field('last_checked', $post_id);
?>

<div data-id="<?php echo $post_id ?>" data-type="produit" class="produit<?php if($featured) echo ' cdc' ?><?php if($is_liked) echo ' liked' ?>">
    <div class="produit-image">
        <img src="<?php echo get_field('images', $post_id)[0] ?>" alt="">
    </div>
    <p class="h1"><?php echo get_the_title($post_id) ?></p>
    <div class="user">
        <p>
            <a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user['ID'])) ?>">
                <img src="<?php echo get_field('photo_profil', 'user_'.$user['ID']) ?>" alt="">
                <?php echo "<span style='text-decoration: underline;'>".ucfirst($user['display_name'])."</span>, ".get_field('ville', 'user_'.$user['ID']) ?>
            </a>
        </p>
    </div>
    <div class="infos">
        <?php 
            if($last_checked == null) {
        ?>
            <p>Date d'ajout : <?php echo get_the_date('d/m/Y H:i:s', $post_id) ?></p>
        <?php 
            } else {
        ?>
            <p>Dernière vérification : <?php echo $last_checked?></p>
            <p>Dernière modification : <?php echo get_the_modified_date('d/m/Y H:i:s', $post_id) ?></p>
        <?php 
            }
        ?>
    </div>
    <form class="admin-actions" method="post">
        <input type="hidden" name="ID" value="<?php echo $post_id ?>">
        <div onclick="validate(<?php echo $post_id?>, this)" class="btn validate">Accepter</div>
       <p class="btn unvalidate"><a onclick="unvalidate(<?php echo $post_id?>, this)" href="<?php echo get_delete_post_link( $post->ID ) ?>">Refuser</a></p>
       <p class="btn unvalidate"><a href="<?php echo get_delete_post_link( $post->ID ) ?>">DUPLICATE</a></p>
	</form>
</div>