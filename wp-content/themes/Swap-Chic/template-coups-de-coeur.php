<?php
/*
Template Name: Coups de coeur
Template Post Type: page
*/

    if(!is_user_logged_in()) {
        header('Location: https://'.$_SERVER['HTTP_HOST'].'/');
        exit();
    } else{
        $user = wp_get_current_user();
        if(!in_array('administrator', $user->roles)) {
            header('Location: https://'.$_SERVER['HTTP_HOST'].'/actualites');
            exit();
        }
    }

    get_header();

    $villes = array();

    // List of the relevant cities zip codes 
    $villes['paris'][zip] = array(
        "75001",
        "75002",
        "75003",
        "75004",
        "75005",
        "75006",
        "75007",
        "75008",
        "75009",
        "75010",
        "75011",
        "75012",
        "75013",
        "75014",
        "75015",
        "75016",
        "75017",
        "75018",
        "75019",
        "75020"
    );

    // Marseille + Aix en provence
    $villes['marseille'][zip] = array(
        "13001",
        "13002",
        "13003",
        "13004",
        "13005",
        "13006",
        "13007",
        "13008",
        "13009",
        "13010",
        "13011",
        "13012",
        "13013",
        "13014",
        "13015",
        "13016",
        "13090",
        "13100",
        "13290",
        "13080",
        "13540",
        "13122"
    );

    $villes['lyon'][zip] = array(
        "69001",
        "69002",
        "69003",
        "69004",
        "69005",
        "69006",
        "69007",
        "69008",
        "69009"
    );

    $villes['toulouse'][zip] = array(
        "31500",
        "31100",
        "31400",
        "31300",
        "31000",
        "31200",
        "31998"
    );

    $villes['bordeaux'][zip] = array(
        "33000",
        "33300",
        "33100",
        "33800",
        "33200"
    );

    $villes['nice'][zip] = array(
        "06000",
        "06100",
        "06200",
        "06300"
    );
   
    $villes['nantes'][zip] = array(
        "44100",
        "44000",
        "44300",
        "44200"
    );

    $villes['montpellier'][zip] = array(
        "34000",
        "34080",
        "34090",
        "34070",
        "34295"
    );

    $villes['strasbourg'][zip] = array(
        "67200",
        "67000",
        "67100"
    );

    $villes['lille'][zip] = array(
        "59800",
        "59000",
        "59260",
        "59777",
        "59160"
    );

    $villes['rennes'][zip] = array(
        "35000",
        "35700",
        "35200"
    );

    $villes['reims'][zip] = array(
        "51100"
    );

    $villes['saint-étienne'][zip] = array(
        "42000",
        "42100",
        "42230"
    );

    $villes['toulon'][zip] = array(
        "83000",
        "83100",
        "83200",
        "83800"
    );

    $villes['le havre'][zip] = array(
        "76610",
        "76600",
        "76620"
    );

    $villes['grenoble'][zip] = array(
        "38000",
        "38100",
        "38700"
    );

    $villes['dijon'][zip] = array(
        "21000"
    );

    $villes['angers'][zip] = array(
        "49000",
        "49100"
    );

    $villes['nîmes'][zip] = array(
        "30000",
        "30998",
        "30900"
    );

    $villes['villeurbanne'][zip] = array(
        "69100"
    );

    $villes['clermont-ferrand'][zip] = array(
        "63100",
        "63000"
    );

    $villes['le mans'][zip] = array(
        "72100",
        "72000"
    );

    $villes['brest'][zip] = array(
        "29200",
        "29000",
        "29240"
    );

    $villes['tours'][zip] = array(
        "37200",
        "37000",
        "37100"
    );

    $villes['amiens'][zip] = array(
        "80000",
        "80090",
        "80080"
    );

    $villes['limoges'][zip] = array(
        "87100",
        "87000",
        "87280"
    );

    $villes['annecy'][zip] = array(
        "74960",
        "74370",
        "74600",
        "74000",
        "74940"
    );

    $villes['perpignan'][zip] = array(
        "66000",
        "66100"
    );

    $villes['orléans'][zip] = array(
        "45000",
        "45100"
    );


    $args = array(
        'role' => 'contributor',
        'orderby' => 'date',
        'order' => 'DESC',
        'nopaging' => true
    );
    $user_query = new WP_User_Query( $args );

    // Searching for users in the relevant cities
    if ( ! empty( $user_query->results ) ) {
        foreach ( $user_query->results as $user ) {
            $user_id = $user->ID;
           if(userHasProducts($user_id)) {
                $author_zip = get_field('code_postal', 'user_'.$user_id);
                foreach($villes as $nom_ville => $ville) {
                    if(in_array($author_zip, $ville[zip])) {
                        $villes[$nom_ville][author][] = $user_id;
                    }
                }
            }
        }
    }
    
?>

<p class="h2">Vos coups de coeur</p>
<div id="cdc">
    <?php 
    foreach($villes as $nom_ville => $ville) {
        if($nom_ville == 'marseille') {
            $display_nom_ville = 'Marseille / Aix-en-Provence';
        } else {
            $display_nom_ville = ucfirst($nom_ville);
        }
    ?>
        <div class="<?php echo $nom_ville ?>">
            <p class="h2"><?php echo $display_nom_ville ?> <span class="expand">+</span></p>
            <?php 
            if($ville[author] != null) {
                $args = array (
                    'post_type' => 'produits',
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'author__in' => $ville[author],
                    'nopaging' => true
                );
                $the_query = new WP_Query( $args );

                if ( $the_query->have_posts() ) {
                    foreach($the_query->posts as $post) {
                        set_query_var( 'post', $post );
                        set_query_var( 'city', ucfirst($nom_ville) );
                        get_template_part( 'partials/content/content', 'produitcdc' );
                    }
                }
                wp_reset_postdata();
            }
            ?>
        </div>
    <?php 
    } 
    ?>
</div>

<?php 
    get_footer();
?>