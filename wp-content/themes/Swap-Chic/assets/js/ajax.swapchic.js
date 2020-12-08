/*
* Likes and dislikes a post
* Parameters : string type, int id, HTMLElement element
* Return : none
*/
function like(type, id, element) {
    if(jQuery(element).parents('[data-id]').hasClass('liked')) {
        // Dislike post
        jQuery(element).parents('[data-id]').removeClass('liked');
        // Decrease number of likes
        var likes = parseInt(jQuery(element).children('span').html()) - 1;
        jQuery(element).children('span').html(likes);
        jQuery(element).children('img').attr('src', window.location.origin+'/wp-content/themes/Swap-Chic/assets/images/likes.svg');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxLike',
                'type' : 'dislike',
                'post_id': id,
                'post_type': type
            },
            function(){
                // nothing
            }
        );
    } else {
        // Like post
        jQuery(element).parents('[data-id]').addClass('liked');
        // Increase number of likes
        var likes = parseInt(jQuery(element).children('span').html()) + 1;
        jQuery(element).children('span').html(likes);
        jQuery(element).children('img').attr('src', window.location.origin+'/wp-content/themes/Swap-Chic/assets/images/liked.svg');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxLike',
                'type' : 'like',
                'post_id': id,
                'post_type': type
            },
            function(){
                // nothing
            }
        );
    }
} 

/*
* Likes and dislikes acomment
* Parameters : int id, int comment_id, HTMLElement element
* Return : none
*/
function likeComment(id, comment_id, element) {
    if(jQuery(element).parents('[data-id]').hasClass('liked')) {
        // Dislike comment
        jQuery(element).parents('[data-id]').removeClass('liked');
        // Decrease number of likes
        var likes = parseInt(jQuery(element).children('span').html()) - 1;
        jQuery(element).children('span').html(likes);
        jQuery(element).children('img').attr('src', window.location.origin+'/wp-content/themes/Swap-Chic/assets/images/likes.svg');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxLike',
                'type' : 'dislike',
                'post_id': id,
                'post_type': 'comments',
                'comment_id': comment_id
            },
            function(response){
                // console.log(response);
            }
        );
    } else {
        // Like comment
        jQuery(element).parents('[data-id]').addClass('liked');
        // Increase number of likes
        var likes = parseInt(jQuery(element).children('span').html()) + 1;
        jQuery(element).children('span').html(likes);
        jQuery(element).children('img').attr('src', window.location.origin+'/wp-content/themes/Swap-Chic/assets/images/liked.svg');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxLike',
                'type' : 'like',
                'post_id': id,
                'post_type': 'comments',
                'comment_id': comment_id
            },
            function(response){
                // Nothing
            }
        );
    }
} 

/*
* Post a comment
* Parameters : string type,, int id, HTMLElement element
* Return : none
*/
function comment(type, id, element) {
    if( jQuery(element).siblings('textarea').val().length != 0) {
        var content = jQuery(element).siblings('textarea').val();
        jQuery(element).siblings('textarea').val('');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxComment',
                'post_id': id,
                'content' : content
            },
            function(response){
                // Append new comment to comment thread
                jQuery(element).parents('.comment-thread-wrapper').children('.comment-thread').prepend(response);
                // Increase number of comments
                var comments = parseInt(jQuery(element).parents('.social').children('.comments').children('span').html()) + 1;
                jQuery(element).parents('.social').children('.comments').children('span').html(comments);
                jQuery(element).siblings('textarea').val('');
            }
        );
    }
}

/*
* Post a comment child
* Parameters : string type, int id, int comment_id, HTMLElement element
* Return : none
*/
function answerComment(type, id, comment_id, element) {
    var content = jQuery(element).siblings('textarea').val();
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxAnswerComment',
            'post_id': id,
            'comment_id': comment_id,
            'content' : content
        },
        function(response){
            jQuery(element).parents('.comment-thread-wrapper[data-comment]').each(function(){
                // Prepend the comment child to the right parent
                if(jQuery(this).attr('data-comment') == comment_id) {
                    jQuery(this).children('.comment-thread').prepend(response);
                }
            });
            // Increase number of comments
            var comments = parseInt(jQuery(element).parents('.social').children('.comments').children('span').html()) + 1;
            jQuery(element).parents('.social').children('.comments').children('span').html(comments);
            jQuery(element).siblings('textarea').val('');
        }
    );
}

