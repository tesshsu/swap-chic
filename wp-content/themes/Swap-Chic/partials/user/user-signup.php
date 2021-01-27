<div id="signup-wrapper" <?php if(get_query_var('shown') != 'register') echo 'style="display:none"';?>>
    <h2 class="h1">Inscription</h2>
    <form method="post" id="signup-form">
        <div id="crop-modal">
            <p class="h2">Ajustez votre image :</p>
            <div class="img-wrapper">
                <img src="" alt="">
            </div>
            <div class="btn" id="crop">Valider</div>
            <div id="close">Annuler</div>
        </div>
        <label for="signup-profile-picture" class="custom-file-upload">
            <div id="image">
                <input type="hidden" name="cropped-picture" id="signup-cropped-picture" value="" required>
            </div>
            <span>Photo de profil</span>           
        </label>
        <input type="file" name="profile-picture" id="signup-profile-picture" onchange="readProfilePictureURL(this);">
        <input type="text" name="username" id="signup-username" placeholder="Identifiant" required>
		<span class="tipNotify">Votre identifiant n'accept pas formulair de votre email</span>
        <input type="mail" name="mail" id="signup-mail" placeholder="E-mail" required>
        <input type="password" name="password" id="signup-password" placeholder="Mot de passe" required>
        <input type="password" name="password-confirmation" id="signup-password-confirmation" placeholder="Confirmation mot de passe" required>
        <p id="msg"></p>
        <input type="text" name="zipcode" id="signup-zipcode" placeholder="Code postal" required>
        <span><input type="checkbox" name="cguv" id="signup-cguv"> J'accepte les <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/cguv/' ?>" target="_blank" class="link">conditions générales</a></span>
        <input type="hidden" name="type" value="signup" required>
        <input type="submit" value="Inscription" class="btn">
    </form>
</div>
<p id="login-toggle-1" class="link" <?php if(get_query_var('shown') != 'register') echo 'style="display:none"';?>>Déjà inscrite ?<br><span>Connecte-toi !</span></p>
