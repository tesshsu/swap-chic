<?php
/*
Template Name: Proposer une swap-place
Template Post Type: page
*/


if($_POST) {
    $to = 'checking@swap-chic.com';
    $subject = "Suggestion de nouvelle swap-place"; 
    $from = "noreply@swap-chic.com";  
    $headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
    $headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
    $headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $variables = array();
    $variables['name'] = $_POST['establishement'];
    $variables['city'] = $_POST['ville'];
	$variables['email'] = $_POST['email'];
    $template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/suggestion_sp.html");
    foreach($variables as $key => $value) {
        $template = str_replace('{{ '.$key.' }}', $value, $template);
    }
    mail($to, $subject, $template, $headers);  
    $ty = true;
}
    get_header(); ?>

<form id="send-suggestion" method="post">
    <p class="h2">Suggére nous une nouvelle Swap-place</p>
    <div class="msg">
        <?php 
            if($ty) {
                print '<div class="alert-success">Merci de nous aider à faire grandir la communauté Swap-Chic !</div>';
            }
        ?>
    </div>
    
    <label for="establishement">Nom de l'établissement : </label>
    <input type="text" name="establishement" id="establishement" required>
    <label for="adresse">Ville de l'établissement : </label>
    <input type="text" name="ville" id="ville" required>
	<label for="adresse">Votre Email : </label>
    <input type="email" name="email" id="mail" required>
    <div id="send-suggestion-submit" class="btn">Envoyer</div>
</form>

<?php
    get_footer(); 
?>