/*
* Display a post comments
* Parameters : string type, int id, HTMLElement element
* Return : none
*/
function getComments(type, id, element) {
    if(!jQuery(element).parent().hasClass('open')) {
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxGetComments',
                'post_id': id,
                'post_type': type
            },
            function(response){
                jQuery(element).parent().addClass('open');
                if(jQuery(element).parents('.produit-single').length ) {
                    // If we are on a product page, we set the comment section to the bottom of the page
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
                    // If we are on a swapplace page, we set the comment section to the bottom of the page
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
                    // If we are on a dressing page, we set the comment section to the bottom of the page
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
                jQuery(element).siblings('.comment-thread-wrapper').css('display', 'flex');
                if(response != 'ok') {
                    jQuery(element).siblings('.comment-thread-wrapper').children('.comment-thread').append(response);
                }
            }
        );
    }
}

/*
* Display a comment's childs
* Parameters : int id, int comment_id, HTMLElement element
* Return : none
*/
function getCommentAnswers(id, comment_id, element) {
    if(!jQuery(element).parent().hasClass('open')) {
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxGetCommentAnswers',
                'post_id': id,
                'comment_id': comment_id,
            },
            function(response){
                // Append comments to the comment
                jQuery(element).parent().addClass('open');
                jQuery(element).siblings('.comment-thread-wrapper').css('display', 'flex');
                jQuery(element).siblings('.comment-thread-wrapper').children('.comment-thread').append(response);
            }
        );
    }
}

/*
* Save a custom alert
* Parameters : int id, int comment_id, HTMLElement element
* Return : none
*/
function saveAlert() {
    if(jQuery('input[name=alert-name]').val() != '') {
        // Get the search string of the url and format it correctly
        var params = window.location.search.substr(1, window.location.search.length - 1).split('&');
        var params_array = [];
        for(var i= 0; i < params.length; i++ ) {
            params_array.push(params[i].split('='));
        }
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxSaveAlert',
                'name': jQuery('input[name=alert-name]').val(),
                'params': params_array
            },
            function(response){
                if(response == 'erreur_nom') {
                    window.alert('Tu as déjà une alerte nommée "'+jQuery('input[name=filtername]').val()+'", choisi un autre nom...');
                } else if(response == 'ok') {
                    window.alert('Alerte sauvegardée, retrouve la dans l\'onglet \'Alertes enregistrées\' du menu');
                    jQuery('.alert__modal').hide()
                    jQuery('#filter-save-modal').hide();
                } else {
                    window.alert('Une erreur est survenue, réessaye ultérieurement');
                    jQuery('#filter-save-modal').hide();
                }
            }
        );
    } else {
        window.alert('Donne un nom à cette alerte');
    }
}

/*
* Notify a user from ajax
* Parameters : int reciever_id, string event, string link, int id
* Return : none
*/
function notify(reciever_id, event, link, id) {
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxNotify',
            'reciever_id': reciever_id,
            'event': event,
            'link': link,
            'event_on': id
        },
        function(response){
            console.log(response);
        }
    );
}

/*
* Delete a notification
* Parameters : HTMLElement element
* Return : none
*/
function deleteNotif(element, click_on_cross = false) {
    var id = jQuery(element).parent().index();
    var href = jQuery(element).parent().children('a').attr('data-href');
    // Get the number of notifications displayed in the navbar and decrease it
    var header_notif = parseInt(jQuery('header .notifs').html());
    if(header_notif == 1) {
        jQuery('header .notifs').hide();
    } else {
        header_notif -= 1;
        jQuery('header .notifs').html(header_notif);
    }
    if(jQuery(element).parent().children('a').attr('data-href').substr(26, 11) == 'discussions') {
        // If the notif is about a discussion, we also decrease the numbers of message notifications
        var msg_notif = parseInt(jQuery('#messagerie-discussions .notifs').html());
        if(msg_notif == 1) {
            jQuery('#messagerie-discussions .notifs').hide();
        } else {
            msg_notif -= 1;
            jQuery('#messagerie-discussions .notifs').html(msg_notif);
        }
    }
    // Get the number of notifications displayed in the "Discussion / Notification" part and decrease it
    var notif_notif = parseInt(jQuery('#messagerie-notifications .notifs').html());
    if(notif_notif == 1) {
        jQuery('#messagerie-notifications .notifs').hide();
    } else {
        notif_notif -= 1;
        jQuery('#messagerie-notifications .notifs').html(notif_notif);
    }
    if(jQuery('.notif').length == 1) {
        jQuery('#notifications').append('<div class="nodata">Aucune notification</div>');
    }
    jQuery(element).parent().remove();
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxDeleteNotify',
            'id': id
        },
        function(response){
            if(!click_on_cross) {
                // If the user did not click on the cross, we redirect him to the notification target
                window.location.assign(href); 
            }
        }
    );
}

