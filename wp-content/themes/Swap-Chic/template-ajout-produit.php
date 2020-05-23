<?php
/*
Template Name: Ajout produit
Template Post Type: page
*/

if(is_user_logged_in()) {

if($_POST) {
    if(isset($_POST['subcategory'])){
        $title = $_POST['subcategory'].' '.$_POST['marque'];
    } else {
        $title = $_POST['category'].' '.$_POST['marque'];
    }
    $wp_data = array(
        'post_content' => $wpdb->escape($_POST['desc']),
        'post_title' => $wpdb->escape($title),
        'post_type' => 'produits'
    );
    $price = $wpdb->escape($_POST['price']);
    // Remove the € sign
    if(strpos($price, "€")) {
        $price = substr($price, 0, strpos($price, "€"));
    }
    $acf_data = array(
        'images' =>  $wpdb->escape(array($_POST['image-front'], $_POST['image-back'], $_POST['image-label'])),
        'action' => $wpdb->escape($_POST['action']),
        'price' => $price,
        'categorie-parent' => $wpdb->escape($_POST['target']),
        'categorie' => $wpdb->escape($_POST['category']),
        'sous-categorie' => $wpdb->escape($_POST['subcategory']),
        'taille' => $wpdb->escape($_POST['taille']),
        'etat' => $wpdb->escape($_POST['etat']),
        'matiere' => $wpdb->escape($_POST['matiere']),
        'couleur' => $wpdb->escape($_POST['product-colors']),
        'imprime' => $wpdb->escape($_POST['imprime']),
        'saison' => $wpdb->escape($_POST['saison']),
        'marque' => $wpdb->escape($_POST['marque'])
    );
    addProduct($wp_data, $acf_data);
} else { 
    get_header();
    get_template_part( 'partials/form/addproduct', 'skeleton');
    get_footer();
}
} else {
    get_header(); ?> 

    <div class="not-connected">
        <p>Connectez vous pour accéder à toutes les fonctionnalités de Swap-Chic.</p>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'] ?>" class="btn">Connexion</a>
    </div>

    <?php get_footer();
}
?>