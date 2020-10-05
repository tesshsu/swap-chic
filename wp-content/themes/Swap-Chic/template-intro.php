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
     <div class="intro-slide" id="intro-video" style="overflow-y: scroll; -webkit-overflow-scrolling: touch; ">
	    <img src="<?php echo get_template_directory_uri().'/assets/images/sp_intro.jpg' ?>" alt="Swap-Chic-img">
        <div class="video-responsive">
		<iframe width="560" height="315" src="https://www.youtube.com/embed/Qrv10M4APcs" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
    </div>

    <div class="intro-slide intro-tutorial" id="intro-tutorial">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm1.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm2.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm3.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm4.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm5.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm6.png' ?>" alt="" class="ccm">
        <img src="<?php echo get_template_directory_uri().'/assets/images/ccm7.png' ?>" alt="" class="ccm">
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