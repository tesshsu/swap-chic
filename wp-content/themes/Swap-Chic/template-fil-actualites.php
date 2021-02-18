<?php



/*



Template Name: Fil d'actualités



Template Post Type: page



*/







if(!is_user_logged_in()) {



    header('Location: https://'.$_SERVER['HTTP_HOST']);



    exit();



}







get_header();







$scope = getScope($_GET);



$current_user_id = get_current_user_id();



$lowest_scope_level = getScopeFormat($scope["scope"]);



set_query_var('scope', $scope);



set_query_var('scope_string', getScopeString($_GET));



?>


<!---
<form method="get"  class="desktop" id="advanced-search-form">       

    <div class="asf-produits">                                    

        <input class="checkbox-tools" type="radio" value="femme" name="target" id="asf-femme">

						<label class="for-checkbox-tools" for="asf-femme">

							<i class="fas fa-female"></i>

							Femme

						</label>

						

        <input class="checkbox-tools" type="radio" value="enfant" name="target" id="asf-enfant">

						<label class="for-checkbox-tools" for="asf-enfant">

							<i class="fas fa-child"></i>

							Enfant

						</label>

						

        <input class="checkbox-tools" type="radio" value="vip" name="target" id="asf-vip">

						<label class="for-checkbox-tools" for="asf-vip">

							<i class="fas fa-star"></i>

							VIP

						</label>

        

		

					

    </div>

    <div class="asf-actions">

        <div class="asf-submit btn">Lancer la recherche</div>

    </div>

</form>
--->




<?php /*get_search_form();*/ ?>


<!---
<div id="topBanner" class="top desktop">



	<a href="#">



      <img src="<?php echo get_template_directory_uri().'/assets/images/banners/1.jpg' ?>" alt="pub4" class="artGreenImg">



    </a>



</div>



<div id="topBanner" class="top mobile">



	<a href="#">



      <img src="<?php echo get_template_directory_uri().'/assets/images/banners/pb4_mobile.jpg' ?>" alt="pub4">



    </a>



</div>
--->


<?php 



    wp_reset_query();



    $membres = array();



    $args = array(



        'role' => 'contributor',



        'orderby' => 'date',



        'order' => 'DESC',



        'nopaging' => true,



	    'exclude' => array( '-'.$current_user_id )



    );



    $user_query = new WP_User_Query( $args );



	



	if ( ! empty( $user_query->results ) ) {



        foreach ( $user_query->results as $user ) {



            $user_id = $user->ID;



           if(userHasProducts($user_id)) {



                $user_scope = getUserScope($user_id, $scope);



                if($user_scope == 'scope') {



                    array_push($membres, $user_id);



                }



            }



        }



    }



?>



<div class="membre_block">

  <h4>"be part of the change"</h4>

  <div id="middleBanner" class="middleBanner">

	<a href="#">

      <img src="<?php echo get_template_directory_uri().'/assets/images/block_sentence.jpg' ?>" alt="block_sentence" class="artGreenImg">

    </a>

</div>

 <hr style="height: 2px;border-width:0;color:gray;background-color:gray;margin: 10px auto;width: 78%;">

 <h6> Ajoute ton dressing en un clic: Swap/Vends/rencontre <a href="https://swap-chic.com/ajouter-produit/" class="add-product-home"> par ici</a></h6>

</div>



<?php if($_GET) { ?>



    <div id="search-results">

        <?php displayAdvancedSearchPosts(getAdvancedSearchPosts(get_current_user_id(), $_GET)); ?>

    </div>

<?php

} ?>





<div class="top">

	

	 <h2 class="h2">Vos actualités à <span class="scope-toggle"><span class="scope"><img src="<?php echo get_template_directory_uri().'/assets/images/loader.gif' ?>" alt="" class="little-spinner"></span><img src="<?php echo get_template_directory_uri().'/assets/images/edit.svg' ?>" alt=""></span></h2>



    <?php get_template_part( 'partials/form/scope', 'change'); ?>



</div>

 <?php if(!empty($membres)) {

	       echo "<p class='memberSentence'>Les membres en ligne dans ta ville</p>";

        }else{

			echo "<p class='memberSentence'> Tu es l’une des premières membres dans ta ville, invite tes amies pour découvrir leur dressing...</p>";

		}

  ?>

<div id="membresHome" style="margin-top: 20px;">

  <?php 

	if(!empty($membres)) {

		

        foreach($membres as $membre){



            set_query_var( 'user', $membre );



			get_template_part( 'partials/content/content', 'membrehome' );



        }



        set_query_var('scope_lvl', getLowestScopeLevel($_GET));



        set_query_var('category', 'membres');



    }



    ?>



</div>







<div class="chat-pop"><a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/messagerie'; ?>"><i class="fas fa-comments"></i></a></div>







<?php



    get_template_part( 'partials/content/content', 'end' );



    get_footer(); 



?>
