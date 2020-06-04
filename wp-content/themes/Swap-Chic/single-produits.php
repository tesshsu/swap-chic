<?php
/**
 * The template for displaying single products
**/


if($_POST){
    $response_code = validate($_POST['ID']);
    print_r($response_code);
    if($response_code == 200) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/articles-a-valider/');
    } else {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/articles-a-valider/?response_code='.$response_code."&post_id=".$_POST['ID']);
    }
}

get_header(); 

global $post;
$post_slug = $post->post_name;
$post_id = get_the_id(); 
$user = get_field('proprietaire', $post_id);
$images = get_field('images', $post_id);
$current_user = wp_get_current_user();
$is_liked = isPostLiked($post_id);

if($user['ID'] == get_current_user_id()) {
    $is_owner = true;
} else {
    $is_owner = false;
}

$status = get_post_status($post_id);

$title = get_the_title($post_id);

$categorie = get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['label'];
$sous_categorie = get_field('sous_categorie_'.get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['value'], $post_id)['label'];

$couleur = get_field('couleur', $post_id);
$matiere - get_field('matiere', $post_id);
$imprime - get_field('imprime', $post_id);
$content = get_post_field('post_content', $post_id);
$size = getProductSize($post_id);

$saison_array = get_field('saison', $post_id);
if(!empty($saison_array)) {
    $i = 0;
    foreach($saison_array as $saison) {
        if($i == 0) {
            $saisons .= $saison;
        } else {
            $saisons .= ', '.strtolower($saison);
        }
        $i++;
    }
}
?>

<div class="produit-single <?php if(in_array('administrator', $current_user->roles)) echo 'admin' ?>" data-id="<?php echo $post_id ?>" data-type="produit">
    <div class="produit-title">
        <a href="javascript:history.back()" class="produit-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
        <h1 class="h1"><?php echo $title; ?></h1>
    </div>
    <div class="produit-carousel"> 
    <?php 
        foreach($images as $image){ ?>
            <div class="carousel-item">
                <img src="<?php echo $image ?>'" alt=""> 
                <div class="desktop btn show-img"><img src="<?php echo get_template_directory_uri().'/assets/images/expand.svg'; ?>" alt=""></div>
            </div>
    <?php } 
    ?>
    </div>
    <div class="infos-wrapper">
        <div class="action-social">
            <p class="action">
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
            <div class="user">
                <img src="<?php echo get_field('photo_profil', 'user_'.$user['ID']); ?>" alt="">
                <p><a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user['ID'])) ?>"><?php echo ucfirst($user['display_name']); ?></a>, <?php echo get_field('ville', 'user_'.$user['ID']); ?></p>
            </div>
        </div>
        <div class="infos">
            <p>
                <span class="label">Type :<br></span>
                <span><?php echo $sous_categorie ?></span><span class="mini"> <?php echo $categorie ?></span>
            </p>
            <?php if($size = getProductSize($post_id))
                print '<p><span class="label>Taille :<br></span>'.$size.'</p>' ;
            ?>
            <p><span class="label">Marque :<br></span><?php echo get_field('marque', $post_id); ?></p>
            <?php if($size) echo '<p><span class="label">Taille :<br></span>'.$size.'</p>' ?>
            <p><span class="label">État :<br></span><?php echo get_field('etat', $post_id); ?></p>
            <?php if(strlen($matiere) > 0) { ?><p><span class="label">Matière :<br></span><?php echo $matiere ?></p><?php } ?>
            <?php if(strlen($couleur) > 0) { ?><p><span class="label">Couleur :<br></span><?php echo $couleur; ?></p><?php } ?>
            <?php if(strlen($imprime) > 0) { ?><p><span class="label">Imprimé :<br></span><?php echo $imprime ?></p><?php } ?>
            <?php if(isset($saisons) > 0) { ?><p><span class="label">Saison :<br></span><?php echo $saison; ?></p><?php } ?>
            <?php if(strlen($content) > 0) { ?><p class="description"><?php echo $content ?></p><?php } ?>
        </div>
    </div>
    <div class="user-wrapper">
    <?php
    // Depending on the user role and the post status, we display different options
    if(!is_user_logged_in()) { ?>
            <div class="not-connected">
                <p>Connectez vous pour accéder à toutes les fonctionnalités de Swap-Chic.</p>
                <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="btn">Connexion</a>
            </div>
       <?php } elseif(!$is_owner && $status == 'publish') {?>
            <div data-userid="<?php echo $user['ID'] ?>" class="openChat btn" onclick="openChat(<?php echo get_current_user_id().', '.$user['ID'] ?>)">Contacter<img src="<?php echo get_template_directory_uri().'/assets/images/chat-white.svg'; ?>" alt=""></div>

        <?php } elseif(in_array('administrator', $current_user->roles) && $status == 'draft') { ?>
            <form class="admin-actions" method="post">
                <input type="hidden" name="ID" value="<?php echo $post_id ?>">
                <div class="btn validate">Accepter</div>
                <div onclick="unvalidate(<?php echo $post_id?>, this)" class="btn unvalidate">Refuser</div>
            </form>
        <?php  } 
        if($status == 'publish' && is_user_logged_in()) { ?>
            <div class="social">
                <div class="social-close" onclick="closeSocial(this)"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></div>
                <div class="likes" onclick="<?php if(is_user_logged_in()) echo 'like('.'\'produits\', \''.$post_id.'\''.', this)'?>">
                    <?php if(!$is_liked) { ?>
                        <img src="<?php echo get_template_directory_uri().'/assets/images/likes.svg'?>" alt="">
                    <? } else { ?>
                        <img src="<?php echo get_template_directory_uri().'/assets/images/liked.svg'?>" alt="">
                    <?php } ?>
                    <span><?php echo getLikesNumber($post_id) ?></span>
                </div>
                <div class="comments" onclick="getComments(<?php echo '\'produits\', \''.$post_id.'\'' ?>, this)">
                    <img src="<?php echo get_template_directory_uri().'/assets/images/comments.svg'?>" alt="">
                    <span><?php echo getCommentsNumber($post_id) ?></span>
                </div>
                <div class="share">
                    <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg';?>" alt="">
                    <span></span>
                    <div class="addtoany-wrapper">
                        <div class="a2a_kit a2a_kit_size_26 a2a_default_style" data-a2a-url="<?php echo get_permalink($post_id) ?>" data-a2a-title="<?php echo get_the_title($post_id) ?>">
                            <a class="a2a_button_facebook"></a>
                            <a class="a2a_button_twitter"></a>
                            <a class="a2a_button_pinterest"></a>
                            <a class="a2a_button_email"></a>
                            <a class="a2a_button_whatsapp"></a>
                            <a class="a2a_button_facebook_messenger"></a>
                        </div>
                    </div>
                </div>
                <?php  
                    set_query_var('post_type', 'produits');
                    set_query_var('post_id', $post_id);
                    get_template_part( 'partials/content/content', 'commentthread' ); 
                ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php
get_footer();
