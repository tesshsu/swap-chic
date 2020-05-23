<?php 
    $user_id = get_current_user_id();
    $user_has_products = false;
    if(!empty(get_field('produits',  get_field('dressing', 'user_'.$user_id)))) {
        $user_has_products = true;
    }
    $scope_lvl = get_query_var('scope_lvl');
    $scope_string = getScopeString($_GET);
    $category = get_query_var('category');
?>

<div class="no-products">
    <p class="h1">Tu es la première ! 🎉</p>
    <?php if(!$user_has_products) { ?>
        <p>L'application Swap-Chic est toute récente, ajoute le premier article dans ta ville.</p>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/ajouter-produit' ?>" class="btn">Ajoute un article</a>
    <?php } else { ?>
        <p>Invite tes amies à s'inscrire et partager leurs dressings avec la communauté !</p>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/inviter-amies' ?>" class="btn">Invite tes amies</a>
    <?php  } 

    if(getPath()[1] == 'catalogue') {
        if($scope_lvl == 'ville') {
            $dpt = substr($scope_string, strpos($scope_string, '&more=') + 1); 
            $dpt = str_replace('more', 'scope', $dpt);
            $dpt = str_replace('even_scope', 'more', $dpt);
    ?>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/catalogue/?'.$dpt.'#'.$category ?>" class="increase-scope-lvl">Parcours les <?php echo $category ?> de ton département</a>
    <?php 
        } elseif($scope_lvl == 'departement') {
            $region = substr($scope_string, strrpos($scope_string, '&more=') + 1);
            $region = str_replace('more', 'scope', $region);
            $actual_dpt = substr($scope_string, strrpos($scope_string, 'scope=') + 6);
     ?>
        <a href="<?php echo 'https://'.$_SERVER['HTTP_HOST'].'/catalogue/?'.$region.','.$actual_dpt.'#'.$category ?>" class="increase-scope-lvl">Parcours les <?php echo $category ?> de ta région</a>
    <?php 
        }
    }
    ?>
</div> 