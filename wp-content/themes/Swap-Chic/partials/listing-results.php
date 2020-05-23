<?php
    $results = get_query_var('results');
?>

<div id="searchresults">
    <h2 class="h2">Résultats pour <span>"<?php echo $_GET['s']?>"</span></h2>
    
    <?php 
        if(!empty($results)) {
            foreach($results as $result) {
                if($result->post_type != 'dressings') {
                    set_query_var('post', $result->ID);
                    get_template_part( 'partials/content/content', $result->post_type );
                } else {
                    set_query_var('dressing', $result->ID);
                    get_template_part( 'partials/content/result', $result->post_type );
                }
            } 
        } else { ?>
            <div class="no-result">
                <p>Désolé, nous n'avons trouvé aucun résultat pour cette recherche...</p>
	            <?php get_search_form(); ?>
            </div>
        <?php }
    ?>
</div>