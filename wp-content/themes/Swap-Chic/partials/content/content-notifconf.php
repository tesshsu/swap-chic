<?php 
    $notifs = get_query_var('notifs');
    // print_r($notifs);
?>

<div class="popup" id="confirmation-popup">
    <?php 
        foreach($notifs as $notif){
            set_query_var('notif', $notif);
            if($notif[event] == 'sell') {
                get_template_part('partials/content/content', 'sell');
            } elseif($notif[event] == 'swap') {
                get_template_part('partials/content/content', 'swap');
            }
        }
    ?>
</div>