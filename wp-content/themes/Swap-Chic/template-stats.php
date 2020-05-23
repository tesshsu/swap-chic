<?php
/*
Template Name: Stats
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

$user_count = 0;
$user__with_product_count = 0;

$args = array(
    'role' => 'contributor',
    'nopaging' => true
);
$user_query = new WP_User_Query( $args );
if (!empty($user_query->results)) {
    foreach ( $user_query->results as $user ) {
        $user_id = $user->ID;
        $user_count ++;
        if(userHasProducts($user_id)) {
            $user__with_product_count ++;
        }
    }
}
wp_reset_postdata();

$city_swapplace_count = array();
$city_swapplace_mail = array();
$swapplace_mail = array();
$args = array (
    'post_type' => 'swapplaces',
    'post_status' => 'publish',
    'nopaging' => true
);
$the_query = new WP_Query( $args );

if ( $the_query->have_posts() ) {
    while ( $the_query->have_posts() ) {
        $the_query->the_post(); 
        $post_id = get_the_id();
        $post_title = get_the_title();
        $adresse = get_field('adresse', $post_id);
        preg_match('/[0-9]{5}[ ,]*/',  $adresse, $matches);
        $city = strtolower(substr($adresse, strpos($adresse, $matches[0]) + strlen($matches[0])));
        if(isset($city_swapplace_count[$city])) {
            $city_swapplace_count[$city] ++;
        } else {
            $city_swapplace_count[$city] = 1;
        }
        $mail = get_field('swapplace_email', $post_id);
        if(preg_match('/@/',  $mail)) {
            $city_swapplace_mail[$city][] = get_field('swapplace_email', $post_id);
            $swapplace_mail[] = get_field('swapplace_email', $post_id);
        }
    }
}
wp_reset_postdata();

function compareCount($a, $b) {
 if($a == $b) {
    return 0;
 }
 return ($a > $b) ? -1 : 1;
}

uasort($city_swapplace_count, 'compareCount');

?>

<div id="dashboard">
    <p class="dashboard__title">Panneau d'administration</p>
    <div class="block">
        <div class="block__title">
            Statistiques :
        </div>
        <div class="block__div">
            <div class="block__subtitle">
                Nombres d'inscrits total :
            </div>
            <div class="block__copy">
                <?php echo $user_count?>
            </div>
        </div>
        <div class="block__div">
            <div class="block__subtitle">
                Nombres d'inscrits avec des produits:
            </div>
            <div class="block__copy">
                <?php echo $user__with_product_count?>
            </div>
        </div>
    </div>
    <div class="block">
        <div class="block__title">
            Mails des swapplaces :
        </div>
        <div class="block__div">
            <input type="hidden" id="mails" value="<?php echo preg_replace('/[\\\"\[\]\{\}]/', '', json_encode($swapplace_mail)) ?>">
            <div class="btn" onclick="copyMails(this)">Récuperer les emails disponibles</div>
        </div>
    </div>
    <div class="block">
        <div class="block__title">
            Swapplaces :
        </div>
        <table>
            <thead>
                <th style="text-align:left">Ville</th>
                <th style="text-align:left">Nombre</th>
                <th>Action</th>
            </thead>
            <tbody>
            <?php 
                foreach($city_swapplace_count as $city => $count ) {
                    print "<tr>";
                    print "<td>". ucfirst($city)."</td>";
                    print "<td>".$count."</td>";
                    if(count($city_swapplace_mail[$city]) > 0) {
                        $input_value = preg_replace('/[\\\"\[\]\{\}]/', '', json_encode($city_swapplace_mail[$city]));
                        print "<td><input type='hidden' id='".$city."-mails' value='".$input_value."'><div class='btn' onclick='copyMails(this)'>Récuperer les emails disponibles</div></td>";
                    } else {
                        print "<td>Aucun email disponible</td>";
                    }
                    print "</tr>";
                }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
get_footer();
?>