// Copy a list of mails from an hidden input
function copyMails(input) {
    var copyText = input.previousElementSibling.value;
    var tmp_input = document.createElement('input');
    var dashboard = document.getElementById('dashboard');
    tmp_input.type = 'text';
    tmp_input.value = copyText;
    dashboard.appendChild(tmp_input)
    tmp_input.select();
    tmp_input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    tmp_input.remove();
    alert("Mails copiés");
}

// Filter posts in the catalogue page
function filterPosts(posts, posts_type, input) {
    for(var i = 0; i < posts.length; i++) {
        if(posts_type == 'produits') {
            var post = new wp.api.models.Produits({id: posts[i] });
        } else if(posts_type == 'swapplaces') {
            var post = new wp.api.models.Swapplaces({id: posts[i] });
        } else {
            return false;
        }
        post.fetch().done( function(response) {
            var match_filters = true;
            jQuery('.filters-'+posts_type).children('div').each(function () {
                var match_fieldset = true;
                if(jQuery(this).find('input:checked').length > 0) {
                    match_fieldset = false;
                    jQuery(this).find('input:checked').each(function () {
                        field_name = jQuery(this).attr('data-name');
                        if(field_name.substr(0, 14) == 'sous_categorie') {
                            if(response.acf['categorie-femme'].value.toLowerCase() != field_name.substr(15)) {
                                match_fieldset = true;
                                return false;
                            }
                        }
                        if(Array.isArray(response.acf[field_name])) {
                            if( response.acf[field_name].toLowerCase().indexOf(jQuery(this).val()) != -1) {
                                match_fieldset = true;
                            }
                        } else if(typeof response.acf[field_name] === 'object') {
                            if( response.acf[field_name].value.toLowerCase() == jQuery(this).val()) {
                                match_fieldset = true;
                            }
                        } else {
                            if( response.acf[field_name].toLowerCase() == jQuery(this).val() ) {
                                match_fieldset = true;    
                            }
                        }
                    });
                    if(match_fieldset == false) {
                        match_filters = false;
                        return false;
                    }
                }
            })
            if(match_filters) {
                jQuery('[data-id='+response.id+']').removeClass('hidden');
            } else {
                jQuery('[data-id='+response.id+']').addClass('hidden');
            }
        });
    }
}

// Return url pathname as array
function urlToArray() {
    return window.location.pathname.split('/');
}

// Return url anchor
function getAnchor() {
    var url = window.location.href;
    return url.substring(url.indexOf("#")+1);
}

// Closw image modal on product page
function closeModal() {
    jQuery('.produit-image-modal').remove();
}

// Display image before upload, see https://stackoverflow.com/questions/4459379/preview-an-image-before-it-is-uploaded
function readURL(input) {
    var img_id = jQuery(input).attr('id');
    var input_index = img_id.substr(img_id.lastIndexOf('-') + 1, img_id.length);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.createElement("img");
            img.src = e.target.result;

            // Resize image to fit 768 x 768 px format
            jQuery(img).on('load', function(){
                if(jQuery('#tmp-canvas-'+input_index).length) {
                    jQuery('#tmp-canvas-'+input_index).remove();
                }
                jQuery('#image-'+input_index).prepend('<canvas id="tmp-canvas-'+input_index+'"></canvas>');
                var canvas = document.getElementById('tmp-canvas-'+input_index);
                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);

                var MAX_WIDTH = 768;
                var MAX_HEIGHT = 768;
                var width = img.width;
                var height = img.height;
                var css_class = "wider"; 

                if (width > height) {
                    css_class = "wider";
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    css_class = "higher";
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, width, height);

                var dataurl = canvas.toDataURL("image/jpeg");
                jQuery(input).prop('required', false);
                jQuery(canvas).addClass(css_class);
                jQuery('#image-'+input_index).parent().siblings('.image-actions').removeClass('hidden');

                // Remove old image if it exists and add the new one
                jQuery('#image-'+input_index).children('img').remove();
                jQuery('#image-'+input_index).append( jQuery('<img>').attr('src', dataurl).addClass(css_class));
                jQuery('input[name=image-'+input_index+']').val(dataurl);
                jQuery('input[name=image-'+input_index+']').change();
                jQuery('canvas').remove();
            });
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Rotate an image 90deg on the right
function rotate90(src, callback){
    var img = new Image()
    img.src = src
    img.onload = function() {
        var canvas = document.createElement('canvas')
        canvas.width = img.height
        canvas.height = img.width
        canvas.style.position = "absolute"
        var ctx = canvas.getContext("2d")
        ctx.translate(img.height, img.width / img.height)
        ctx.rotate(Math.PI / 2)
        ctx.drawImage(img, 0, 0)
        callback(canvas.toDataURL("image/jpeg"))
    }
}

