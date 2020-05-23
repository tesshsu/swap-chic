<?php 
    $category = get_query_var('category');
    $scope_lvl = get_query_var('scope_lvl');
    $scope_string = getScopeString($_GET);
   // print_r($scope_string);
   
?>

<div class="see-more">
    <p class="h1">Envie d'en voir plus ?</p>
    <?php
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