/*
* Delete a custom alert
* Parameters : HTMLElement element
* Return : none
*/
function deleteAlerte(element) {
    var index = jQuery(element).parents('.alerte').index();
    jQuery(element).parents('.modal').hide();
    jQuery(element).parents('.alerte').remove();
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxDeleteFilterSet',
            'index': index
        },
        function(response){
            // console.log(response);
        }
    );
}

/*
* Dislike a product from the favorite page
* Parameters : int id, string type, HTMLElement element
* Return : none
*/
function deleteFavori(id, type, element) {
    jQuery(element).prev().hide();
    jQuery(element).hide();
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxLike',
            'type' : 'dislike',
            'post_id': id,
            'post_type': type
        },
        function(){
        }
    );
}

/*
* Delete a product
* Parameters : int id, HTMLElement element
* Return : none
*/
function deleteProduct(id, element) {
    jQuery(element).parents('.produit-min').remove();
    jQuery(element).parents('.deleteproduct').hide();
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxDeleteProduct',
            'post_id': id
        },
        function(){
        }
    );
}

/*
* Send a sale confirmation
* Parameters : int id, HTMLElement element
* Return : none
*/
function sendSellConfirmation(id, element) {
    var partner_id = jQuery('#sell-partners').val();
    jQuery(element).parents('.deleteproduct').children('.confirm-delete').show();
    jQuery(element).parents('.deleteproduct').children('.confirm-swap').addClass('hidden');
    jQuery(element).parents('.deleteproduct').children('.confirm-sell').addClass('hidden');
    jQuery(element).parents('.deleteproduct').children('.confirm-swap-2').addClass('hidden');
    jQuery(element).parents('.deleteproduct').hide();
    jQuery(element).parents('.produit-min').addClass('pending-delete');
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxSendSellConfirmation',
            'post_id': id,
            'partner_id': partner_id
        },
        function(response){
            // console.log(response);
        }
    );
}

/*
* Confirm a sale
* Parameters : int id, int partner_id, HTMLElement element
* Return : none
*/
function confirmSell(id, partner_id, element) {
    if(jQuery('#confirmation-popup').children().length == 1) {
        jQuery('#confirmation-popup').remove();
        location.reload(true);
    } else {
        jQuery(element).parents('.confirmation').remove();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxConfirmSell',
            'post_id': id,
            'partner_id': partner_id
        },
        function(response){
            // console.log(response);
        }
    );
}

/*
* Deny a sale
* Parameters : int id, int partner_id, HTMLElement element
* Return : none
*/
function denySell(id, partner_id, element) {
    if(jQuery('#confirmation-popup').children().length == 1) {
        jQuery('#confirmation-popup').remove();
        location.reload(true);
    } else {
        jQuery(element).parents('.confirmation').remove();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxDenySell',
            'post_id': id,
            'partner_id': partner_id
        },
        function(response){
            // console.log(response);
        }
    );
}

/*
* Send a swap confirmation
* Parameters : int id, HTMLElement element
* Return : none
*/
function sendSwapConfirmation(id, element) {
    var partner_id = jQuery('#swap-partners').val();
    var partner_post_id = jQuery('#swap-product').val();
    jQuery(element).parents('.deleteproduct').children('.confirm-delete').show();
    jQuery(element).parents('.deleteproduct').children('.confirm-swap').addClass('hidden');
    jQuery(element).parents('.deleteproduct').children('.confirm-sell').addClass('hidden');
    jQuery(element).parents('.deleteproduct').children('.confirm-swap-2').addClass('hidden');
    jQuery(element).parents('.deleteproduct').hide();
    jQuery(element).parents('.produit-min').addClass('pending-delete');
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxSendSwapConfirmation',
            'post_id': id,
            'partner_id': partner_id,
            'partner_post_id': partner_post_id
        },
        function(response){

        }
    );
}

