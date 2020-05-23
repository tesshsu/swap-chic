<div id="login-wrapper" <?php if(get_query_var('shown') != 'login') echo 'style="display:none"'?> >
    <h2 class="h1">Connexion</h2>
    <form method="post" id="login-form">
        <input type="text" name="username" id="login-username" placeholder="Identifiant ou e-mail" required>
        <input type="password" name="password" id="login-password" placeholder="Mot de passe" required>
        <input type="hidden" name="type" value="login" required>
        <input type="submit" value="Connexion" class="btn">
    </form>
    <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/wp-login.php?action=lostpassword'?> " class="link" <?php if(get_query_var('shown') != 'login') echo 'style="display:none"'?>>Mot de passe oublié ?</a>
</div>
<p id="signup-toggle" class="link" <?php if(get_query_var('shown') != 'login') echo 'style="display:none"'?>>Pas encore inscrite ?<br><span>Rejoins-nous !</span></p>