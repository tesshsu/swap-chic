<?php 

    $post_id = get_query_var('post'); 

    $user = get_field('proprietaire', $post_id);
	
	$code_postal = get_field('zip', $post_id);
	
	$title = get_the_title($post_id);
	
	$featured = get_field('is_coup_de_coeur', $post_id);

    /*if(get_field('is_coup_de_coeur', $post_id) == 1){

        $featured = true;

    } else {

        $featured = false;

    }*/



    $categorie = get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['label'];

    $sous_categorie = get_field('sous_categorie_'.get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['value'], $post_id)['label'];  

    $is_liked = isPostLiked($post_id);
	
	//search scoop area
	
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

?>



<div data-id="<?php echo $post_id ?>" data-title="<?php echo $title ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $post_id ); ?>" data-postal="<?php echo $code_postal; ?>" data-level="<?php echo $map_lvl ?>" data-type="produit" class="produit <?php if($featured) echo ' cdc' ?><?php if($is_liked) echo ' liked' ?>">

    <div class="produit-thumbnail">

        <img src="<?php echo get_the_post_thumbnail_url($post_id) ?>"alt="">

		<div data-userid="<?php echo $user['ID'] ?>" class="openChat btn" onclick="openChat(<?php echo get_current_user_id().', '.$user['ID'] ?>)"><img src="<?php echo get_template_directory_uri().'/assets/images/chat-white.svg'; ?>" alt=""></div>

        <p class="produit-size"><span><?php echo $sous_categorie ?></span><span class="mini"> <?php echo $categorie ?> </span></p>

		<div class="size-block"><?php if($size = getProductSize($post_id)) echo $size; ?></div>

	</div>

    <div class="infos-wrapper">

        <h3 class="h1"><?php echo generateProductTitle($post_id) ?></h3>

        <!--div class="user">

            <p>

                <a href="<?php echo get_permalink(get_field('dressing', 'user_'.$user['ID'])) ?>">

                    <img src="<?php echo get_field('photo_profil', 'user_'.$user['ID']) ?>" alt="">

                    <?php echo "<span style='text-decoration: underline;'>".ucfirst($user['display_name'])."</span>, ".get_field('ville', 'user_'.$user['ID']) ?>

                </a>

            </p>

            <div data-userid="<?php echo $user['ID'] ?>" class="openChat btn" onclick="openChat(<?php echo get_current_user_id().', '.$user['ID'] ?>)"><img src="<?php echo get_template_directory_uri().'/assets/images/chat-white.svg'; ?>" alt=""></div>

        </div-->

        <div class="infos">

            <p class="swaporsell">

                <b><?php 

                    $action = get_field('action', $post_id);

                    if( $action[0] == 'À vendre' && count($action) == 1) {

                        echo 'À vendre : '.get_field('prix', $post_id).'€'; 

                    } elseif(isset($action[1])) {

                        echo 'À swaper/ à vendre : '.get_field('prix', $post_id).'€';

                    } else {

                        echo 'À swaper';

                    }

                ?></b>

            </p>

        </div>

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


            <div class="share">

                <img src="<?php echo get_template_directory_uri().'/assets/images/share.svg'?>" alt="">

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

    </div>

</div>