/*
* Confirm a swap
* Parameters : int id, int partner_id, HTMLElement element
* Return : none
*/
function confirmSwap(id, partner_post_id, partner_id, element) {
    if(jQuery('#confirmation-popup').children().length == 1) {
        jQuery('#confirmation-popup').remove();
        location.reload(true);
    } else {
        jQuery(element).parents('.confirmation').remove();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxConfirmSwap',
            'post_id': id,
            'partner_post_id': partner_post_id,
            'partner_id': partner_id
        },
        function(response){
            // console.log(response);
        }
    );
}

/*
* Deny a swap
* Parameters : int id, int apartner_post_id, int partner_id, HTMLElement element
* Return : none
*/
function denySwap(id, partner_post_id, partner_id, element) {
    if(jQuery('#confirmation-popup').children().length == 1) {
        jQuery('#confirmation-popup').remove();
        //location.reload(true);
    } else {
        jQuery(element).parents('.confirmation').remove();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxDenySwap',
            'post_id': id,
            'partner_post_id': partner_post_id,
            'partner_id': partner_id
        },
        function(response){
            // console.log(response);
        }
    );
}


/*
* Validate a product
* Parameters : int id, HTMLElement element
* Return : none
*/
function validate(id, element) {
    if(jQuery(element).parents('.produit-single').length == 0) {
        //var msg = '<div class="pending-validation"><img src="https://' + window.location.host + '/wp-content/themes/Swap-Chic/assets/images/loader.gif" alt="" class="spinner">En cours de traitement ...</div>';
        //jQuery(element).parents('.produit').append(msg);
		jQuery(element).parents('.produit').hide();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxValidate',
            'post_id': id
        },
        function(response){
            console.log(response);
            jQuery(element).parents('.produit').children('.pending-validation').remove();
            if(response == 200) {
                if(jQuery(element).parents('.produit-single').length == 0) {
                    jQuery(element).parents('.produit').remove(); 
                } else {
                    window.location.assign('https://' + window.location.host + '/articles-a-valider/');
                }
            } else if(response == 400) {
                alert("ERREUR 400: Paramètres invalides ou fichier non traitable");
				console.log(response);
            } else if(response == 402) {
                alert("ERREUR 402: Crédits insufissants, approvisionnez votre compte");
            } else if(response == 403) {
                alert("ERREUR 403: Problème d'identification, prévenez votre développeur");
            } else if(response == 429) {
                alert("ERREUR 429: Trop de demande d'un coup, attendez une minute et réessayez");
            }
        }
    );
}

/*
* Unvalidate a product
* Parameters : int id, HTMLElement element
* Return : none
*/
function unvalidate(id, element) {
    if(jQuery(element).parents('.produit-single').length == 0) {
        jQuery(element).parents('.produit').hide();
    }
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxUnvalidate',
            'post_id': id
        },
        function(){
            if(jQuery(element).parents('.produit-single').length == 0) {
                jQuery(element).parents('.produit').remove(); 
            } else {
                window.location.assign('https://' + window.location.host + '/articles-a-valider/');
            }
        }
    );
}

/*
* Set a featured product
* Parameters : int id, HTMLElement element
* Return : none
*/
function setCoupDeCoeur(id, element) {
    if(jQuery(element).hasClass(' cdc')) {
        alert('Déjà votre coup de coeur');
    } else {
        // Get the previous featured product id and remove the featured class
        var old_cdc_id = jQuery(element).siblings('.produitcdc.cdc').attr('data-id');
        jQuery(element).siblings('.cdc').removeClass('cdc');
        jQuery(element).addClass('cdc');
        jQuery.post(
            swapchic_ajax.ajax_url,
            {
                'action': 'ajaxSetCoupDeCoeur',
                'post_id': id,
                'old_post_id': old_cdc_id,
            },
            function(response){
                // console.log(response);
            }
        );
    }
}

/*
* Set a user as active
* Parameters : none
* Return : none
*/
function setActiveUser() {
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxSetActiveUser'
        },
        function(){

        }
    );
}

/*
* Set a user as inactive
* Parameters : none
* Return : none
*/
function setInactiveUser() {
    jQuery.post(
        swapchic_ajax.ajax_url,
        {
            'action': 'ajaxSetInactiveUser'
        },
        function(){

        }
    );
}

jQuery(document).ready(function() {
    setActiveUser();
    jQuery(window).bind("beforeunload", function() { 
        setInactiveUser();
    })
});