<?php
/*
Template Name: Inviter vos amies
Template Post Type: page
*/

if($_POST) {
    $user_id = get_current_user_id();
    foreach($_POST as $mail) {
        $to = $mail;  
		$subject = ucfirst(get_userdata($user_id)->data->user_login) . " t'invite à rejoindre la communauté Swap-Chic !"; 
		$from = "noreply@swap-chic.com";  
        $headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
  		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$variables = array();
		$variables['name'] = get_userdata($user_id)->data->user_login;
		$variables['link'] = 'https://'.$_SERVER['HTTP_HOST'];
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/Invitation.html");
		foreach($variables as $key => $value) {
			$template = str_replace('{{ '.$key.' }}', $value, $template);
		}
		mail($to, $subject, $template, $headers);  
    }
    $ty = true;
}
    get_header(); 
?>

<form id="send-invitation" method="post">
    <p class="h2">Inviter vos amies à rejoindre la communauté Swap-Chic !</p>

    <?php 
        if($ty) {
            print '<div class="alert-success">Merci de nous aider à faire grandir la communauté Swap-Chic !</div>';
        }
    ?>

    <input type="email" placeholder="E-mail..." name="email-1" id="email-1" required>
    <input type="email" placeholder="E-mail..." name="email-2" id="email-2">
    <input type="email" placeholder="E-mail..." name="email-3" id="email-3">
    <div id="send-invitation-submit" class="btn">Envoyer</div>
</form>

<?php
    get_footer();
?>