<?php
/*
Template Name: Connexion / Inscription 
Template Post Type: page
*/

$has_error = false;
set_query_var('shown', 'login');

if($_POST) {
    if($_POST['type'] == 'login') {
        $credentials = array(
            'user_login' => $wpdb->escape($_POST['username']), 
            'user_password' => $wpdb->escape($_POST['password']),
            'remember' => true
        );
        $login = loginUser($credentials);
        if(!is_wp_error($login)) {
            header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites/');
		    exit();
        } else {
            $has_error = [true, 'login'];
            $errors_html = '<div class="alert-danger">';
            foreach($login->errors as $error){ 
                $errors_html .= $error[0]; 
            }
            $errors_html .= '</div>';
        }
    } elseif($_POST['type'] == 'signup') {
        $wp_data = array(
            'user_pass' => $wpdb->escape($_POST['password']),
            'user_login' => $wpdb->escape($_POST['username']),
            'user_email' => $wpdb->escape($_POST['mail']),
            'role' => 'contributor',
            'show_admin_bar_front' => 'false'
        );
        $acf_data = array(
            'profile_picture' => $_POST['cropped-picture'],
            'user_zip' => $wpdb->escape($_POST['zipcode'])
        );
        $register = registerUser($wp_data, $acf_data);
        if(!is_wp_error($register)) {
            $scope = getScope($register->ID);
            // Redirect new users directly to the product adding form
            header('Location: https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit/?from_signup=1');
		    exit();
        } else {
            $has_error = [true, 'register'];
            $errors_html = '<div class="alert-danger">';
            foreach($register->errors as $error){ 
                $errors_html .= $error[0]; 
            }
            $errors_html .= '</div>';
        }
    } elseif($_POST['type'] == 'forgotpassword ') {
        $user_id = get_userdata();
        recoverPassword($user_id);
    }
}

    get_header();
    ?>
        <div class="signin-wrapper">
           <h1 class="logo"><img src="<?php echo get_template_directory_uri().'/assets/images/logo.svg'?>" alt="Swap-Chic"></h1>
           <?php if($has_error) {?>
                <div class="alert-danger">
                    <?php 
                        if($has_error[1] == 'register') {
                            foreach($register->errors as $error) { 
                                echo $error[0]; 
                                set_query_var('shown', 'register');
                            }
                        } elseif($has_error[1] == 'login') {
                            foreach($login->errors as $error) { 
                                echo $error[0]; 
                                set_query_var('shown', 'login');
                            }
                        }
                    ?>
                </div>
            <?php } elseif(isset($_GET['mail']) && $_GET['mail'] == 'sent') { ?>
                <div class="alert-success">
                    Vérifie ta boite mail pour y trouver le lien de confirmation.
                </div>
            <?php }
                get_template_part( 'partials/user/user', 'login' );
                get_template_part( 'partials/user/user', 'signup' );
            ?>
            <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="link intro-replay">Revoir l'introduction</a>
        </div>
<?php
    get_footer();
?>