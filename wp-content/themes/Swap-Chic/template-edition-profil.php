<?php
    /*
    Template Name: Édition du profil
    Template Post Type: page
    */
    
    $current_user_id = get_current_user_id(); 
    if($_POST) {
        if($_POST['type'] == 'profile-picture') {
            $image = saveNewProfilePicture($current_user_id, $_POST['cropped-picture']);
	        update_field('photo_profil', $image, 'user_'.$current_user_id);
        } elseif($_POST['type'] == 'zipcode') {
            update_field('code_postal', $wpdb->escape($_POST['zipcode']), 'user_'.$current_user_id);
            $user_zip = $_POST['zipcode'];
            $data = json_decode(file_get_contents('https://geo.api.gouv.fr/communes?codePostal='.$user_zip.'&fields=nom,codesPostaux&format=json&geometry=centre'));
            update_field('ville', $data[0]->nom, 'user_'.$current_user_id);
        } elseif($_POST['type'] == 'password') {
            wp_set_password($wpdb->escape($_POST['pwd']), $current_user_id);
        }
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/editer-profil');
    } else {

    get_header(); 
?>

<div class="edit-title">
    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="edit-close"><img src="<?php echo get_template_directory_uri().'/assets/images/close.svg'; ?>" alt=""></a>
    <h1 class="h1">Éditer mon profil</h1>
</div>
<form method="post" id="edit-picture">
    <p class="h2">Photo de profil :</p>
    <div id="crop-modal">
        <p class="h2">Ajustez votre image :</p>
        <div class="img-wrapper">
            <img src="" alt="">
        </div>
        <div class="btn" id="crop">Valider</div>
        <div id="close">Annuler</div>
    </div>
    <label for="edit-profile-picture" class="custom-file-upload">
        <div id="image" class="hasBefore">
            <img src="<?php echo get_field('photo_profil', 'user_'.$current_user_id)?>" alt="">
            <input type="hidden" name="cropped-picture" id="edit-cropped-picture" value="" required>
        </div>    
    </label>
    <input type="file" name="profile-picture" id="edit-profile-picture" onchange="readProfilePictureURL(this);" required>
    <input type="hidden" name="type" value="profile-picture">
    <input type="submit" value="Valider" class="btn">
</form>
<form method="post" id="edit-zipcode">
    <p class="h2">Code postal :</p>
    <input type="text" name="zipcode" placeholder="Votre nouveau code postal..." value="<?php echo get_field('code_postal', 'user_'.$current_user_id) ?>" required>
    <input type="hidden" name="type" value="zipcode">
    <input type="submit" value="Valider" class="btn">
</form>
<form method="post" id="edit-password">
    <p class="h2">Mot de passe :</p>
    <input type="password" name="pwd" placeholder="Nouveau mot de passe..." required">
    <input type="password" name="pwd-conf" placeholder="Confirmer mot de passe..." required">
    <p id="msg"></p>
    <input type="hidden" name="type" value="password">
    <input type="submit" value="Valider" class="btn">
</form>

<?php
  }
get_footer();
?>