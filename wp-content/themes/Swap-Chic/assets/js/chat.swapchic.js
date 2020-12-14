/*
* Heavily based on this : https://css-tricks.com/jquery-php-chat/
*/

var instanse = false;
var state;
var mes;
var file;

function Chat () {
    this.update = updateChat;
    this.send = sendChat;
    this.getState = getStateOfChat;
}

/*
* Get the initial state of the discussion
* Parameters : array ids, int post_id
* Return : none
*/
function getStateOfChat(ids, post_id) {
	if(!instanse){
        instanse = true;
        jQuery.post(
            swapchic_chat.ajax_url,
            {
                'action': 'chatGetState',
                'ids': ids,
                'post_id': post_id
            },
            function(data_raw) {
                data = JSON.parse(data_raw);
                state = data.state;
                instanse = false;
                // Display the previous messages
                if(data.text){
					for (var i = 0; i < data.text.length; i++) {
                        jQuery('#chat-area').append(jQuery(data.text[i]));
                        jQuery('#chat-area > div').each(function() {
                            // Set the class for current user's messages
                            if(jQuery(this).attr('data-from') == ids[0]) {
                                jQuery(this).addClass('mine');
                            }
                        });
					}	
                    jQuery(window).scrollTop(jQuery(document).height());
                }
            }
        );
	}	
}

/*
* Updates the state of the discussion
* Parameters : array ids, int post_id
* Return : none
*/
function updateChat(ids, post_id) {
	if(!instanse){
		instanse = true;
		jQuery.post(
            swapchic_chat.ajax_url,
            {
                'action': 'chatUpdate',
                'state': state,
                'ids': ids,
                'post_id': post_id
            },
            function(data_raw) {
                data = JSON.parse(data_raw);
                // Append new messages to the chat area
				if(data.text){
					for (var i = 0; i < data.text.length; i++) {
                        jQuery('#chat-area').append(jQuery(data.text[i]));
					}
                    jQuery(window).scrollTop(jQuery(document).height());
                }
                instanse = false;
                state = data.state;
                if(jQuery('#chat-area > *:last-child').attr('data-from') == ids[0]) {
                    jQuery('#chat-area > *:last-child').addClass('mine');
                }
			}
		);
	} else {
        //console.log('Wait...');
		//setTimeout(updateChat(ids), 1500);
	}
}

/*
* Send a new message
* Parameters : string message, array ids, int post_id, string post_url
* Return : none
*/
function sendChat(message, ids, post_id, post_url) {
    updateChat(ids, post_id);
    jQuery.post(
        swapchic_chat.ajax_url,
        {
            'action': 'chatSend',
            'message': message,
            'ids': ids,
            'post_id': post_id
        },
		function(data_raw){
            data = JSON.parse(data_raw);
            notify(ids[1], 'chat', post_url, -1);
			updateChat(ids, post_id);
		}
	);
}

/*
* Open a discussion page
* Parameters : int user_id, int partner_id
* Return : none
*/
function openChat(user_id, partner_id) {
    jQuery.post(
        swapchic_chat.ajax_url,
        {
            'action': 'chatOpen',
            'user_id': user_id,
            'partner_id': partner_id
        },
        function(post_url) {
            window.location.assign(post_url);
        }
    );
}

// Auto resize message textarea
var autoExpand = function (field) {

    // Reset field height
    field.style.height = 'calc(1.25em + 20px)';

    // Get the computed styles for the element
    var computed = window.getComputedStyle(field);

    // Calculate the height
    var height = parseInt(computed.getPropertyValue('border-top-width'), 10)
                + field.scrollHeight
                + parseInt(computed.getPropertyValue('border-bottom-width'), 10);

    field.style.height = height + 'px';
};

document.addEventListener('input', function (event) {
    if (event.target.tagName.toLowerCase() !== 'textarea') return;
    autoExpand(event.target);
}, false);