// Display image before upload and crop it, see cropper.js documentation
function readProfilePictureURL(input){
    var target = jQuery('#crop-modal .img-wrapper img');
    if (input.files && input.files[0]) {  
        var reader = new FileReader();
        reader.onload = function (e) {
            jQuery('#crop-modal').css('display', 'flex');
            target.attr('src', e.target.result);
            if(target.data('cropper')) {
                target.data('cropper').replace(e.target.result);
            } else {
                target.cropper({
                    aspectRatio: 1 / 1,
                    viewMode: 1,
                    dragMode: 'none',
                    movable: false,
                    zoomable: false,
                    ready(){
                        this.cropper.crop();
                        jQuery('#crop').unbind().click(function(){
                            var croppedImageDataURL = target.data('cropper').getCroppedCanvas().toDataURL("image/jpeg");
                            jQuery('#crop-modal').hide();
                            jQuery(input).prop('required', false);
                            jQuery('#image').children('img').remove();
                            jQuery('#image').append( jQuery('<img>').attr('src', croppedImageDataURL));
                            jQuery('#image').removeClass('hasBefore');
                            jQuery('#edit-cropped-picture').val(croppedImageDataURL);
                            jQuery('#signup-cropped-picture').val(croppedImageDataURL);
                            jQuery('#signup-profile-picture').removeAttr('required');
                            jQuery('#edit-profile-picture').removeAttr('required');
                        });
                        jQuery('#close').click(function(){
                            jQuery('#signup-profile-picture').val('');
                            jQuery('#edit-profile-picture').val('');
                            jQuery('#crop-modal').hide();
                        });
                    }
                });
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Close social action section
function closeSocial(element) {
    if(jQuery(element).parents('.produit-single').length) {
        jQuery(element).parent().siblings().show();
        jQuery(element).parents('.user-wrapper').css({
            'position' : 'fixed',
            'align-items': 'space-between'
        });
        jQuery(element).parents('.user-wrapper').siblings('.infos-wrapper').children('.infos').css({
            'margin-bottom': '80px'
        });
    } else if(jQuery(element).parents('.swapplace-single').length ) {
        jQuery(element).parent().siblings().show();
        jQuery(element).parents('.bottom-swapplace').css({
            'position' : 'fixed',
            'align-items': 'space-between'
        });
        jQuery(element).parents('.bottom-swapplace').siblings('.infos-wrapper').children('.description').css({
            'margin-bottom': '80px'
        });
    } else if(jQuery(element).parents('.dressing-single').length ) {
        jQuery(element).parent().siblings().show();
        jQuery(element).parents('.bottom-dressing').css({
            'position' : 'fixed',
            'align-items': 'space-between'
        });
        jQuery(element).parents('.bottom-dressing').siblings('.content').css({
            'margin-bottom': '80px'
        });
    }
    jQuery(element).siblings('.comment-thread-wrapper').children('.comment-thread').empty();
    jQuery(element).siblings('.comment-thread-wrapper').hide();
    jQuery(element).siblings('.share').children('.addtoany-wrapper').hide();
    jQuery(element).parent().css({
        'marginBottom': '0',
        'borderRadius': '20px'
    });
    jQuery(element).parent().removeClass('open');
}

// Open swap-place post from chat
function openChatSwapplace(id) {
    window.location.assign('https://' + window.location.host + '/?post_type=swapplaces&p=' + id);
}

// Open product post from chat
function openChatProduct(id) {
    window.location.assign('https://' + window.location.host + '/?post_type=produits&p=' + id);
}

jQuery(document).ready(function () {
    var urlArray = urlToArray();
    var isMobile = false; 
    var isTablet = false; 
    var isProfilShown = false;
    var isSendmoreShown = false;
    var isSearchShown = false;

    // If there is no scope display section, remove the loader
    if(jQuery('.scope-toggle').length == 0) {
        jQuery('.loader').fadeOut(function(){
            jQuery('.loader').remove();
        });
    }

    // Scroll back to top or reallow scroll after closing hide bubble
    if(jQuery('#help-fil').length == 0) {
        if ( Cookies.get("hide-helps") == 1 ) {
            jQuery('html, body').css('overflow', 'visible');
        } else {
            jQuery("html, body").scrollTop(0);
        }
    }
    if(jQuery('#help-addproduct').length == 0) {
        if ( Cookies.get("hide-help-addproduct") == 1 ) {
            jQuery('html, body').css('overflow', 'visible');
        } else {
            jQuery("html, body").scrollTop(0);
        }
    }
    //add produit tip block hide as click cross
	jQuery('#help-addproduct img').on("click", function() {
		    jQuery('#help-addproduct').hide();
            jQuery('html, body').css('overflow', 'visible');
        });
		
    // Check device size
    if(jQuery(window).width() <= 768) {
        var isMobile = true; 
        jQuery('body').addClass('isMobile');
        if(jQuery(window).width() > 425) {
            var isTablet = true; 
            jQuery('body').addClass('isTablet');
        }
    } else {
        var isMobile = false; 
        jQuery('body').removeClass('isMobile');
    }

    // Remember scroll position
    if(urlArray[1] == 'actualites') {
        if ( Cookies.get("scroll-actu") !== null && Cookies.get("hide-helps") != 1) {
            jQuery(document).scrollTop( Cookies.get("scroll-actu") );
        }
        jQuery('*').on("click", function() {
            Cookies.set("scroll-actu", jQuery(document).scrollTop());
        });
    } else if(urlArray[1] == 'catalogue' && urlArray[3] == null) {
        jQuery("html, body").scrollTop(0);
    }


    // Redirect to homepage on logo click
    jQuery('h1.logo').click(function(){
       window.location.assign(window.location.origin+"/actualites");
    });

    // User login, signup and forgotten password toggle
    jQuery("#forgotpassword-toggle").click(function(){
        jQuery('.alert-danger').hide();
        jQuery("#login-wrapper").hide();
        jQuery("#login-toggle-1").hide();
        jQuery(".intro-replay").hide();
        jQuery("#signup-toggle").hide();
        jQuery("#forgotpassword-wrapper").show();
    });
    jQuery("#login-toggle-1, #login-toggle-2").click(function(){
        jQuery('.alert-danger').hide();
        jQuery("#signup-wrapper").hide();
        jQuery("#login-toggle-1").hide();
        jQuery("#forgotpassword-wrapper").hide();
        jQuery(".intro-replay").show();
        jQuery("#login-wrapper").show();
        jQuery("#signup-toggle").show();
    });
    jQuery("#signup-toggle").click(function(){
        jQuery('.alert-danger').hide();
        jQuery("#login-wrapper").hide();
        jQuery("#signup-toggle").hide();
        jQuery(".intro-replay").hide();
        jQuery("#signup-wrapper").show();
        jQuery("#login-toggle-1").show();
    });

    // User sign up password check
    jQuery('#signup-password-confirmation').keyup(function(){
        if(jQuery('#signup-password-confirmation').val() != jQuery('#signup-password').val()){
            jQuery("#msg").html("Mots de passe différents").css("color","red");
            jQuery('#signup-form input[type=submit]').prop('disabled', true);
        } else {
            jQuery("#msg").html("Mots de passe identiques").css("color","green");
            jQuery('#signup-form input[type=submit]').prop('disabled', false);
        }
    });
    jQuery('[name=pwd-conf]').keyup(function(){
        if(jQuery('[name=pwd-conf]').val() != jQuery('[name=pwd]').val()){
            jQuery("#msg").html("Mots de passe différents").css("color","red");
            jQuery('#edit-password input[type=submit]').prop('disabled', true);
        } else {
            jQuery("#msg").html("Mots de passe identiques").css("color","green");
            jQuery('#edit-password input[type=submit]').prop('disabled', false);
        }
    });

    // Signup form check
    jQuery('#signup-form').submit(function(e) {
        e.preventDefault();
        var has_error = false;
        var errors = '<ul>'; 
        if(jQuery('#signup-cropped-picture').val() == "") {
            errors += '<li>Photo de profil manquante</li>'; 
            has_error = true;
        }
        if(jQuery('#signup-zipcode').val() != "") {
            // Check if zipcode exists
            jQuery.get("https://geo.api.gouv.fr/communes?codePostal="+jQuery('#signup-zipcode').val(), function(response) {
                if(response.length <= 0) {
                    errors += '<li>Code postal inexistant</li>'; 
                    has_error = true;
                }
                if(has_error) {
                    e.preventDefault();
                    jQuery('.alert-danger').remove();
                    errors += '</ul>'; 
                    var error_msg = '<div class="alert-danger">Ton formulaire contient au moins une erreur : '+errors+'</div>';
                    jQuery(document).scrollTop(0);
                    jQuery('#signup-wrapper').prepend(error_msg);
                } else {
                    jQuery('#signup-form')[0].submit();
                }
            });
        }
    });
    
    // Redirect on click
    jQuery('.produit, .dressing, .swapplace, .comment').click(function(e){
        if(e.toElement.nodeName == 'A'){
            window.location.assign(e.toElement.href);
        } else if(jQuery(e.toElement).parents('.social').length 
               || jQuery(e.toElement).parents('.toggle').length
               || jQuery(e.toElement).hasClass('btn')) {
            // Nothing
        } else if(e.toElement.nodeName == 'IMG' && jQuery(e.toElement.parentNode).hasClass('openChat') || jQuery(e.toElement).hasClass('openChat')) {
            // Replaced by the openChatSwapplace and openChatProduct functions
            //window.location.assign('https://' + window.location.host + '/messagerie/discussion?to=' + jQuery(e.toElement.parentNode).attr('data-userid'));
        } else if(jQuery(e.toElement).parents('#pending').length) {
            window.location.assign('https://' + window.location.host + '/?post_type=produits&p=' + jQuery(this).attr('data-id'));
        } else {
            if(jQuery(this).hasClass('produit')) {
                window.location.assign('https://' + window.location.host + '/produits/' + jQuery(this).attr('data-slug'));
            } else if(jQuery(this).hasClass('dressing')) {
                window.location.assign('https://' + window.location.host + '/dressings/' + jQuery(this).attr('data-slug'));
            } else if(jQuery(this).hasClass('swapplace')) {
                window.location.assign('https://' + window.location.host + '/swap-places/' + jQuery(this).attr('data-slug'));
            } else if(jQuery(this).hasClass('comment')) {
                window.location.assign('https://' + window.location.host + '/'+ jQuery(this).attr('data-posttype') +'/' + jQuery(this).attr('data-slug'));
            } 
        }
    });
    jQuery('.produit-min').click(function(e){
        if(e.toElement.classList[0] == 'send-product'
        || jQuery(e.toElement).parents('.product-actions').length
        || jQuery(e.toElement).hasClass('.product-actions')
        || jQuery(e.toElement).parents('.deleteproduct').length
        || jQuery(e.toElement).hasClass('.deleteproduct')){
            // Nothing
        } else if(jQuery(this).children('.pending').length){
            window.location.assign('https://' + window.location.host + '/?post_type=produits&p=' + jQuery(this).attr('data-id'));
        } else if(e.toElement.nodeName == 'A'){
            window.location.assign(e.toElement.href);
        } else {
            window.location.assign('https://' + window.location.host + '/produits/' + jQuery(this).attr('data-slug'));
        }
    });
    jQuery('.swapplace-min').click(function(e){
        if(e.toElement.classList[0] == 'send-swapplace'){
            // Nothing
        } else if(e.toElement.nodeName == 'A'){
            window.location.assign(e.toElement.href);
        } else {
            window.location.assign('https://' + window.location.host + '/swap-places/' + jQuery(this).attr('data-slug'));
        }
    });
    jQuery('.messagerie .openChat').click(function(){
        window.location.assign(jQuery(this).attr('data-link'));
    });

    // Carousels, see slick documentation
    if(isMobile){
        if(jQuery('.produit-single').children('.produit-carousel').children().length > 1){
            jQuery('.produit-single').children('.produit-carousel').slick({
                infinite: false,
                arrows: false,
                dots: true,
                adaptiveHeight: true
            });
        }
    } else {
        jQuery('.produit-carousel .carousel-item > img').each(function(){
            let item = this;
            var img = new Image();
            img.src = item.src;
            img.onload = function() {
                if(img.width >= img.height) {
                    jQuery(item).addClass('wider');
                } else {
                    jQuery(item).addClass('higher');     
                }
            }
        });
        jQuery('.show-img').click(function(){
            jQuery('main').prepend('<div class="produit-image-modal" onclick="closeModal()"><img src="'+jQuery(this).siblings('img').attr('src')+'" alt=""></div>')
        });
    }

    if(jQuery('.produit').children('.produit-carousel').children().length > 1){
        jQuery('.produit').children('.produit-carousel').slick({
            infinite: false,
            arrows: false,
            dots: true,
            adaptiveHeight: false
        });
    }

    jQuery('.map .swapplaces-caroussel-infos').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
		infinite: false,
        arrows: false,
        fade: true,
        asNavFor: '.swapplaces-caroussel',
        adaptiveHeight: true,
    });
    jQuery('.map .swapplaces-caroussel').slick({
        slidesToShow: 3,
        slidesToScroll: 3,
		infinite: false,
        centerPadding: '30px',
        arrows: true,
        asNavFor: '.swapplaces-caroussel-infos',
        centerMode: false,
        focusOnSelect: true
      });

    // Add class to swap-place carousel
    var map_imgs = jQuery('.map-bottom img');
    for(var i = 0; i < map_imgs.length; i++) {
        var item = map_imgs[i];
        const img = new Image();
        img.src = item.src;
        if(img.naturalWidth >= img.naturalHeight) {
            jQuery(item).addClass('wider');
        } else {
            jQuery(item).addClass('higher');
        }
    }

    // Set the height to width value after causel init
    jQuery('.map .swapplaces-caroussel .slick-slide').each(function(){
        jQuery(this).css('height', jQuery(this).css('width'));
    });

    // Add class to catalogue carousel
    jQuery('.catalogue .swapplace .swapplace-carousel img').each(function(){
        if(jQuery(this).width() >= jQuery(this).height()) {
            jQuery(this).addClass('wider');
        } else {
            jQuery(this).addClass('higher');     
        }
    });

    // SCope toggle modal
    jQuery('.scope-toggle').click(function(){
        jQuery('#scope-modal').css('display', 'flex');
    });
    jQuery('#scope-close').click(function(){
        jQuery('#scope-modal').hide();
    });

    // alert toggle modal
    jQuery('.alert__trigger').click(function(){
        jQuery('.alert__modal').css('display', 'flex');
    });
    jQuery('.alert__close').click(function(){
        jQuery('.alert__modal').hide();
    });

    if(isMobile == true) {
        // Hide bottom nav when keyboard is up on mobile 
        jQuery('.modal input[type=text]').focusin(function(){
            jQuery('footer').hide();
        });
        jQuery('.modal input[type=text]').blur(function(){
            jQuery('footer').css('display', 'flex');
        });
    }

    // Close sidebar 
    if(isMobile == true || isTablet == true) {
        jQuery(document).mouseup(function(e) {
            var container = jQuery('aside.profil');
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                container.hide();
                jQuery('html').css('overflow-y', 'visible');
            }
        });
    }

    // Show sidebar 
    var last_scroll = 0;
    jQuery('.profil-toggle').click(function(){
        jQuery('aside.profil').css('display', 'flex');
        last_scroll = jQuery(document).scrollTop();
        jQuery('html').css({
            'height' : '0px',
            'min-height' : '0px'
        });
        jQuery('main').css({
            'position' : 'fixed',
            'top' : '-'+last_scroll+'px',
        });
        jQuery('aside .overlay').addClass('shown');
    });
    
    // Close sidebar 
    jQuery('aside .overlay').on('click touchmove', function() {
        jQuery('aside.profil').hide();
        jQuery('html').attr('style', "");
        jQuery('main').attr('style', "");
        jQuery(document).scrollTop(last_scroll);
        jQuery(this).removeClass('shown');
    });

    // Close sidebar
    jQuery('.rs, .copyright').on('touchmove', function() {
        jQuery('aside.profil').hide();
        jQuery('html').attr('style', "");
        jQuery('main').attr('style', "");
        jQuery(document).scrollTop(last_scroll);
        jQuery('aside .overlay').removeClass('shown');
    });

    // Active class on footer links 
    if(urlArray[1] == 'actualites') {
        jQuery('footer a:first-of-type, header .desktop a:first-of-type').addClass('active');
        jQuery('footer a:last-of-type, header .desktop a:nth-of-type(2)').removeClass('active');
    } else if(urlArray[1] == 'catalogue') {
        jQuery('footer a:last-of-type, header .desktop a:nth-of-type(2)').addClass('active');
        jQuery('footer a:first-of-type, header .desktop a:first-of-type').removeClass('active');
    } else {
        jQuery('footer a, header .desktop').removeClass('active');        
    }

    // Toggle profil and website links 
    jQuery('.profil-links-switch').click(function(){
        jQuery('.website-links').hide();
        jQuery('aside .website').hide();
        jQuery('.website-links-switch span').removeClass('active');
        jQuery('.profil-links').css('display', 'flex');
        jQuery('aside .user').css('display', 'flex');
        jQuery('.profil-links-switch span').addClass('active');
    });
    jQuery('.website-links-switch').click(function(){
        jQuery('.profil-links').hide();
        jQuery('aside .user').hide();
        jQuery('.profil-links-switch span').removeClass('active');
        jQuery('.website-links').css('display', 'flex');
        jQuery('aside .website').css('display', 'flex');
        jQuery('.website-links-switch span').addClass('active');
    });

    // Toggle send more
    jQuery('#send-more-switch .produits-toggle').click(function() {
        jQuery(this).siblings().removeClass('bold');
        jQuery(this).addClass('bold');
        jQuery('.produits').show();
        jQuery('.swap-places').hide();
    });
    jQuery('#send-more-switch .swapplaces-toggle').click(function() {
        jQuery(this).siblings().removeClass('bold');
        jQuery(this).addClass('bold');
        jQuery('.swap-places').show();
        jQuery('.produits').hide();
    });
    
    // Toggle partner and user dressing
    jQuery('.partner-dressing-toggle').click(function() {
        if(jQuery(this).hasClass('bold')){
            jQuery(this).removeClass('bold');
            jQuery(this).siblings().addClass('hidden');
        } else {
            jQuery(this).addClass('bold');
            jQuery('.user-dressing-toggle').removeClass('bold');
            jQuery('.user-dressing').children('*:not(.user-dressing-toggle)').addClass('hidden');
            jQuery(this).siblings().removeClass('hidden');
        }
    });
    jQuery('.user-dressing-toggle').click(function() {
        if(jQuery(this).hasClass('bold')){
            jQuery(this).removeClass('bold');
            jQuery(this).siblings().addClass('hidden');
        } else {
            jQuery(this).addClass('bold');
            jQuery('.partner-dressing-toggle').removeClass('bold');
            jQuery('.partner-dressing').children('*:not(.partner-dressing-toggle)').addClass('hidden');
            jQuery(this).siblings().removeClass('hidden');
        }
    });
    
    // Toggle partner and user favorites swap places
    jQuery('.partner-swap-places-toggle').click(function() {
        if(jQuery(this).hasClass('bold')){
            jQuery(this).removeClass('bold');
            jQuery(this).siblings().addClass('hidden');
        } else {
            jQuery(this).addClass('bold');
            jQuery('.user-swap-places-toggle').removeClass('bold');
            jQuery('.user-swap-places').children('*:not(.user-swap-places-toggle)').addClass('hidden');
            jQuery(this).siblings().removeClass('hidden');
        }
    });
    jQuery('.user-swap-places-toggle').click(function() {
        if(jQuery(this).hasClass('bold')){
            jQuery(this).removeClass('bold');
            jQuery(this).siblings().addClass('hidden');
        } else {
            jQuery(this).addClass('bold');
            jQuery('.partner-swap-places-toggle').removeClass('bold');
            jQuery('.partner-swap-places').children('*:not(.partner-swap-places-toggle)').addClass('hidden');
            jQuery(this).siblings().removeClass('hidden');
        }
    });
    
    // Set the default catalogue listing to products
    if(window.location.pathname=='/catalogue/' && window.location.href.indexOf("#") == -1) {
        window.location.replace(window.location.href+"#produits");
        jQuery('.filters-swapplaces').hide();
        jQuery('.filters-produits').show();
        jQuery("html, body").scrollTop('0');
    }
    
    // Catalogue top nav 
    jQuery('.catalogue .share > span').html('');

    var catalogue_posts = [];
    var catalogue_tab = 'produits';

    if(getAnchor() == 'produits') {
        catalogue_posts = [];
        catalogue_tab = 'produits';
        jQuery("html, body").scrollTop('0');
        jQuery("#catalogue-produits").addClass('bold');
        jQuery("#catalogue-membres, #catalogue-swap-places").removeClass('bold');
        jQuery('#membres, #swap-places').hide();
        jQuery('#produits').show();
        jQuery('.filters-produits').show();
        jQuery('.filters-swapplaces').hide();
        jQuery('.filters-membres').hide();
        jQuery('#data-type').html('produits');
        // Init or reset carousel
        if (jQuery('.catalogue #produits, .catalogue #membres').hasClass('slick-initialized')) {
            jQuery('.catalogue #produits.slick-initialized.slick-slider').slick('setPostion');
			jQuery('.catalogue #membres.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #produits, .catalogue #membres').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: false,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                  });
            } else {
                jQuery('.catalogue #produits, .catalogue #membres').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: false,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                  });
            }          
        }
        jQuery('.produit:not(.slick-cloned)').each(function() {
            catalogue_posts.push(jQuery(this).attr('data-id')) ;
        });
    } else if(getAnchor() == 'membres') {
        catalogue_posts = [];
        catalogue_tab = 'dressings';
        jQuery(document).scrollTop(0);
        jQuery("#catalogue-membres").addClass('bold');
        jQuery("#catalogue-produits, #catalogue-swap-places").removeClass('bold');
        jQuery('#produits, #swap-places').hide();
        jQuery('#membres').show();
        jQuery('.filters-produits').hide();
        jQuery('.filters-swapplaces').hide();
        jQuery('.filters-membres').show();
        jQuery('#data-type').html('membres');
        // Init or reset carousel
        if (jQuery('.catalogue #membres').hasClass('slick-initialized')) {
            jQuery('.catalogue #membres.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #membres').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: false,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                });
            } else {
                jQuery('.catalogue #membres').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: false,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                });
            }
        }
        jQuery('.dressing:not(.slick-cloned)').each(function() {
            catalogue_posts.push(jQuery(this).attr('data-id')) ;
        });
        jQuery('.catalogue #membres .see-more').css('max-width', jQuery('.catalogue #membres .slick-slide:not(.see-more)').css('width'));
    } else if(getAnchor() == 'swapplaces') {
        catalogue_posts = [];
        catalogue_tab = 'swapplaces';
        jQuery(document).scrollTop(0);
        jQuery("#catalogue-swap-places").addClass('bold');
        jQuery("#catalogue-produits, #catalogue-membres").removeClass('bold');
        jQuery('#produits, #membres').hide();
        jQuery('#swap-places').show();
        jQuery('.filters-swapplaces').show();
        jQuery('.filters-produits').hide();
        jQuery('.filters-membres').hide();
        jQuery('#data-type').html('swap-places');
        // Init or reset carousel
        if (jQuery('.catalogue #swap-places').hasClass('slick-initialized')) {
            jQuery('.catalogue #swap-places.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #swap-places').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: false,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                });
            } else {
                jQuery('.catalogue #swap-places').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: false,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                });
            }
        }
        jQuery('.swapplace:not(.slick-cloned)').each(function() {
            catalogue_posts.push(jQuery(this).attr('data-id')) ;
        });
        jQuery('.catalogue #swap-places .see-more').css('max-width', jQuery('.catalogue #swap-places .slick-slide:not(.see-more)').css('width'));
    }

    jQuery("#catalogue-produits a").click(function(){
        jQuery("#catalogue-produits").addClass('bold');
        jQuery("#catalogue-membres, #catalogue-swap-places").removeClass('bold');
        jQuery('#membres, #swap-places').hide();
        jQuery('#produits').show();
        jQuery('.filters-swapplaces').hide();
        jQuery('.filters-membres').hide();
        jQuery('.filters-produits').show();
        jQuery('.filters').hide();
        jQuery('.filter-close').hide();
        jQuery('.filters').parent().removeClass('isOpen');
        jQuery('.filter-sort').css('margin-bottom', 0);
        jQuery('#data-type').html('produits');
        // Init or reset carousel
        if (jQuery('.catalogue #produits').hasClass('slick-initialized')) {
            jQuery('.catalogue #produits.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #produits').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                  });
            } else {
                jQuery('.catalogue #produits').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                  });
            }
        }
        jQuery('.catalogue #produits .see-more').css('max-width', jQuery('.catalogue #produits .slick-slide:not(.see-more)').css('width'));
    });

    jQuery("#catalogue-membres a").click(function(){
        jQuery("#catalogue-membres").addClass('bold');
        jQuery("#catalogue-produits, #catalogue-swap-places").removeClass('bold');
        jQuery('#produits, #swap-places').hide();
        jQuery('#membres').show();
        jQuery('.filters-swapplaces').hide();
        jQuery('.filters-produits').hide();
        jQuery('.filters-membres').show();
        jQuery('.filters').hide();
        jQuery('.filter-close').hide();
        jQuery('.filters').parent().removeClass('isOpen');
        jQuery('.filter-sort').css('margin-bottom', 0);
        jQuery('#data-type').html('membres');
        // Init or reset carousel
        if (jQuery('.catalogue #membres').hasClass('slick-initialized')) {
            jQuery('.catalogue #membres.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #membres').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                });
            } else {
                jQuery('.catalogue #membres').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                });
            }
        }
        jQuery('.catalogue #membres .see-more').css('max-width', jQuery('.catalogue #membres .slick-slide:not(.see-more)').css('width'));
    });

    jQuery("#catalogue-swap-places a").click(function(){
        jQuery("#catalogue-swap-places").addClass('bold');
        jQuery("#catalogue-produits, #catalogue-membres").removeClass('bold');
        jQuery('#produits, #membres').hide();
        jQuery('#swap-places').show();
        jQuery('.filters-swapplaces').show();
        jQuery('.filters-produits').hide();
        jQuery('.filters-membres').hide();
        jQuery('.filters').hide();
        jQuery('.filter-close').hide();
        jQuery('.filters').parent().removeClass('isOpen');
        jQuery('.filter-sort').css('margin-bottom', 0);
        jQuery('#data-type').html('swap-places');
        // Init or reset carousel
        if (jQuery('.catalogue #swap-places').hasClass('slick-initialized')) {
            jQuery('.catalogue #swap-places.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.catalogue #swap-places').slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    centerMode: true,
                    centerPadding: '30px',
                    arrows: false,
                    adaptiveHeight: false
                });
            } else {
                jQuery('.catalogue #swap-places').slick({
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                });
            }
        }
        jQuery('.catalogue #swap-places .see-more').css('max-width', jQuery('.catalogue #swap-places .slick-slide:not(.see-more)').css('width'));
    });

    // Change select font if it has a value
    if(jQuery('select').val()){
        jQuery('select').css({
            fontFamily: 'Montserrat Regular',
            color: '#1a1a1a'
        });
    } else {
        jQuery('select').css({
            fontFamily: 'Montserrat Italic',
            color: '#757575'
        });
    }
    jQuery('select').change(function(){
        if(jQuery(this).val()){
            jQuery(this).css({
                fontFamily: 'Montserrat Regular',
                color: '#1a1a1a'
            });
        } else {
            jQuery(this).css({
                fontFamily: 'Montserrat Italic',
                color: '#757575'
            });
        }
    });


    // Switch notifs messagerie
    jQuery('#messagerie-discussions').click(function() {
        jQuery(this).addClass('bold');
        jQuery(this).siblings().removeClass('bold');
        jQuery('#discussions').show();
        jQuery('#notifications').hide();
    });
    jQuery('#messagerie-notifications').click(function() {    
        jQuery(this).addClass('bold');
        jQuery(this).siblings().removeClass('bold');
        jQuery('#notifications').show();
        jQuery('#discussions').hide();
    });

    // Display number of unread discussions
    if(jQuery('.discussion.unread').length) {
        jQuery('.notifs-msg').html(jQuery('.discussion.unread').length);
     } else {
        jQuery('.notifs-msg').hide();
    }

    // Toggle discussion more
    /*jQuery('#send-more-toggle').click(function() {
        if(!isSendmoreShown) {
            jQuery('#send-more-toggle').css({
                transform : 'rotate(45deg)',
                background : '#f44336'
            });
            jQuery('#send-more').show();
            isSendmoreShown = true;
        } else {
            jQuery('#send-more-toggle').css({
                transform : 'rotate(0deg)',
                background : '#B2895D'
            });
            jQuery('#send-more').hide();
            isSendmoreShown = false;
        }
    });
    jQuery('.send-product').click(function() {
        jQuery('#send-more-toggle').css({
            transform : 'rotate(0deg)',
            background : '#B2895D'
        });
        jQuery('#send-more').hide();
        jQuery(document).scrollTop(jQuery(document).height());
    });
    jQuery('.send-swapplace').click(function() {
        jQuery('#send-more-toggle').css({
            transform : 'rotate(0deg)',
            background : '#B2895D'
        });
        jQuery('#send-more').hide();
        jQuery(document).scrollTop(jQuery(document).height());
    });*/


    // Toggle search
    jQuery('.search-toggle').click(function(){
        if(!isSearchShown) {
            jQuery('#searchform').css('display', 'flex');
            jQuery('main').css('padding-top', '300px');
            isSearchShown = true;
        } else {
            jQuery('#searchform').css('display', 'none');
            jQuery('main').css('padding-top', '68px');
            isSearchShown = false;
        }
    });
    jQuery('.search-close').click(function(){
        jQuery('#searchform').addClass('hidden');
        jQuery('main').css('padding-top', '68px');
        isSearchShown = false;
    });
    jQuery('.search-submit').click(function(){
        if(jQuery('#search').val().length > 0) {
            jQuery('#searchform').submit();            
        }
    });
    if(isMobile == true) {
        jQuery('#search').click(function(e){
            e.stopPropagation();
            jQuery('.search-options').css('display', 'block'); 
            jQuery('#searchform').css('box-shadow', '0px 4px 8px rgba(0, 0, 0, 0.25)'); 
            jQuery('.search-input').css('background', 'linear-gradient(180deg, transparent 50%, #f2f2f2 50%)'); 
        });
        jQuery('html').click(function(e){
            e.stopPropagation();
            if(jQuery(e.toElement).parents('#searchform').length == 0) {
                jQuery('.search-input').css('background', 'transparent'); 
                jQuery('.search-options').css('display', 'none'); 
                jQuery('#searchform').css('box-shadow', 'none'); 
            }
        });
    }

    //Filters
    jQuery('.filter-open > p').click(function(){
        jQuery('.filters').show();
        jQuery('.filter-close').show();
        jQuery('.sort-open').hide();
        jQuery('.filters').parent().addClass('isOpen');
        jQuery('.filter-sort').css('margin-bottom', jQuery('.filter-open').height());
    });
    jQuery('.check-all').click(function(){
        if(jQuery(this).html() != 'Décocher tout') {
            jQuery(this).parent().siblings().children('input[type=checkbox]').prop('checked', true);
            jQuery(this).html('Décocher tout');
            jQuery(this).parent().siblings().children('input[type=checkbox]').each(function(){
                jQuery(this).change();
            });
        } else {
            jQuery(this).parent().siblings().children('input[type=checkbox]').prop('checked', false);
            jQuery(this).html('Cocher tout');
            jQuery(this).parent().siblings().children('input[type=checkbox]').each(function(){
                jQuery(this).change();
            });
        }
    });
    
    jQuery('.filters input[type=checkbox], .drawer input[type=checkbox]').change(function(){
        if(jQuery(this).prop('checked') == true) {
            var is_checked = 1;
        } else {
            var is_checked = 0;            
        }
        if(jQuery(this).parent().siblings('label').length + 1 == jQuery(this).parent().siblings('label').children('input:checked').length + is_checked) {
            jQuery(this).parent().siblings('.h3').children('.check-all').html('Décocher tout');
        } else {
            jQuery(this).parent().siblings('.h3').children('.check-all').html('Cocher tout');
        }
    });

    jQuery('.filter-close').click(function(){
        jQuery('.filters').hide();
        jQuery('.filter-close').hide();
        jQuery('.sort-open').show();
        jQuery('.filters').parent().removeClass('isOpen');
        jQuery('.filter-sort').css('margin-bottom', 0);
    });

    jQuery('.filters input[type=checkbox], .filters input[type=radio]').change(function(){      
        if(jQuery(this).val() == 'À vendre') {
            jQuery('.filter-price').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-price').find('input').each(function() {
                    jQuery(this).val('');
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'femme') {
            jQuery('.filter-category-femme').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-category-femme').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'accessoires') {
            jQuery('.filter-subcategory-accessoires').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-accessoires').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'bijoux') {
            jQuery('.filter-subcategory-bijoux').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-bijoux').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'chaussures') {
            jQuery('.filter-subcategory-chaussures').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-chaussures').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
            jQuery('.filter-taille-femme-chaussures').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-taille-femme-chaussures').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'lingerie') {
            jQuery('.filter-subcategory-lingerie').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-lingerie').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'makeup') {
            jQuery('.filter-subcategory-makeup').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-makeup').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'sacs') {
            jQuery('.filter-subcategory-sacs').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-sacs').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'sports') {
            jQuery('.filter-subcategory-sports').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-sports').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'vetements') {
            jQuery('.filter-subcategory-vetements').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-subcategory-vetements').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
            jQuery('.filter-taille-femme-vetements').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-taille-femme-vetements').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        if(jQuery(this).val() == 'enfant') {
            jQuery('.filter-category-enfant').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-category-enfant').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
            jQuery('.filter-taille-enfant-vetements').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-taille-enfant-vetements').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
            jQuery('.filter-taille-enfant-chaussures').toggle();
            if(jQuery(this).prop('checked') == false) {
                jQuery('.filter-taille-enfant-chaussures').find('input:checked').each(function() {
                    jQuery(this).prop('checked', false);
                    jQuery(this).change();
                });
            }
        }
        filterPosts( catalogue_posts, catalogue_tab, jQuery(this));
        jQuery('.filter-sort').css('margin-bottom', jQuery('.filter-open').height());
    });

    jQuery('.filter-submit').click(function(){
        jQuery('.filters input:checked').each(function() {
            if(jQuery(this).parents().css('display') == 'none') {
                jQuery(this).prop('checked', false);
            }
        });
        jQuery('.filters input[type=text]').each(function() {
            if(jQuery(this).parents().css('display') == 'none' ||
            jQuery(this).val() == '' || jQuery(this).val()== null) {
                jQuery(this).remove();
            }
        });
        jQuery('.filter-sort').css('margin-bottom', 0);
        jQuery('#filterform').submit();
    });

    // Advanced search
    jQuery('input[type=text][name=asf_scope]').focus(function() {
        jQuery('input[type=radio][name=asf_scope]:checked').prop('checked', false);
    });

    jQuery('#advanced-search-form input[type=checkbox], #advanced-search-form input[type=radio]').change(function(){
        if(jQuery(this).attr('name') == 'asf_scope') {
            jQuery('input[type=text][name=asf_scope]').val('');
        }

        if(jQuery(this).val() == 'produit') {
            jQuery('.asf-produits').toggle();
            if(jQuery('.asf-swapplaces').css('display') != 'none') {
                jQuery('.asf-swapplaces').toggle();
            }
        }
        if(jQuery(this).val() == 'swapplace') {
            jQuery('.asf-swapplaces').toggle();
            if(jQuery('.asf-produits').css('display') != 'none') {
                jQuery('.asf-produits').toggle();
            }
        }
        if(jQuery(this).val() == 'sell') {
            jQuery('.asf-price').toggle();
        }
        if(jQuery(this).val() == 'femme') {
            unlock('.asf-category-femme');
            lock('.asf-category-enfant');
            jQuery('.asf-taille-enfant-vetements').css('display', 'none');
            jQuery('.asf-taille-enfant-chaussures').css('display', 'none');
        }
        if(jQuery(this).val() == 'accessoires') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-accessoires').show();
        }
        if(jQuery(this).val() == 'bijoux') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-bijoux').show();
        }
        if(jQuery(this).val() == 'chaussures') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-chaussures').show();
            jQuery('.asf-taille-femme-chaussures').show();
        }
        if(jQuery(this).val() == 'lingerie') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-lingerie').show();
        }
        if(jQuery(this).val() == 'makeup') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-makeup').show();
        }
        if(jQuery(this).val() == 'sacs') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-sacs').show();
        }
        if(jQuery(this).val() == 'sports') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-sports').show();
        }
        if(jQuery(this).val() == 'vetements') {
            jQuery('.asf-subcategory').hide();
            jQuery('.asf-taille').hide();
            jQuery('.asf-subcategory-vetements').show();
            jQuery('.asf-taille-femme-vetements').show();
        }
        if(jQuery(this).val() == 'enfant') {
            lock('.asf-category-femme');
            jQuery('.asf-subcategory-accessoires').css('display', 'none');    
            jQuery('.asf-subcategory-bijoux').css('display', 'none');
            jQuery('.asf-subcategory-chaussures').css('display', 'none');
            jQuery('.asf-taille-femme-chaussures').css('display', 'none');
            jQuery('.asf-subcategory-lingerie').css('display', 'none');
            jQuery('.asf-subcategory-makeup').css('display', 'none');
            jQuery('.asf-subcategory-sacs').css('display', 'none');
            jQuery('.asf-subcategory-sports').css('display', 'none');
            jQuery('.asf-subcategory-vetements').css('display', 'none');
            jQuery('.asf-taille-femme-vetements').css('display', 'none');
            unlock('.asf-category-enfant');
            jQuery('.asf-taille-enfant-vetements').toggle();
            jQuery('.asf-taille-enfant-chaussures').toggle();
        }
    });

    jQuery('.asf-submit').click(function(){
        jQuery('#advanced-search-form input:checked').each(function() {
            if(jQuery(this).parents().css('display') == 'none') {
                jQuery(this).prop('checked', false);
            }
        });
        jQuery('#advanced-search-form input[type=text]').each(function() {
            if(jQuery(this).parents().css('display') == 'none' ||
            jQuery(this).val() == '' || jQuery(this).val()== null) {
                jQuery(this).remove();
            }
        });
        jQuery('#advanced-search-form').submit();
    });
    
    // Display number of item in send more drawer
    if(jQuery('#send-more .drawer').length > 0) {
        jQuery('#send-more .drawer').each(function(){
            if(jQuery(this).children('.number').html() == '0') {
                jQuery(this).addClass('locked');
            }
        });
    }
    var send_more_expand_html = "";
    jQuery('.drawer .expand').click(function(){
        if(jQuery(this).parent().hasClass('closed')) {
            send_more_expand_html = jQuery(this).html();
            jQuery(this).parent().removeClass('closed');
            jQuery(this).html('<img src="https://'+window.location.host+'/wp-content/themes/Swap-Chic/assets/images/arrowbot-white.svg">');
            jQuery(this).children('img').css('transform', 'rotate(180deg)');
        } else if(!jQuery(this).parent().hasClass('locked')) {
            jQuery(this).parent().addClass('closed');
            jQuery(this).html(send_more_expand_html);
        }
    });
    
    jQuery('#addproduct-form .expand').click(function(){
        if(jQuery(this).parent().hasClass('closed')) {
            jQuery(this).parent().removeClass('closed');
            jQuery(this).children('img').css('transform', 'rotate(180deg)');
        } else if(!jQuery(this).parent().hasClass('locked')) {
            jQuery(this).parent().addClass('closed');
            jQuery(this).children('img').css('transform', 'rotate(0deg)');
        }
    });

    // Function to lock a drawer
    function lock(field) {
        jQuery(field).addClass('locked');
        jQuery(field+' .expand img').attr('src', window.location.origin + '/wp-content/themes/Swap-Chic/assets/images/lock.svg');
        jQuery(field+' .expand img').css('transform', 'rotate(0deg)');
    }
   
    // Function to unlock a drawer
    function unlock(field) {
        jQuery(field).removeClass('locked');
        jQuery(field+' .expand img').attr('src', window.location.origin + '/wp-content/themes/Swap-Chic/assets/images/arrowbot-white.svg');
        jQuery(field+' .expand img').css('transform', 'rotate(180deg)');
    }

    function hasSize(value) {
        if(jQuery('.step-taille.'+value).length) {
            return true;
        } else {
            return false;
        }
    }

    function hasSubcategories(value) {
        if(jQuery('.step-sous-categorie.'+value).length) {
            return true;
        } else {
            return false;
        }        
    }

    // Rotate a roduct image 90deg to the right and display it
    jQuery('.turn-right').click(function(){
        var element = this;
        var src = jQuery(this).parent().siblings('.custom-file-upload').find('input[type=hidden]').val();
        rotate90(src,function(res){
            var img_res = document.createElement('img')
            img_res.src = res;
            img_res.onload = function() {
                jQuery(element).parent().siblings('.custom-file-upload').find('img').remove();
                var width = img_res.width;
                var height = img_res.height;
                var css_class = "wider"; 

                if (width > height) {
                    css_class = "wider";
                } else {
                    css_class = "higher";
                }

                jQuery(element).parent().siblings('.custom-file-upload').children('div').append( jQuery('<img>').attr('src', res).addClass(css_class))
                jQuery(element).parent().siblings('.custom-file-upload').find('input[type=hidden]').val(res);
            }
         })
    });


    // Product add
    jQuery('input[name=image-front], input[name=image-back], input[name=image-label]').change(function(){
        if(jQuery('input[name=image-front]').val() != "" && jQuery('input[name=image-back]').val() != "" && jQuery('input[name=image-label]').val() != "") {
            unlock('.step-action');
        }
    }); 

    jQuery('#addproduct-swap, #addproduct-sell').change(function() {
        if(jQuery('#addproduct-sell').prop('checked')) {
            jQuery('.step-price').removeClass('hidden');
            unlock('.step-price');
            if(jQuery('#addproduct-price').val().length > 0) {
                unlock('.step-categorie-parente');
            } else {
                lock('.step-categorie-parente');
            }
        }
        if(jQuery('#addproduct-swap').prop('checked') && jQuery('#addproduct-sell').prop('checked') == false) {
            lock('.step-price');
            jQuery('.step-price').addClass('hidden');
            unlock('.step-categorie-parente');
        }
        if(jQuery('#addproduct-swap').prop('checked') == false && jQuery('#addproduct-sell').prop('checked') == false) {
            jQuery('.step-price').removeClass('hidden');
            lock('.step-price');
            lock('.step-categorie-parente');
        }
    });

    jQuery('#addproduct-swap, #addproduct-sell').change(function() {
        if(jQuery('#addproduct-sell').prop('checked')) {
            jQuery('#addproduct-price').removeClass('hidden');
            jQuery(this).parent().siblings('.price-title').removeClass('hidden');
        }
        if(jQuery('#addproduct-swap').prop('checked') && jQuery('#addproduct-sell').prop('checked') == false) {
            jQuery('#addproduct-price').addClass('hidden');
            jQuery(this).parent().siblings('.price-title').addClass('hidden');
        }
        if(jQuery('#addproduct-swap').prop('checked') == false && jQuery('#addproduct-sell').prop('checked') == false) {
            jQuery('#addproduct-price').addClass('hidden');
            jQuery(this).parent().siblings('.price-title').addClass('hidden');
        }
    });

    jQuery('#addproduct-price').keyup(function() {
        if(jQuery('#addproduct-price').val().length > 0) {
            unlock('.step-categorie-parente');
        } else {
            lock('.step-categorie-parente');
        }
    });

    jQuery('#addproduct-femme').change(function() {
        if(jQuery(this).prop('checked')) {
            jQuery('.step-categorie.femme').removeClass('hidden');
            unlock('.step-categorie.femme');
            jQuery('.step-categorie.femme').removeClass('closed');
            jQuery('.step-categorie.enfant').addClass('hidden');
            lock('.step-categorie.enfant');
        } else {
            jQuery('.step-categorie.enfant').removeClass('hidden');
            lock('.step-categorie.enfant');
            jQuery('.step-categorie.enfant').removeClass('closed');
            jQuery('.step-categorie.femme').addClass('hidden');
            lock('.step-categorie.femme');
        }
    });

    jQuery('#addproduct-enfant').change(function() {
        if(jQuery(this).prop('checked')) {
            jQuery('.step-categorie.enfant').removeClass('hidden');
            unlock('.step-categorie.enfant');
            jQuery('.step-categorie.enfant').removeClass('closed');
            jQuery('.step-categorie.femme').addClass('hidden');
            lock('.step-categorie.femme');
        } else {
            jQuery('.step-categorie.femme').removeClass('hidden');
            unlock('.step-categorie.femme');
            jQuery('.step-categorie.femme').removeClass('closed');
            jQuery('.step-categorie.enfant').addClass('hidden');
            lock('.step-categorie.enfant');
        }
    });

    jQuery('#addproduct-category-femme, #addproduct-category-enfant').change(function(){
        lock('.step-sous-categorie');
        jQuery('.step-sous-categorie').addClass('hidden');
        lock('.step-taille');
        jQuery('.step-taille').addClass('hidden');
        jQuery('.step.'+jQuery(this).val()).removeClass('hidden');
        
        if(hasSubcategories(jQuery(this).val())) {
            unlock('.step-sous-categorie.'+jQuery(this).val());
        } else if(hasSize(jQuery(this).val())) {
            unlock('.step-taille.'+jQuery(this).val());
        } else {
            unlock('.step-etat');
        }
    });
    
    jQuery('select[name=subcategory]').change(function(){
        var val = jQuery(this).attr('id').substr(jQuery('#addproduct-subcategory-sacs').attr('id').lastIndexOf('-') + 1);
        if(hasSize(val)) {
            unlock('.step-taille.'+val);
        } else {
            unlock('.step-etat');
        }
    });
    
    jQuery('select[name=taille]').change(function(){
        unlock('.step-etat');
    });
    
    jQuery('input[name=etat]').change(function(){
        unlock('.step-marque');
    });
    
    jQuery('input[name=marque]').keyup(function(){
        if(jQuery('input[name=marque]').val().length > 0) {
            jQuery('.step-optional').addClass('closed');
            jQuery('.step-optional').removeClass('locked');
            jQuery('.step-optional'+' .expand img').attr('src', window.location.origin + '/wp-content/themes/Swap-Chic/assets/images/arrowbot-white.svg');
            unlock('.step-confirmation');
        } else {
            lock('.step-optional');
            lock('.step-confirmation');
        }
    });

    jQuery('#addproduct-form').submit(function(e) {
        e.preventDefault();
        var has_error = false;
        var errors = '<ul>'; 
        jQuery('#addproduct-form .step').each(function(){
            jQuery(this).removeClass('error');
            if(jQuery(this).hasClass('hidden')){
                jQuery(this).find('input[type=checkbox], input[type=radio]').prop('checked', false);
                jQuery(this).find('input[type=text], textarea, select').val('');
            } else if(jQuery(this).hasClass('required')) {
                if(jQuery(this).find('input[type=checkbox], input[type=radio]').length > 0) {
                    if(jQuery(this).find('input:checked').length == 0){
                        errors += '<li>'+jQuery(this).find('.h2').html()+'</li>'; 
                        jQuery(this).addClass('error');
                        has_error = true;
                    }
                } else if(jQuery(this).find('input[type=hidden], input[type=text], textarea, select').length > 0) {
                    if(jQuery(this).find('input[type=hidden], input[type=text], textarea, select').val() == '') {
                        errors += '<li>'+jQuery(this).find('.h2').html()+'</li>'; 
                        jQuery(this).addClass('error');
                        has_error = true;
                    }
                }
            }
        });
        if(has_error) {
            e.preventDefault();
            errors += '</ul>'; 
            var error_msg = '<p class="error_msg">Le(s) champ(s) suivant(s) comprennent une erreur : '+errors+'</p>';
            jQuery(document).scrollTop(0);
            jQuery('.addproduct-wrapper').prepend(error_msg);
        } else {
            jQuery('#addproduct-form')[0].submit();
        }
    });

    jQuery('.share').click(function(){
        var element = this;
        jQuery(element).parent().addClass('open');
        jQuery(element).parent().css({
            'marginBottom': '46px',
            'borderRadius': '20px 20px 0 0'
        });
        if(jQuery(element).parents('.produit-single').length ) {
            jQuery(element).parent().siblings().hide();
            jQuery(element).parents('.user-wrapper').css({
                'position' : 'initial',
                'align-items': 'flex-start'
            });
            jQuery(element).parents('.user-wrapper').siblings('.infos-wrapper').children('.infos').css({
                'margin-bottom': '0px'
            });
            jQuery(document).scrollTop(window.innerHeight);
        } else if(jQuery(element).parents('.swapplace-single').length ) {
            jQuery(element).parent().siblings().hide();
            jQuery(element).parents('.bottom-swapplace').css({
                'position' : 'initial',
                'align-items': 'flex-start'
            });
            jQuery(element).parents('.bottom-swapplace').siblings('.infos-wrapper').children('.description').css({
                'margin-bottom': '0px'
            });
            jQuery(document).scrollTop(window.innerHeight);
        } else if(jQuery(element).parents('.dressing-single').length ) {
            jQuery(element).parent().siblings().hide();
            jQuery(element).parents('.bottom-dressing').css({
                'position' : 'initial',
                'align-items': 'flex-start'
            });
            jQuery(element).parents('.bottom-dressing').siblings('.content').css({
                'margin-bottom': '0px'
            });
            jQuery(document).scrollTop(window.innerHeight);
        } else {
            //jQuery(window).scrollTop(jQuery(element).offset().top - 20);
        }
        jQuery(element).children('.addtoany-wrapper').css('display', 'flex');
    });

    jQuery('.alerte > p').click(function(){
        jQuery(this).siblings('.modal').css('display', 'flex');
    });

    jQuery('.alerte .modal .close').click(function(){
        jQuery(this).parents('.modal').css('display', 'none');
    });

    jQuery('#cdc .h2').click(function(){
        if(jQuery(this).children('.expand').html() == '+') {
            jQuery(this).siblings('.produitcdc').show();
            jQuery(this).children('.expand').html('-');
        } else {
            jQuery(this).siblings('.produitcdc').hide();
            jQuery(this).children('.expand').html('+');
        }
    });

    jQuery('#send-invitation-submit').click(function(){
        jQuery('#send-invitation').submit();
    });

    jQuery('#send-suggestion-submit').click(function(){
        var is_complete = true;
        jQuery('#send-suggestion input').each(function(){
            if(jQuery(this).val() == '' || jQuery(this).val() == null) { 
                is_complete = false;
            }
        });
        if(is_complete) {
            jQuery('#send-suggestion').submit();
        } else {
            jQuery('.msg').empty();
            jQuery('.msg').append('<div class="alert-danger">Votre formulaire est incomplet.</div>')
        }
    });

    if(urlArray[1] == 'sign-in'
    || urlArray[1] == '') {
        if(window.location.search == '') {
            jQuery('header').remove();
        }
    }

    // Intro 
    jQuery('.intro-presentation').slick({
        prevArrow: jQuery('.prev')[0],
        nextArrow: jQuery('.next')[0],
        //cssEase: 'ease-in',
        draggable: false,
        infinite: false,
        swipe: false,
        touchMove: false
    });
    jQuery('.intro-presentation').slick('slickGoTo', 0)

    jQuery('.intro-presentation .slick-slide').css('height', jQuery(window).height() - 54)
    
    jQuery('.intro-actions .next').click(function(){
        var intro_position = jQuery('.intro-presentation').slick('slickCurrentSlide');
        if(intro_position > 0) {
            jQuery('.intro-actions .prev').removeClass('hidden');
        } else {
            jQuery('.intro-actions .prev').addClass('hidden');
        }
        if(intro_position == 1) {
            document.getElementById("video").controls = true;
        }
        
        if(intro_position == 2) {
            jQuery('.intro-presentation .intro-tutorial').css({
                'height': 'auto',
                'margin-bottom': '54px'
            });
            jQuery('.intro-actions .next').click(function(){
                Cookies.set("intro_seen", 1, { expires: 36500 });
                window.location.assign(window.location.origin+"/sign-in");      
            });
        } else {
            jQuery('.intro-presentation .intro-tutorial').css('height', jQuery(window).height() - 54);
        }

        if(intro_position == 3) {
            Cookies.set("intro_seen", 1, { expires: 36500 });
            window.location.assign(window.location.origin+"/sign-in");      
        }
    });

    jQuery('.intro-actions .prev').click(function(){
        var intro_position = jQuery('.intro-presentation').slick('slickCurrentSlide');
        if(intro_position == 0) {
            jQuery('.intro-actions .prev').addClass('hidden');
        }
        if(intro_position == 1) {
            document.getElementById("video").controls = true;
        } else {
            document.getElementById("video").pause()
        }
        if(intro_position == 2) {
            jQuery('.intro-presentation .intro-tutorial').css({
                'height': 'auto',
                'margin-bottom': '54px'
            });
            jQuery('.intro-actions .next').click(function(){
                Cookies.set("intro_seen", 1, { expires: 36500 });
                window.location.assign(window.location.origin+"/sign-in");      
            });
        } else {
            jQuery('.intro-presentation .intro-tutorial').css('height', jQuery(window).height() - 54);
        }
    });

    jQuery('#intro-title img').animate({opacity: 1}, 1200);
    jQuery('.intro-actions').delay(1200).animate({bottom: 0}, 600);

    jQuery('.intro-replay').click(function(e){
        e.preventDefault();
        Cookies.set("intro_seen", 0, { expires: 36500 });
        window.location.assign(window.location.origin);
    });

    // Product deletion process
    jQuery('.product-actions .delete').click(function(){
        jQuery(this).parents('.produit-min').next('.deleteproduct').show();
    });
    jQuery('.deleteproduct .cancel').click(function(){
        jQuery(this).parents('.deleteproduct').children('.confirm-delete').show();
        jQuery(this).parents('.deleteproduct').children('.confirm-swap').addClass('hidden');
        jQuery(this).parents('.deleteproduct').children('.confirm-sell').addClass('hidden');
        jQuery(this).parents('.deleteproduct').children('.confirm-swap-2').addClass('hidden');
        jQuery(this).parents('.deleteproduct').hide();
    });
    jQuery('.delete-actions .delete-swap').click(function(){
        jQuery(this).parents('.deleteproduct').children('.confirm-delete').hide();
        jQuery(this).parents('.deleteproduct').children('.confirm-swap').removeClass('hidden');
    });
    jQuery('.confirm-swap .confirm').click(function(){
        jQuery(this).parents('.deleteproduct').children('.confirm-swap').addClass('hidden');
        jQuery(this).parents('.deleteproduct').children('.confirm-swap-2').removeClass('hidden');
        jQuery('#swap-product').html('<option value="" disabled selected>Selectionne un produit...</option>');
        jQuery.get("https://"+window.location.host+"/wp-json/wp/v2/produits/?author="+jQuery('#swap-partners').val(), function(produits) {
            for(var i = 0; i < produits.length; i++) {
                jQuery('#swap-product').append('<option value="'+produits[i].id+'">'+produits[i].title.rendered+'</option>');
            }
        });
    });
    jQuery('.delete-actions .delete-sell').click(function(){
        jQuery(this).parents('.deleteproduct').children('.confirm-delete').hide();
        jQuery(this).parents('.deleteproduct').children('.confirm-sell').removeClass('hidden');
    });

    // Homepage help bubbles
    if ( Cookies.get("hide-helps") != 1 && urlArray[1] == 'actualites') {
        jQuery("html, body").scrollTop(0);
        jQuery("html, body").css('overflow', 'hidden');
        jQuery('header > *, footer > *, main.actualites > *:not(.top):not(.help), .top .filter-sort').addClass('blurred');
        jQuery('#help-geo').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-helps", 1, { expires: 36500 });
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery('*').removeClass('blurred');  
                    jQuery("html, body").css('overflow', 'visible');
                });
                jQuery(this).fadeOut();
            } else {
                jQuery("html, body").css('overflow', 'hidden');
                jQuery(this).fadeOut();
                jQuery('.top .filter-sort').removeClass('blurred');
                jQuery('main.actualites .top').addClass('blurred');
                jQuery('footer > a:first-child').removeClass('blurred');
                jQuery('#help-fil').fadeIn();
            }
        });
        jQuery('#help-fil').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-helps", 1, { expires: 36500 });
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery('*').removeClass('blurred');  
                    jQuery("html, body").css('overflow', 'visible');
                });
                jQuery(this).fadeOut();
            } else {
                jQuery(this).fadeOut();
                jQuery('footer > a:first-child').addClass('blurred')
                jQuery('footer > a:last-child').removeClass('blurred')
                jQuery('#help-catalogue').fadeIn();
            }
        });
        jQuery('#help-catalogue').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-helps", 1, { expires: 36500 });
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery('*').removeClass('blurred');  
                    jQuery("html, body").css('overflow', 'visible');
                });
                jQuery(this).fadeOut();
            } else {
                jQuery(this).fadeOut();
                jQuery('footer > *').addClass('blurred');
                jQuery('header > *').removeClass('blurred');
                jQuery('header > nav > *:not(.add-product-link)').addClass('blurred');
                jQuery('#help-ajoutproduit').fadeIn();
            }
        });
        jQuery('#help-ajoutproduit').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-helps", 1, { expires: 36500 });
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery('*').removeClass('blurred');  
                    jQuery("html, body").css('overflow', 'visible');
                });
                jQuery(this).fadeOut();
            } else {
                jQuery(this).fadeOut();
                jQuery('header > nav > .add-product-link').addClass('blurred');
                var first_sp = jQuery('#thread .swapplace')[0];
                var scroll = first_sp.offsetTop - 68;
                jQuery("html, body").animate({scrollTop: scroll}, 300, function(){
                    jQuery("html, body").css('overflow', 'hidden');
                    jQuery('main.actualites > *').removeClass('blurred');
                    jQuery('main.actualites #thread > *').not(first_sp).addClass('blurred');
                    jQuery(first_sp).children('*:not(.infos-wrapper)').addClass('blurred');
                    jQuery(first_sp).children('.infos-wrapper').children('*:not(.title-action)').addClass('blurred');
                    jQuery(first_sp).find('.title-action').children('h3').addClass('blurred');
                    jQuery('#help-map').css('top', jQuery(window).scrollTop() + jQuery(first_sp).children('.swapplace-carousel').height() + jQuery(first_sp).find('.title-action').height() + 68 + 40 + 'px');
                    jQuery('#help-map').fadeIn();
                });
            }
        });
        jQuery('#help-map').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-helps", 1, { expires: 36500 });
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery('*').removeClass('blurred');  
                    jQuery("html, body").css('overflow', 'visible');
                });
                jQuery(this).fadeOut();
            } else {
                jQuery(this).fadeOut();
                jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                    jQuery("html, body").css('overflow', 'visible');
                    jQuery('main.actualites #thread > *').removeClass('blurred');
                    var first_sp = jQuery('#thread .swapplace')[0];
                    jQuery(first_sp).find('*').removeClass('blurred');
                    jQuery('*').removeClass('blurred');
                    // jQuery('footer > a:first-of-type').removeClass('blurred');
                    // jQuery('footer > a:last-of-type').removeClass('blurred');
                    Cookies.set("hide-helps", 1, { expires: 36500 });
                });   
            }
        });
    } else {
        jQuery("html, body").css('overflow', 'visible');
    }
   // Product add help bubble
    /*if ( Cookies.get("hide-help-addproduct") != 1 && urlArray[1] == 'ajouter-produit') {
        jQuery("html, body").scrollTop(0);
        jQuery("html, body").css('overflow', 'hidden');
        jQuery('.addproduct-title > *, #addproduct-form > *:not(.help)').addClass('blurred');
        jQuery('#help-addproduct').click(function(e){
            if(e.toElement.type == "checkbox") {
                Cookies.set("hide-help-addproduct", 1, { expires: 36500 });
            }
            jQuery("html, body").animate({scrollTop: 0}, 300, function(){
                jQuery('*').removeClass('blurred');  
                jQuery("html, body").css('overflow', 'visible');
            });
            jQuery(this).fadeOut();
        });
    } else {
        jQuery("html, body").css('overflow', 'visible');
    }*/
    jQuery('[name=hide-help]').change( function (){
        Cookies.set("hide-helps", 1, { expires: 36500 });
        jQuery(this).parents('.help').hide();
        jQuery("html, body").animate({scrollTop: 0}, 300, function(){
            jQuery('*').removeClass('blurred');  
            jQuery("html, body").css('overflow', 'visible');
        });
    })

    // Show video controls 
    if(jQuery('#video').length) {
        document.getElementById("video").controls = true;
    }

    // Edit product picture
    jQuery('.editer-produit .images .hasBefore img').each(function(){
        jQuery(this).on('load', function(){
            var img = document.createElement("img");
            img.src = jQuery(this).attr('src');
            var img_id = jQuery(this).parent().attr('id');
            var input_index = img_id.substr(img_id.lastIndexOf('-') + 1, img_id.length);
            if(jQuery('#tmp-canvas-'+input_index).length) {
                jQuery('#tmp-canvas-'+input_index).remove();
            }
            jQuery('#image-'+input_index).prepend('<canvas id="tmp-canvas-'+input_index+'"></canvas>');
            var canvas = document.getElementById('tmp-canvas-'+input_index);
            var ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0);

            var width = img.width;
            var height = img.height;
            var css_class = "wider"; 

            if (width > height) {
                css_class = "wider";
            } else {
                css_class = "higher";
            }

            canvas.width = width;
            canvas.height = height;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);
            jQuery('input[name=addproduct-image-'+input_index+']').prop('required', false);
            var dataurl = canvas.toDataURL("image/jpeg");
            jQuery(canvas).addClass(css_class);
            jQuery('#image-'+input_index).children('img').remove();
            jQuery('#image-'+input_index).append( jQuery('<img>').attr('src', dataurl).addClass(css_class));
            jQuery('input[name=image-'+input_index+']').val(dataurl);
            jQuery('input[name=image-'+input_index+']').change();
            jQuery('canvas').remove();
        });
    });

    // Check product picture edit form
    jQuery('#edit-product-pictures').submit(function(e) {
        e.preventDefault();
        if(jQuery('#image-front input').val().length > 0 || jQuery('#image-back input').val().length > 0 || jQuery('#image-label input').val().length > 0) {
            jQuery('#image-front input').prop('required', false);
            jQuery('#image-back input').prop('required', false);
            jQuery('#image-label input').prop('required', false);
            jQuery('#edit-product-pictures')[0].submit();
        }
    });

    // Product validation process
    jQuery('.produit .admin-actions .validate, .produit-single .admin-actions .validate').click(function() {
        var form = jQuery(this).parents('form')[0];
        form.submit();
    });
	
	//add vip slider
	if (jQuery('.actualites #produits-bloger').hasClass('slick-initialized')) {
            jQuery('.actualites #produits-bloger.slick-initialized.slick-slider').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.actualites #produits-bloger').slick({
                    dots: true,
					infinite: false,
					mobileFirst: true,
					speed: 500,
					slidesToShow: 1,
                    slidesToScroll: 1,
                    centerPadding: '30px',
                    arrows: false
                });
            }
        }
		
	//add banner slider in home page
	/*if (jQuery('.actualites #topBanner').hasClass('slick-initialized')) {

            jQuery('.actualites #topBanner.slick-initialized.slick-slider').slick('setPostion');

        } else {

            if(isMobile) {

                jQuery('.actualites #topBanner').slick({
                    
					dots: true,
					infinite: false,
					mobileFirst: true,
					speed: 500,
					slidesToShow: 1,
                    slidesToScroll: 1,
                    centerPadding: '30px',
                    arrows: false

                });

            } else {

                jQuery('.actualites #topBanner').slick({                  
					
					slidesToShow: 1,

                    slidesToScroll: 1,

                    infinite: false,

                    centerMode: false,

                    arrows: false,

                    adaptiveHeight: false,
					
					autoplay: true,
					
					autoplaySpeed: 2000

                });
            }
        }*/
	//add home page member slider
    if (jQuery('.actualites #membresHome').hasClass('slick-initialized')) {
            jQuery('.actualites #membresHome').slick('setPostion');
        } else {
            if(isMobile) {
                jQuery('.actualites #membresHome').slick({
                    slidesToShow: 7,
                    slidesToScroll: 7,
                    infinite: false,
                    centerMode: false,
					centerPadding: '30px',
                    arrows: true,
                    adaptiveHeight: false
                  });
            } else {
                jQuery('.actualites #membresHome').slick({
                    slidesToShow: 10,
                    slidesToScroll: 10,
                    infinite: false,
                    centerMode: false,
                    arrows: true,
                    adaptiveHeight: false
                  });
            }          
        }
});