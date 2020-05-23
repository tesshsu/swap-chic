<div class="loader">
    <img src="<?php echo get_template_directory_uri().'/assets/images/logo-devise.svg' ?>" alt="Swap-Chic">
    <img src="<?php echo get_template_directory_uri().'/assets/images/loader.gif' ?>" alt="" class="spinner">
    <?php if(explode('/', $_SERVER[REQUEST_URI])[2] == 'nouvelle-discussion') { ?>
        <img src="<?php echo get_template_directory_uri().'/assets/images/quote-2.svg' ?>" alt="La vie est comme une immense chasse au trésor où chaque nouvelle rencontre peut devenir une si belle surprise." class="quote">
    <?php } else { ?>   
        <img src="<?php echo get_template_directory_uri().'/assets/images/quote-1.svg' ?>" alt="Privilégie l'usage à la possession" class="quote">
    <?php } ?>
    <img src="<?php echo get_template_directory_uri().'/assets/images/leaves.png' ?>" alt="" class="leaves">
</div>

<script>
    var loader_timeout = setTimeout( function() {     
    jQuery('.loader').fadeOut(function(){
        jQuery('.loader').remove();
    });
    } , 5000);
</script>