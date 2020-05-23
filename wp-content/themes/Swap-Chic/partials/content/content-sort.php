<?php 
    $scope_array = get_query_var('scope'); 
    if(isset($scope_array["scope"]) && !empty($scope_array["scope"])) {
        $scope = '';
        foreach($scope_array["scope"] as $key => $scope_data) {
            if($key != 0) {
                $scope .= ',';
            }
            $scope .= $scope_data;
        }
    }
    if(isset($scope_array["more"]) && !empty($scope_array["more"])) {
        $more = '';
        foreach($scope_array["more"] as $key => $more_data) {
            if($key != 0) {
                $more .= ',';
            }
            $more .= $more_data;
        }
    }
    if(isset($scope_array["even_more"]) && !empty($scope_array["even_more"])) {
        $even_more = '';
        foreach($scope_array["even_more"] as $key => $even_more_data) {
            if($key != 0) {
                $even_more .= ',';
            }
            $even_more .= $even_more_data;
        }
    }
    $order =  get_query_var('order'); 
    $filters = get_query_var('filters');
 ?>

<div class="sort-open"><p>Trier...</p>
    <div class="order" style="display:none">
        <form method="get" id="orderform">
            <input type="hidden" name="scope" value="<?php echo $scope ?>">
            <input type="hidden" name="more" value="<?php echo $more ?>">
            <input type="hidden" name="even_more" value="<?php echo $even_more ?>">
            <?php if(isset($filters)) { ?>
                <input type="hidden" name="filters" value="<?php echo $filters ?>">
            <?php } ?>
            <label for="order-distance"><input type="radio" value="distance" name="order" id="order-distance" <?php if($order == 'distance') echo 'checked' ?> >Du plus proche...</label>        
            <label for="order-recent"><input type="radio" value="recent" name="order" id="order-recent" <?php if($order == 'recent') echo 'checked' ?> >Du plus récent...</label>        
            <label for="order-oldest"><input type="radio" value="oldest" name="order" id="order-oldest" <?php if($order == 'oldest') echo 'checked' ?> >Du plus ancien...</label>        
            <label for="order-popular"><input type="radio" value="popular" name="order" id="order-popular" <?php if($order == 'popular') echo 'checked' ?> >Du plus populaire...</label>        
            <label for="order-busiest"><input type="radio" value="busiest" name="order" id="order-busiest" <?php if($order == 'busiest') echo 'checked' ?> >Du plus commenté...</label>        
            <div class="modal-actions">
                <div class="order-submit btn">Trier</div>
                <div class="order-close">Annuler</div>
            </div>
        </form>
    </div>
</div>
