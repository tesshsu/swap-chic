<?php
/*
Template Name: Introduction
Template Post Type: page
*/

// The introduction is in the form of a slick carousel

    get_header(); 
?>

<div class="intro-presentation">
    <div class="intro-slide" id="intro-title">
        <h1><img src="<?php echo get_template_directory_uri().'/assets/images/logo-devise.svg' ?>" alt="Swap-Chic"></h1>
    </div>
    <div class="intro-slide mobile" id="intro-video" style="overflow-y: scroll; -webkit-overflow-scrolling: touch; ">
	    <img src="<?php echo get_template_directory_uri().'/assets/images/sp_intro.jpg' ?>" alt="Swap-Chic-img">
    </div>
	
	<div class="intro-slide desktop" id="intro-video">
	    <div class="videopart">
		   <img src="<?php echo get_template_directory_uri().'/assets/images/sp_intro.jpg' ?>" class="ccm" alt="Swap-Chic-img"> 
		</div>
    </div>		
	<div class="intro-slide" id="intro-video" style="overflow-y: scroll; -webkit-overflow-scrolling: touch; ">       
	<p id="intro-text-video">Découvre le concept Swap-chic : </p>
	<video id="video" poster="<?php echo get_template_directory_uri().'/assets/images/poster.png' ?>" disablepictureinpicture controlslist="nodownload">
            <source src="<?php echo get_template_directory_uri().'/assets/Swap_chic.mp4' ?>" type="video/mp4">
        </video>  
	</div>
    
    <?php // This is for a smooth transition to the sign-in page ?>
    <div class="intro-slide" id="intro-filler">
    </div>

</div>
<div class="intro-actions">
    <div class="skip"><a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/sign-in' ?>">Passer l'intro</a></div>
    <div class="prev hidden btn">Précedent</div>
    <div class="next btn">Suivant</div>
</div>

<?php 
    get_footer();
?>