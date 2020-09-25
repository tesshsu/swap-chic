<?php 
    $user_id = get_query_var('user');
    $user = get_userdata($user_id);
    $dressing = get_field('dressing', 'user_'.$user_id);
    $is_liked = isPostLiked($post_id);
	$photo_profil = get_field('photo_profil', 'user_'.$user_id);
	if(!$photo_profil ){
		$photo_profil_url = get_template_directory_uri().'/assets/images/profil.svg';
	}else{
		$photo_profil_url = $photo_profil;
	}
	$is_online = false;
                if(get_user_meta($user_id, 'asdb-loggedin')[0] == 1) {
                    $is_online = true;
                }
?>
<div data-id="<?php echo $dressing ?>" data-slug="<?php echo $slug = get_post_field( 'post_name', $dressing ); ?>" data-type="dressing" class="dressing<?php if($is_liked) echo ' liked' ?> discussion <?php if($is_online){ echo 'online'; }?>">
    <div class="user">
	   <a href="<?php echo get_permalink($dressing) ?>">
        <img src="<?php echo $photo_profil_url ?>" alt="">
		<p><?php echo ucfirst($user->data->display_name) ?></p>
       </a>
    </div>
</div>