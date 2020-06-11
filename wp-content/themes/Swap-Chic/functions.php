<?php
/**
 * Functions and definitions
**/


// Necessary for the remove.bg API, see setProductThumbnail()
require_once( ABSPATH . 'vendor/autoload.php' );
use Guzzle\Http\Exception\ClientErrorResponseException;


/*
* Cron job sending alerts to users
* Parameters : none
* Return : none
*/
function cronSendAlerts() {
	$args = array(
		'role' => 'contributor',
		'nopaging' => true
	);
	
    $user_query = new WP_User_Query( $args );

    if (!empty($user_query->results)) {
		foreach ( $user_query->results as $user ) { 
			$user_id = $user->ID;
			checkUserAlerts($user_id);
		}
	}	
}
add_action( 'send_alerts_event', 'cronSendAlerts',  10);


/*
* Check users alerts and sends them an email if there are new products matching at least one of the alerts
* Parameters : int $user_id
* Return : none
*/
function checkUserAlerts($user_id) {
	$alerts = get_field('alertes', 'user_'.$user_id);
	$alerts_with_new_posts = array();
	if(!empty($alerts)) {
		foreach($alerts as $alert) {
			// Since $alert is an url search string, we can parse it to get an array of the alert's filters
			parse_str(substr($alert['alerte'], 1), $parsed_alert);
			// We add the time filter to get only products a week old or younger
			$parsed_alert['time'] = 1;
			$postlist = getAdvancedSearchPosts($user_id, $parsed_alert);
			if(count($postlist) > 0) {
				$alerts_with_new_posts[] = array(
					'name' => $alert['alerte_nom'],
					'count' => count($postlist),
					'link' => 'https://'.$_SERVER['HTTP_HOST'].'/recherche-avancee/'.$alert['alerte'],
				);
			}
		}
	}
	if(!empty($alerts_with_new_posts)) {
		$to = get_userdata($user_id)->data->user_email;
		$to = 'tomceccarelli2@gmail.com';
		$subject = "Alerte : Nous avons trouvé un article qui peut t'intéresser";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/alerts.html");
		$subtemplate = "";
		// Construction of the mail part where we display each alert with new post
		foreach($alerts_with_new_posts as $variables) {
			$subtemplate .= file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/alert.html");
			foreach($variables as $key => $value) {
				$subtemplate = str_replace('{{ '.$key.' }}', $value, $subtemplate);
			}
		}
		$template = str_replace('{{ username }}', get_userdata($user_id)->data->display_name, $template);
		$template = str_replace('{{ link2 }}', 'https://'.$_SERVER['HTTP_HOST'].'/alertes-enregistrees', $template);
		$template = str_replace('{{ subtemplate }}', $subtemplate, $template);
		wp_mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
	}
}

/*
* Retrieve the posts according to the specifications
* Parameters : int $user_id, array $specifications
* Return : array $postlist
*/
function getAdvancedSearchPosts($user_id, $specifications) {
	

	// We build first the meta_query for the WP_Query

	$meta_query = array(
		'relation'		=> 'AND',
	);
	
	// Depending on the users registered location, we get the scope of the search
	if($specifications[asf_scope] == 'city') {
		// Retrives all the zip codes of the user's city
		$zips = json_decode(file_get_contents('https://geo.api.gouv.fr/communes?codePostal='.get_field('code_postal', 'user_'.$user_id).'&fields=nom,codesPostaux&format=json&geometry=centre'))[0]->codesPostaux;
		$meta_query_field = array(
			'relation' => 'OR'
		);
		foreach($zips as $zip) {
			$meta_query_field[] = array(
				'key'		=> 'zip',
				'value'		=> $zip,
				'compare'	=> '='
			);
		}
		$meta_query[] = $meta_query_field;
	} elseif($specifications[asf_scope] == 'department') {
		// Retrives the department code from the zip code
		$location = substr(get_field('code_postal', 'user_'.$user_id), 0, 2);
		$meta_query[] = array(
			'key'		=> 'dpt',
			'value'		=> $location,
			'compare'	=> '='
		);		
	} elseif($specifications[asf_scope] == 'region') {
		// Retrives all of the department codes of the user's region
		$code_region = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.substr(get_field('code_postal', 'user_'.$user_id), 0, 2).'?fields=region'))->region->code;
		$departements = json_decode(file_get_contents('https://geo.api.gouv.fr/regions/'.$code_region.'/departements?fields=code'));
		$meta_query_field = array(
			'relation' => 'OR'
		);
		foreach($departements as $departement) {
			$meta_query_field[] = array(
				'key'		=> 'dpt',
				'value'		=> $departement->code,
				'compare'	=> '='
			);
		}
		$meta_query[] = $meta_query_field;
	} else {
		// if the user wants to see products elsewhere than its city, department or region
		$elsewhere = urlencode($specifications[asf_scope]);

		// We ask the google maps API to geocode the given string 
		$request_data = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$elsewhere.'%20France&key=AIzaSyCTCLEBH0SHbmMFfYNSDAsDyoGmq4oLFDw'));
		if($request_data->results[0]->address_components[0]->types[0] == "administrative_area_level_1") {
			// If the requested scope is a region

			$region = json_decode(file_get_contents("https://geo.api.gouv.fr/regions?nom=".urlencode($request_data->results[0]->address_components[0]->long_name)."&fields=nom,code"));
			$departements = json_decode(file_get_contents("https://geo.api.gouv.fr/regions/".$region[0]->code."/departements?fields=nom,code"));

			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($departements as $departement) {
				$meta_query_field[] = array(
					'key'		=> 'dpt',
					'value'		=> $departement->code,
					'compare'	=> '='
				);
			}
			$meta_query[] = $meta_query_field;
		} else if($request_data->results[0]->address_components[0]->types[0] == "administrative_area_level_2") {
			// If the requested scope is a department

			$departement = json_decode(file_get_contents("https://geo.api.gouv.fr/departements?nom=".$request_data->results[0]->address_components[0]->long_name."&fields=nom,code,codeRegion"));
			$meta_query[] = array(
				'key'		=> 'dpt',
				'value'		=> $departement[0]->code,
				'compare'	=> '='
			);
		} else if($request_data->results[0]->address_components[0]->types[0] == "locality") {
			// If the requested scope is a city
			
			$communes = json_decode(file_get_contents("https://geo.api.gouv.fr/communes?nom=".$request_data->results[0]->address_components[0]->long_name."&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre"));
			// Since the API returns all the cities matching the query, we loop through the results to get the right one
			foreach($communes as $commune) {
				if($commune->nom == $request_data->results[0]->address_components[0]->long_name) {
					$meta_query_field = array(
						'relation' => 'OR'
					);
					foreach($commune->codesPostaux as $zip) {
						$meta_query_field[] = array(
							'key'		=> 'zip',
							'value'		=> $zip,
							'compare'	=> '='
						);
					}
				}
			}
			$meta_query[] = $meta_query_field;
		} else if(value[0].address_components[0].types[0] == "postal_code") {
			// If the requested scope is a postal_code

			$commune = json_decode(file_get_contents("https://geo.api.gouv.fr/communes?codePostal=".$request_data->results[0]->address_components[0]->long_name."&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre"));
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($commune->codePostaux as $zip) {
				$meta_query_field[] = array(
					'key'		=> 'zip',
					'value'		=> $zip,
					'compare'	=> '='
				);
			}
			$meta_query[] = $meta_query_field;
		} else if($request_data->results[0]->address_components[1]->types[0] == "locality") {
			// If the requested scope is a city (again, but some google data can be mismatched)

			$communes = json_decode(file_get_contents("https://geo.api.gouv.fr/communes?nom=".$request_data->results[0]->address_components[1]->long_name."&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre"));
			// Since the API returns all the cities matching the query, we loop through the results to get the right one
			foreach($communes as $commune) {
				if($commune->nom == $request_data->results[0]->address_components[1]->long_name) {
					$meta_query_field = array(
						'relation' => 'OR'
					);
					foreach($commune->codePostaux as $zip) {
						$meta_query_field[] = array(
							'key'		=> 'zip',
							'value'		=> $zip,
							'compare'	=> '='
						);
					}
				}
			}
			$meta_query[] = $meta_query_field;
		}		
	}

	//For the following specs, we check if the specification has multiple values and we build the query accordingly

	if($specifications[action] != null) {
		if(count($specifications[action]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[action] as $specification) {
				$meta_query_field[] = array(
					'key' => 'action',
					'value' => $specification,
					'compare' => 'LIKE'
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'action',
				'value' => $specifications[action][0],
				'compare' => 'LIKE'
			);
		}
		$meta_query[] = $meta_query_field;
	}
	
	if($specifications[prix_min] != null) {
		$meta_query_field = array(
			'key' => 'prix',
			'value' => $specifications[prix_min],
			'compare' => '>='
		);
		$meta_query[] = $meta_query_field;
	}

	if($specifications[prix_max] != null) {
		$meta_query_field = array(
			'key' => 'prix',
			'value' => $specifications[prix_max],
			'compare' => '<='
		);
		$meta_query[] = $meta_query_field;
	}

	if($specifications[target] != null) {
		$meta_query_field = array(
			'key' => 'categorie-parente',
			'value' => $specifications[target],
			'compare' => '='
		);
		$meta_query[] = $meta_query_field;
	}

	if($specifications[category] != null) {

		$multiple_categories = false;

		if(count($specifications[category]) > 1) {
			$multiple_categories = true;
			$meta_query_field = array(
				'relation' => 'OR'
			);
		} elseif(count($specifications['subcategory-'.$specification_category]) > 1) {
			$multiple_categories = true;
			$meta_query_field = array(
				'relation' => 'OR'
			);
		}

		foreach($specifications[category] as $specification_category) {
			if($specifications['subcategory-'.$specification_category] != null) {
				foreach($specifications['subcategory-'.$specification_category] as $specification_subcategory) {
					if($multiple_categories) {
						$meta_query_field[] = array(
							'key' => 'sous_categorie_'.$specification_category,
							'value' => $specification_subcategory,
							'compare' => '='
						);
					} else {
						$meta_query_field = array(
							'key' => 'sous_categorie_'.$specification_category,
							'value' => $specification_subcategory,
							'compare' => '='
						);
					}
				}
			} else {
				if($multiple_categories) {
					$meta_query_field[] = array(
						'key' => 'categorie-'.$specifications[target],
						'value' => $specification_category,
						'compare' => '='
					);
				} else {
					$meta_query_field = array(
						'key' => 'categorie-'.$specifications[target],
						'value' => $specification_category,
						'compare' => '='
					);
				}
			}
		}
		$meta_query[] = $meta_query_field;
	}

	if($specifications[taille] != null) {
		if(count($specifications[taille]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[category] as $category_specification) {
				foreach($specifications[taille] as $size_specification) {
					$meta_query_field[] = array(
						'key' => 'taille-'.$category_specification.'-'.$specifications[target],
						'value' => $size_specification,
						'compare' => '='
					);
				}
			}
		} else {
			foreach($specifications[category] as $category_specification) {
				$meta_query_field = array(
					'key' => 'taille-'.$category_specification.'-'.$specifications[target],
					'value' => $specifications[taille][0],
					'compare' => '='
				);
			}
		}
		$meta_query[] = $meta_query_field;
	}

	if($specifications[etat] != null) {
		if(count($specifications[etat]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[etat] as $specification) {
				$meta_query_field[] = array(
					'key' => 'etat',
					'value' => $specification,
					'compare' => '='
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'etat',
				'value' => $specifications[etat][0],
				'compare' => '='
			);
		}
		$meta_query[] = $meta_query_field;
	}

	if($specifications[couleur] != null) {
		if(count($specifications[couleur]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[couleur] as $specification) {
				$meta_query_field[] = array(
					'key' => 'couleur',
					'value' => $specification,
					'compare' => '='
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'couleur',
				'value' => $specifications[couleur][0],
				'compare' => '='
			);
		}
		$meta_query[] = $meta_query_field;
	}
	
	if($specifications[imprime] != null) {
		if(count($specifications[imprime]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[imprime] as $specification) {
				$meta_query_field[] = array(
					'key' => 'imprime',
					'value' => $specification,
					'compare' => '='
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'imprime',
				'value' => $specifications[imprime][0],
				'compare' => '='
			);
		}
		$meta_query[] = $meta_query_field;
	}
	
	if($specifications[matiere] != null) {
		if(count($specifications[matiere]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[matiere] as $specification) {
				$meta_query_field[] = array(
					'key' => 'matiere',
					'value' => $specification,
					'compare' => '='
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'matiere',
				'value' => $specifications[matiere][0],
				'compare' => '='
			);
		}
		$meta_query[] = $meta_query_field;
	}
	
	if($specifications[saison] != null) {
		if(count($specifications[saison]) > 1) {
			$meta_query_field = array(
				'relation' => 'OR'
			);
			foreach($specifications[saison] as $specification) {
				$meta_query_field[] = array(
					'key' => 'saison',
					'value' => $specification,
					'compare' => '='
				);
			}
		} else {
			$meta_query_field = array(
				'key' => 'saison',
				'value' => $specifications[saison][0],
				'compare' => '='
			);
		}
		$meta_query[] = $meta_query_field;
	}


	$args = array(
		'post_type'		 => 'produits',
		'author__not_in' => array($user_id),
 		'post_status'	 => 'publish',
		'order'			 => 'DESC',
		'orderby'		 => 'date',
		'nopaging'		 => true,
		'meta_query' 	 => $meta_query
	);

	// For checkUserAlerts()
	if($specifications[time] == 1) {
		$args['date_query'] =  array(
			array(
				'after' => '1 week ago'
			),
		);
	}

	$the_query = new WP_Query( $args );

	if ( $the_query->have_posts() ) {
		$postlist = array();
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_id = get_the_id();
			$postlist[] = $post_id;
		}
	} 
	return $postlist;
}

/*
* Display the postlist
* Parameters : array $postlist
* Return : none
*/
function displayAdvancedSearchPosts($postlist) {
	if ( count($postlist > 0) ) {
		print "<p class='postlist__title'>".count($postlist)." résultat(s)</p>";
		get_template_part( 'partials/interface/interface', 'alert' );
		foreach ($postlist as $post) {
			set_query_var('post', $post);
			get_template_part( 'partials/content/content', 'produits' );
		}
	} else {
		print "<p class='postlist__title'>Aucun résultat...</p>";
	}
}

/**
 * This function will connect wp_mail to your authenticated
 * SMTP server. This improves reliability of wp_mail, and 
 * avoids many potential problems.
 *
 * Values are constants set in wp-config.php. Be sure to
 * define the using the wp_config.php example in this gist.
 *
 * Author:     Chad Butler
 * Author URI: http://butlerblog.com
 *
 * For more information and instructions, see:
 * http://b.utler.co/Y3
 */
add_action( 'phpmailer_init', 'send_smtp_email' );
function send_smtp_email( $phpmailer ) {
	if ( ! is_object( $phpmailer ) ) {
		$phpmailer = (object) $phpmailer;
	}

	$phpmailer->Mailer     = 'smtp';
	$phpmailer->Host       = SMTP_HOST;
	$phpmailer->SMTPAuth   = SMTP_AUTH;
	$phpmailer->Port       = SMTP_PORT;
	$phpmailer->Username   = SMTP_USER;
	$phpmailer->Password   = SMTP_PASS;
	$phpmailer->SMTPSecure = SMTP_SECURE;
	$phpmailer->From       = SMTP_FROM;
	$phpmailer->FromName   = SMTP_NAME;
}


/*
* Set the excerpt for blog posts
* Parameters : none
* Return : none
*/
function setupReadMoreText() {
	global $post;
    return '...&nbsp;<a class="readmore" href="'.get_permalink($post->ID).'">Lire la suite</a>';
}
add_filter( 'excerpt_more', 'setupReadMoreText' );



/*
* Ajax function to delete the featured product, see TODO
* Parameters : int $post_id
* Return : none
*/
function deleteCDC($post_id) {
	wp_delete_post($post_id);
}
add_action( 'delete_cdc_event','deleteCDC',  10, 1);

// TODO
function sendReminder($user_id) {
	$send_mail = false;
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	if(is_array($dressing)) {
		if(count($dressing) == 0) {
			$send_mail = true;
		}
	} else {
		$send_mail = true;
	}
	if($send_mail) {
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Ajoute tes articles sur Swap-Chic";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/relance.html");
		wp_mail( $to, $subject, $template, $headers);
	}
}
add_action( 'send_reminder_event', 'sendReminder',  10 ,1);


/*
* Cron job to send a first reminder by mail if the user has not registered any product
* Parameters : int $user_id
* Return : none
*/
function sendFirstReminder($user_id) {
	$send_mail = false;
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	// ACF does not initialize the 'produits' field as an array right away, so we have to check first if it's one or not
	if(is_array($dressing)) {
		if(count($dressing) == 0) {
			$send_mail = true;
		}
	} else {
		$send_mail = true;
	}
	if($send_mail) {
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Ajoute tes articles sur Swap-Chic";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/relance1.html");
		wp_mail( $to, $subject, $template, $headers);
	}
}
add_action( 'send_first_reminder_event', 'sendFirstReminder',  10 ,1);

/*
* Cron job to send a second reminder by mail if the user has not registered any product
* Parameters : int $user_id
* Return : none
*/
function sendSecondReminder($user_id) {
	$send_mail = false;
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	if(is_array($dressing)) {
		if(count($dressing) == 0) {
			$send_mail = true;
		}
	} else {
		$send_mail = true;
	}
	if($send_mail) {
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Publie ton premier article sur Swap-Chic !";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/relance2.html");
		wp_mail( $to, $subject, $template, $headers);
	}
}
add_action( 'send_second_reminder_event', 'sendSecondReminder',  10 ,1);

/*
* Cron job to send a third reminder by mail if the user has not registered any product
* Parameters : int $user_id
* Return : none
*/
function sendThirdReminder($user_id) {
	$send_mail = false;
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	if(is_array($dressing)) {
		if(count($dressing) == 0) {
			$send_mail = true;
		}
	} else {
		$send_mail = true;
	}
	if($send_mail) {
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Pense à ajouter tes articles sur Swap-Chic";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/relance3.html");
		wp_mail( $to, $subject, $template, $headers);
	}
}
add_action( 'send_third_reminder_event', 'sendThirdReminder',  10 ,1);

/*
* Cron job to send an incentive by mail if the user registered at least one product
* Parameters : int $user_id
* Return : none
*/
function sendIncentive($user_id) {
	$send_mail = false;
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	if(is_array($dressing)) {
		if(count($dressing) > 0) {
			$send_mail = true;
		}
	} else {
		$send_mail = false;
	}
	if($send_mail) {
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Continue à utiliser Swap-Chic pour vendre et échanger !";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/incentive.html");
		wp_mail( $to, $subject, $template, $headers);
	}
}
add_action( 'send_incentive_event', 'sendIncentive',  10 ,1);

/*
* Log the user in, see https://developer.wordpress.org/reference/functions/wp_signon/
* Parameters : array $credentials
* Return : WP_User on success, WP_Error on failure
*/
function loginUser($credentials) {
	return wp_signon($credentials);
}

/*
* On swap-place save, sets the zip and tge department code values automatically
* Parameters : int $post_id
* Return :none
*/
function setSwapplaceGeolocation($post_id) {
	if(get_post_type($post_id) == 'swapplaces') {
		$adresse = str_replace(' ', '+', get_field('adresse', $post_id));
		// Find the zip in the address
		preg_match('/[0-9]{5}/', $adresse, $matches);
		update_field('zip', $matches[0], $post_id);
		update_field('dpt',substr($matches[0], 0, 2), $post_id);
		swapplaceGet($post_id, $adresse);
	}
}
add_action('acf/save_post', 'setSwapplaceGeolocation');

/*
* Register a new user, see https://developer.wordpress.org/reference/functions/wp_insert_user/
* Parameters : array $wp_data, array $acf_data
* Return : WP_User on success, WP_Error on failure
*/
function registerUser($wp_data, $acf_data) {
	$user_register = wp_insert_user($wp_data);
	if(!is_wp_error($user_register)) {
		insertACFFieldsToNewUser($user_register, $acf_data);
		// Send welcome mail
		if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
			$to = $wp_data['user_email'];
			$subject = "Bienvenue sur Swap-Chic !";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['name'] = $wp_data['user_login'];
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/welcome.html");
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);

			// Send new user alert to admin
			$to = 'checking@swap-chic.com';
			$subject = "Nouvelle membre";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['img'] = get_field('photo_profil', 'user_'.$user_register);
			$variables['name'] = $wp_data['user_login'];
			$variables['ville'] = get_field('ville', 'user_'.$user_register);
			$variables['mail'] = $wp_data['user_email'];
			$variables['link'] = 'https://'.$_SERVER['HTTP_HOST'].'/wp-admin/user-edit.php?user_id='.$user_register.'&wp_http_referer=%2Fwp-admin%2Fusers.php';
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/new_user.html" );
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
		}
		
		// Schedule the 3 reminder and incentive mails, respectively 24 hours, 7 days, 20 days and 15 days after register
		wp_schedule_single_event( time() + 86400, 'send_first_reminder_event', array($user_register) );
		wp_schedule_single_event( time() + 604800, 'send_second_reminder_event', array($user_register) );
		wp_schedule_single_event( time() + 1728000, 'send_third_reminder_event', array($user_register) );
		wp_schedule_single_event( time() + 1296000, 'send_incentive_event', array($user_register) );

		// Login the newly registered user
		$credentials = array(
			'user_login' => $wp_data['user_login'],
			'user_password' => $wp_data['user_pass'],
			'remember' => true
		);
		return loginUser($credentials);
	} else {
		return $user_register;
	}
}

/*
* Insert the entered values from the sign up form inside the respective acf fields of the new user
* Parameters : int $user_id, array $acf_data
* Return : none
*/
function insertACFFieldsToNewUser($user_id, $acf_data) {

	$image = saveNewProfilePicture($user_id, $acf_data['profile_picture']);

	update_field('photo_profil', $image, 'user_'.$user_id);
	update_field('code_postal', $acf_data['user_zip'], 'user_'.$user_id);

	$user_zip = $acf_data['user_zip'];
	// Get the city name from the zip code
	$data = json_decode(file_get_contents('https://geo.api.gouv.fr/communes?codePostal='.$user_zip.'&fields=nom,codesPostaux&format=json&geometry=centre'));
	update_field('ville', $data[0]->nom, 'user_'.$user_id);

	setUserLatLng($user_id, $user_zip);

	// Create user's dressing
	$post_data = array(
		'post_type' => 'dressings',
		'post_title' => 'Dressing de '.ucfirst(get_userdata($user_id)->data->user_login),
		'post_status' => 'publish',
		'post_author' => $user_id
	);

	$post_id = wp_insert_post( $post_data );

	update_field('proprietaire', $user_id, $post_id);
	update_field('dressing', $post_id, 'user_'.$user_id);
}

/*
* Register the profile picture as an image in the media library
* Parameters : int $user_id, string (base64) $image
* Return : int $attach_id
*/
function saveNewProfilePicture($user_id, $image){

	$upload_dir  = wp_upload_dir();
	$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;

	$title = $user_id;
	$base64_img = $image;

	$img             = str_replace( 'data:image/jpeg;base64,', '', $base64_img );
	$img             = str_replace( ' ', '+', $img );
	$decoded         = base64_decode( $img );
	$filename        = $title . '.jpeg';
	$file_type       = 'image/jpeg';
	$hashed_filename = md5( $filename . microtime() ) . '_' . $filename;

	// Save the image in the uploads directory.
	$upload_file = file_put_contents( $upload_path . $hashed_filename, $decoded );
	$attachment = array(
		'post_mime_type' => $file_type,
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $upload_dir['baseurl'] . '/' . 'users' . '/' . basename( $hashed_filename )
	);

	$attach_id = wp_insert_attachment( $attachment, $upload_dir['basedir'] . '/' . 'users' . '/' . $hashed_filename );

	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . $hashed_filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	return $attach_id;
}

/*
* Sets the user latitude and longitude fields
* Parameters : int $user_id, string $user_zip
* Return : none
*/
function setUserLatLng($user_id, $user_zip) {
	$url = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$user_zip.'%20France&key=AIzaSyCTCLEBH0SHbmMFfYNSDAsDyoGmq4oLFDw'));
	if($url->status == 'OK') {
		update_field('lat', $url->results[0]->geometry->location->lat, 'user_'.$user_id);
		update_field('lng', $url->results[0]->geometry->location->lng, 'user_'.$user_id);
	} elseif($url->status == 'OVER_QUERY_LIMIT') {
		sleep(1);
		setUserLatLng($user_id, $user_zip);
	} else {
		echo $user_id." FAIL\n";
	}
}

/*
* Redirect the user to the sign in page after password reset
* Parameters : none
* Return : none
*/
function wpse_lost_password_redirect() {
    wp_redirect('https://'.$_SERVER['HTTP_HOST'].'/sign-in/'); 
    exit;
}
add_action('after_password_reset', 'wpse_lost_password_redirect');

/*
* Redirect the user to the sign in page after password reset
* Parameters : none
* Return : none
*/
function wpse_lost_password_redirect_after_mail() {

    // Check if have submitted 
    $confirm = ( isset($_GET['checkemail'] ) ? $_GET['checkemail'] : '' );

    if( $confirm ) {
        wp_redirect( 'https://'.$_SERVER['HTTP_HOST'].'/sign-in/?mail=sent' ); 
        exit;
    }
}
add_action('login_headerurl', 'wpse_lost_password_redirect_after_mail');


/*
* Register a new product, see https://developer.wordpress.org/reference/functions/wp_insert_post/
* Parameters : array $wp_data, array $acf_data
* Return : none on success, WP_Error on failure
*/
function addProduct($wp_data, $acf_data) {
	$product_register = wp_insert_post($wp_data);
	if(!is_wp_error($product_register)) {
		$user_id = get_current_user_id();
		insertACFFieldsToNewProduct($product_register, $acf_data);

		// Send new product mail to admin 
		if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
			$name = ucfirst(get_userdata($user_id)->data->user_login);
			$to = 'checking@swap-chic.com';
			$subject = $name." a ajouté un nouveau produit";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['name'] = $name;
			$variables['img1'] = get_field('images', $product_register)[0];
			$variables['img2'] = get_field('images', $product_register)[1];
			$variables['img3'] = get_field('images', $product_register)[2];
			$variables['p_name'] = get_the_title($product_register);
			//$variables['marque'] = get_field('marque', $product_register) ;
			$variables['link'] = 'https://'.$_SERVER['HTTP_HOST'].'/?post_type=produits&p='.$product_register;
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/new_product.html" );
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
		}
		header('Location: '.get_permalink(get_field('dressing', 'user_'.get_current_user_id())).'?from_product_add=1');
		exit();
	} else {
		return $product_register;
	}
}


/*
* Insert the entered values from the product adding form inside the respective acf fields of the new product
* Parameters : int $post_id, array $acf_data
* Return : none
*/
function insertACFFieldsToNewProduct($post_id, $acf_data) {

	$user_id = get_current_user_id();

	$images = saveNewProductImages($post_id, $acf_data['images']);

	// Inserting images to gallery field
	update_field('field_5dea1c131306f', $images, $post_id);

	update_field('action', $acf_data['action'], $post_id);

	if(isset($acf_data['price'])) {
		update_field('prix', $acf_data['price'], $post_id);
	}

	update_field('categorie-parente', ucfirst($acf_data['categorie-parent']), $post_id);

	if($acf_data['categorie-parent'] == 'femme') {
		update_field('categorie-femme', $acf_data['categorie'], $post_id);
		if($acf_data['categorie'] == 'lingerie'
		|| $acf_data['categorie'] == 'vetements') {
			update_field('taille-vetements-femme', $acf_data['taille'], $post_id);
		} elseif($acf_data['categorie'] == 'chaussures') {
			update_field('taille-chaussures-femme', $acf_data['taille'], $post_id);
		}
	} elseif($acf_data['categorie-parent'] == 'enfant') {
		update_field('categorie-enfant', $acf_data['categorie'], $post_id);
		if($acf_data['categorie'] == 'vetements-enfant') {
			update_field('taille-vetements-enfant', $acf_data['taille'], $post_id);
		} elseif($acf_data['categorie-enfant'] == 'chaussures-enfant') {
			update_field('taille-chaussures-enfant', $acf_data['taille'], $post_id);
		}
	}

	if($acf_data['categorie'] == 'accessoires') {
		update_field('sous_categorie_accessoires', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'bijoux ') {
		update_field('sous_categorie_bijoux', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'chaussures') {
		update_field('sous_categorie_chaussures', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'lingerie') {
		update_field('sous_categorie_lingerie', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'makeup') {
		update_field('sous_categorie_makeup', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'sacs') {
		update_field('sous_categorie_sacs', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'sports') {
		update_field('sous_categorie_sports', $acf_data['sous-categorie'], $post_id);
	} else if($acf_data['categorie'] == 'vetements') {
		update_field('sous_categorie_vetements', $acf_data['sous-categorie'], $post_id);
	}

	update_field('etat', $acf_data['etat'], $post_id);
	update_field('matiere', $acf_data['matiere'], $post_id);
	update_field('couleur', $acf_data['couleur'], $post_id);
	update_field('imprime', $acf_data['imprime'], $post_id);
	update_field('saison', $acf_data['saison'], $post_id);
	update_field('marque', $acf_data['marque'], $post_id);

	update_field('proprietaire', $user_id, $post_id);
	
	$post_location = get_field('code_postal', 'user_'.$user_id);
	update_field('zip', $post_location, $post_id);
	update_field('dpt', substr($post_location, 0, 2), $post_id);

	$dressing_id = get_field('dressing', 'user_'.$user_id);
	$dressing = get_field('produits', $dressing_id);
	$dressing[] = $post_id;

	update_field('produits', $dressing, $dressing_id);
}

/*
* Register the product pictures as images in the media library
* Parameters : int $post_id, array $images
* Return : array $image_ids
*/
function saveNewProductImages($post_id, $images){

	$upload_dir  = wp_upload_dir();
	$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;

	// Create a directory for the images
	if(is_dir($upload_path . $post_id)) {
		chmod($upload_path . $post_id, 0755);
		$upload_path = $upload_path . $post_id . DIRECTORY_SEPARATOR;
	} else {
		if(mkdir($upload_path . $post_id, 0755)) {
			$upload_path = $upload_path . $post_id . DIRECTORY_SEPARATOR;
		}
	}

	$title = 0;
	foreach($images as $base64_img){
		$img             = str_replace( 'data:image/jpeg;base64,', '', $base64_img );
		$img             = str_replace( ' ', '+', $img );
		$decoded         = base64_decode( $img );
		$filename        = $title . '.jpeg';
		$file_type       = 'image/jpeg';
		$hashed_filename = md5( $filename . microtime() ) . '_' . $filename;
		$title++;

		// Save the image in the uploads directory.
		$upload_file = file_put_contents( $upload_path . $hashed_filename, $decoded );
		$attachment = array(
			'post_mime_type' => $file_type,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $hashed_filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $upload_dir['baseurl'] . '/' . 'products' . '/' . $post_id . '/' . basename( $hashed_filename )
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_dir['basedir'] . '/' . 'products' . '/' .  $post_id . '/' . $hashed_filename );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . $hashed_filename );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		$image_ids[] = $attach_id;
	}
	return $image_ids;
}

/* 
* Validate a product
* Parameters : int $post_id
* Return : none
*/
function validate($post_id){
	$user_id = get_field('proprietaire', $post_id)['ID'];
	$dressing_id = get_field('dressing', 'user_'.$user_id);

	$removebg_response = setProductThumbnail($post_id);
	$status_code = $removebg_response->getStatusCode();
	
	$image = get_field('images', $post_id)[0];


	if($status_code == 200) {
		// If the background removal went correctly, we register the new image and set it as thumbnail of the product 
		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . 'no-bg' . DIRECTORY_SEPARATOR;

		$file = substr($image, strrpos($image, '/') + 1, strlen($image));
		$file_name = substr($file, 0, strlen($file) - 4)."-no-bg.png";
		$hashed_file_name = md5( $file_name . microtime() ) . '_' . $file_name;

		$fp = fopen($upload_path.$hashed_file_name, "wb");
		fwrite($fp, $removebg_response->getBody());
		fclose($fp);

		$attachment = array(
			'post_mime_type' => 'image/png',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($hashed_file_name) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $upload_dir['baseurl'] . '/' . 'no-bg' . '/' . basename( $hashed_file_name )
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_dir['basedir'] . '/' . 'no-bg' . '/' . $hashed_file_name );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . $hashed_file_name );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail($post_id, $attach_id);

		wp_update_post(
				array(
				'ID'    =>  $post_id,
				'post_status'   =>  'publish'
			)
		);
		
		$product = get_the_title($post_id);

		// Send mail to user 
		if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
			$to = get_userdata($user_id)->data->user_email;
			$subject = "Bravo, ton article ".$product." figure maintenant dans notre catalogue !";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['name'] = ucfirst(get_userdata($user_id)->data->user_login);
			$variables['product'] = $product;
			$variables['link'] = 'https://'.$_SERVER['HTTP_HOST'].'/?post_type=produits&p='.$post_id;
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/accept.html");
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
		}
	}
	return $status_code;
}

/*
* Create a PNG image from the first image of the product gallery, register it in the media library and set it as the product thumbnail, see https://www.remove.bg/api
* Parameters : int post_id
* Return : int $attach_id
*/
function setProductThumbnail($post_id) {
	$image = get_field('images', $post_id)[0];

	$client = new GuzzleHttp\Client();
	
	try {
		$response = $client->post('https://api.remove.bg/v1.0/removebg', [
			'multipart' => [
				[
					'name'     => 'image_url',
					'contents' => $image
				],
				[
					'name'     => 'size',
					'contents' => 'auto'
				]
			],
			'headers' => [
				'X-Api-Key' => 'mvutm18ge5PS7wZHF7A4FcuV'
			]
		]);
	} catch (GuzzleHttp\Exception\ClientException $e) {
		$response = $e->getResponse();
		return $response;
	}
	return $response;
}

/*
* Create a PNG image from the first image of the product gallery, register it in the media library and set it as the product thumbnail, see https://www.remove.bg/api
* Contrary to setProductThumbnail(), this one fires on post status change
* Parameters : int post_id
* Return : int $attach_id
*/
function setProductThumbnailFromBO($new_status, $old_status, $post) {
	if('publish' === $new_status && 'publish' !== $old_status && $post->post_type === 'produits') {
		$image = get_field('images', $post->ID)[0];

		$client = new GuzzleHttp\Client();
		$res = $client->post('https://api.remove.bg/v1.0/removebg', [
			'multipart' => [
				[
					'name'     => 'image_url',
					'contents' => $image
				],
				[
					'name'     => 'size',
					'contents' => 'auto'
				]
			],
			'headers' => [
				'X-Api-Key' => 'mvutm18ge5PS7wZHF7A4FcuV '
			]
		]);

		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . 'no-bg' . DIRECTORY_SEPARATOR;

		$file = substr($image, strrpos($image, '/') + 1, strlen($image));
		$file_name = substr($file_name, 0, strlen($file_name) - 4)."-no-bg.png";

		$hashed_file_name = md5( $file_name . microtime() ) . '_' . $file_name;

		$fp = fopen($upload_path.$hashed_file_name, "wb");
		fwrite($fp, $res->getBody());
		fclose($fp);

		// $image_resized = wp_get_image_editor( $upload_path.$hashed_file_name );
		// if ( ! is_wp_error( $image_resized ) ) {
		// 	$image_resized->resize( 600, 600);
		// 	$image_resized->save( $upload_path.'600x600-'.$hashed_file_name );
		// }

		$attachment = array(
			'post_mime_type' => 'image/png',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($hashed_file_name) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $upload_dir['baseurl'] . '/' . 'no-bg' . '/' . basename( $hashed_file_name )
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_dir['basedir'] . '/' . 'no-bg' . '/' . $hashed_file_name );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . $hashed_file_name );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail($post->ID, $attach_id);
	}
}
// It is not needed right now so we do not register the action
//add_action('transition_post_status', 'setProductThumbnailFromBO', 10, 3);


/*
* Split the url at each slash
* Parameters : none
* Return : array
*/
function getPath() {
	return explode('/', $_SERVER['REQUEST_URI']);
}

/*
* Decide if the loader is displayed or not
* Parameters : array $path
* Return : bool
*/
function displayLoader($path) {
	if($path[1] == 'actualites') {
		return true;
	} elseif($path[1] == 'catalogue') {
		return true;
	} elseif($path[1] == 'messagerie' && $path[2] == 'nouvelle-discussion') {
		return true;
	} else {
		return false;
	}
}

/*
* Decide if the header is displayed or not
* Parameters : array $path
* Return : bool
*/
function displayHeader($path) {
	if($path[1] == 'produits' && !empty($path[2])) {
		return false;
	} elseif($path[1] == 'swap-places' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'dressings' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'messagerie' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'introduction') {
		return false;
	}  elseif($path[1] == 'sign-in') {
		return false;
	}  elseif($path[1] == 'ajouter-produit') {
		return false;
	}  elseif($path[1] == 'editer-produit') {
		return false;
	}  elseif($path[1] == 'editer-profil') {
		return false;
	}  elseif(!empty($path[1])) {
		return true;
	} else {
		return false;
	}
}

/*
* Decide if the footer is displayed or not
* Parameters : array $path
* Return : bool
*/
function displayFooter($path) {
	if($path[1] == 'produits' && !empty($path[2])) {
		return false;
	} if($path[1] == 'swap-places' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'dressings' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'messagerie' && !empty($path[2])) {
		return false;
	}  elseif($path[1] == 'discussions') {
		return false;
	}  elseif($path[1] == 'introduction') {
		return false;
	}  elseif($path[1] == 'sign-in') {
		return false;
	}  elseif($path[1] == 'ajouter-produit') {
		return false;
	}  elseif($path[1] == 'editer-produit') {
		return false;
	} elseif(!empty($path[1])) {
		return true;
	} else {
		return false;
	}
}

/*
* Turn the scope parameters in a url search string or get the user zip code if they are not set
* Parameters : array $get (should simply be $_GET ?)
* Return : string $scope
*/
function getScopeString($get) {
	$scope = '';
	if(isset($get['scope'])) {
		$scope = "?scope=".$get['scope'];
		if(isset($get['more'])) {
			$scope .= "&more=".$get['more'];
			if(isset($get['even_more'])) {
				$scope .= "&even_more=".$get['even_more'];
				return $scope;
			}
			return $scope;
		}
		return $scope;
	} else {
		$user = wp_get_current_user();
		$user_id = $user->data->ID;
		$scope = get_field('code_postal', 'user_'.$user_id );
		return $scope;
	}
}

/*
* Turn the scope parameters in a array  or get the user zip code if they are not set
* Parameters : array $get (should simply be $_GET ?)
* Return : array $scope
*/
function getScope($get) {
	$scope_array = [];
	if(isset($get['scope'])) {
		if(strpos($get['scope'], '%2C')) {
			$scope = explode('%2C', $get['scope']);
		} elseif(strpos($get['scope'], ',')) {
			$scope = explode(',', $get['scope']);
		} else {
			$scope = array($get['scope']);
		}
		$scope_array['scope'] = $scope;
		if(isset($get['more'])) {
			if(strpos($get['more'], '%2C')) {
				$more = explode('%2C', $get['more']);
			} elseif(strpos($get['more'], ',')) {
				$more = explode(',', $get['more']);
			} else {
				$more = array($get['more']);
			}
			$scope_array['more'] = $more;
			if(isset($get['even_more'])) {
				if(strpos($get['even_more'], '%2C')) {
					$even_more = explode('%2C', $get['even_more']);
				} elseif(strpos($get['even_more'], ',')) {
					$even_more = explode(',', $get['even_more']);
				} else {
					$even_more = array($get['even_more']);
				}
				$scope_array['even_more'] = $even_more;
			}
		}
		return $scope_array;
	} else {
		$user = wp_get_current_user();
		$user_id = $user->data->ID;
		$scope_array['scope'] = get_field('code_postal', 'user_'.$user_id );
		return $scope_array;
	}
	return false;
}

/*
* Says what the lowest scope level is
* Parameters : array $get (should simply be $_GET ?)
* Return : string 'ville', 'departement', or 'region'
*/
function getLowestScopeLevel($get) {
	if(isset($get['scope'])) {
		if(strpos($get['scope'], '%2C')) {
			$scope = explode('%2C', $get['scope']);
			if(getScopeFormat($scope) == 'postal_code') {
				return 'ville';
			} else {
				return 'region';
			}
		} elseif(strpos($get['scope'], ',')) {
			$scope = explode(',', $get['scope']);
			if(getScopeFormat($scope) == 'postal_code') {
				return 'ville';
			} else {
				return 'region';
			}
		} else {
			$scope = array($get['scope']);
			if(getScopeFormat($scope) == 'postal_code') {
				return 'ville';
			} else {
				return 'departement';
			}
		}
	}
	return false;
}

/*
* Says what the scope format is
* Parameters : array $scope CAREFUL THIS IS ACTUALLY $scope['scope']
* Return : string 'postal_code' or 'dpt_code'
*/
function getScopeFormat($scope) {
	if(strlen($scope[0]) > 2) {
		return 'postal_code';
	} else {
		return 'dpt_code';
	}
}

/*
* Retrive the latitude and longitude of the first scope value
* Parameters : array $scope
* Return : array 
*/
function getScopeLatLng($scope) {
	$url = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$scope['scope'][0].'%20France&key=AIzaSyCTCLEBH0SHbmMFfYNSDAsDyoGmq4oLFDw'));
	if($url->status == 'OK') {
		$scope_lat = $url->results[0]->geometry->location->lat;
		$scope_lng = $url->results[0]->geometry->location->lng;
		return array($scope_lat, $scope_lng);
	} elseif($url->status == 'OVER_QUERY_LIMIT') {
		sleep(1);
		getScopeLatLng($scope);
	} else {
		echo "VOTRE LOCALISATION NE PEUT PAS DÉTECTER";
	}
}

/*
* Says in which scope level the post belongs
* Parameters : int $post_id, string $post_type, array $scope_array
* Return : string 'scope', 'more', or 'even_more' 
*/
function getPostScope($post_id, $post_type, $scope_array) {
	// We get the post location according to the post type
	if( $post_type == 'produits' ) {
		$post_owner = get_field('proprietaire', $post_id);
		$post_location = get_field('code_postal', 'user_'.$post_owner['ID']);
	} elseif( $post_type == 'swapplaces' ) {
		preg_match('/[0-9]{5}/', get_field('adresse', $post_id), $matches);
		if(!empty($matches)) {
			$post_location = $matches[0];
		} else {
			$post_location = false;
		}
	}
	
	// We compare the post location to the locations of each scope level
	foreach($scope_array as $key => $val) {
		$scope_format = getScopeFormat($scope_array[$key]);
		if($post_location != false) {
			if($scope_format == 'postal_code') {
				if(in_array($post_location, $scope_array[$key])) {
					return $key;
				}
			} else {
				if(in_array(substr($post_location, 0, 2), $scope_array[$key])) {
					return $key;
				}
			}
		}
	}
	return false;
}

/*
* Says in which scope level the user belongs
* Parameters : int $user_id, array $scope_array
* Return : array 
*/
function getUserScope($user_id, $scope_array) {
	$user_location = get_field('code_postal', 'user_'.$user_id);
	foreach($scope_array as $key => $val) {
		$scope_format = getScopeFormat($scope_array[$key]);
		if($scope_format == 'postal_code') {
			if(in_array($user_location, $scope_array[$key])) {
				return $key;
			}
		} else {
			if(in_array(substr($user_location, 0, 2), $scope_array[$key])) {
				return $key;
			}
		}
	}
	return false;
}

/*
* Says in which scope level the comment belongs
* Parameters : int $comment_id, array $scope_array
* Return : array 
*/
function getCommentScope($comment_id, $scope_array) {
	$comment_post_id = get_comment($comment_id)->comment_post_ID;
	$comment_post_type = get_post_type($comment_post_id);

	if($comment_post_type == 'produits'){
		$owner = get_field('proprietaire', $comment_post_id)['ID'];
		$post_location = get_field('code_postal', 'user_'.$owner);
	} elseif($comment_post_type == 'swapplaces') {
		preg_match('/[0-9]{5}/', get_field('adresse', $comment_post_id), $matches);
		$post_location = $matches[0];
	} elseif($comment_post_type == 'dressings') {
		$owner = get_field('proprietaire', $comment_post_id)['ID'];
		$post_location = get_field('code_postal', 'user_'.$owner);
	}

	foreach($scope_array as $key => $val) {
		$scope_format = getScopeFormat($scope_array[$key]);
		if($scope_format == 'postal_code') {
			if(in_array($post_location, $scope_array[$key])) {
				return $key;
			}
		} else {
			if(in_array(substr($post_location, 0, 2), $scope_array[$key])) {
				return $key;
			}
		}
	}
	return false;
}

/*
* Check if the post is in the scope level
* Parameters : int $post_id, string $post_type, array $scope
* Return : bool 
*/
function checkPostLocation($post_id, $post_type, $scope) {
	if( $post_type == 'produits' ) {
		$post_owner = get_field('proprietaire', $post_id);
		$post_location = get_field('code_postal', 'user_'.$post_owner['ID']);
	} elseif( $post_type == 'swapplaces' ) {
		preg_match('/[0-9]{5}/', get_field('adresse', $post_id), $matches);
		$post_location = $matches[0];
	}
	if(is_array($scope)) {
		foreach($scope as $scope_location) {
			if(in_array($post_location, $scope_location)) {
				return true;
			}
		}
	} else {
		if(in_array($post_location, $scope_location)) {
			return true;
		}
	}
	return false;
}

/*
* Check if the featured post is in the city in the scope
* Parameters : int $post_id, string $post_type, array $scope
* Return : bool 
*/
function checkFeaturedPostCity($post_id, $scope) {
	if(getScopeFormat($scope['scope']) == "postal_code") {
		$post_owner = get_field('proprietaire', $post_id);
		$post_location = get_field('ville', 'user_'.$post_owner['ID']);
		$ad1 = json_decode(file_get_contents('https://geo.api.gouv.fr/communes/?codePostal='.$scope['scope'][0].'&fields=nom,code,codeRegion'));
		if($ad1[0]->nom == $post_location) {
			return true;
		} elseif($ad1[0]->nom == 'Marseille' && $post_location == 'Aix-en-Provence') {
			return true;
		} elseif($ad1[0]->nom == 'Aix-en-Provence' && $post_location == 'Marseille') {
			return true;
		}
	}
	return false;
}

/*
* Check if the user is in the region in the scope
* Parameters : int $user_id, array $scope
* Return : bool 
*/
function checkUserRegion($user_id, $scope) {
	$user_location = substr(get_field('code_postal', 'user_'.$user_id), 0, 2);
	$ad1 = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.$user_location.'?fields=nom,code,codeRegion'));
	if(is_array($scope)) {
		foreach($scope as $scope_location) {
			if(strlen($scope_location[0]) > 2) {
				$scope_location = substr($scope_location[0], 0, 2);
			}
			$ad2 = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.$scope_location.'?fields=nom,code,codeRegion'));
			if($ad1->codeRegion == $ad2->codeRegion) {
				return true;
			}
		}
	} else {
		if(strlen($scope_location[0]) > 2) {
			$scope_location = substr($scope_location[0], 0, 2);
		}
		$ad2 = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.$scope_location.'?fields=nom,code,codeRegion'));
		if($ad1->codeRegion == $ad2->codeRegion) {
			return true;
		}
	}
	return false;
}

/*
* Says in which scope level the user brlongs
* Parameters : int $user_id, array $scope
* Return : string 'scope', 'more', or 'even_more' 
*/
function checkUserLocation($user_id, $scope) {
	$user_location = get_field('code_postal', 'user_'.$user_id);
	foreach($scope_array as $key => $val) {
		$scope_format = getScopeFormat($scope_array[$key]);
		if($scope_format == 'postal_code') {
			if(in_array($user_location, $scope_array[$key])) {
				return $key;
			}
		} else {
			if(in_array(substr($user_location, 0, 2), $scope_array[$key])) {
				return $key;
			}
		}
	}
	return false;
}


/*
* Check if user has at least one published product in his dressing
* Parameters : int $user_id
* Return : bool
*/
function userHasProducts($user_id) {
	$dressing = get_field('produits', get_field('dressing', 'user_'.$user_id));
	if($dressing && count($dressing) > 0) {
		foreach($dressing as $product) {
			if(get_post_status($product) == 'publish') {
				return true;
			}
		}
	}
	return false;
}

/*
* Check if the comment is a child
* Parameters : int $comment_id
* Return : false if not or comment parent id if yes
*/
function isCommentChild($comment_id) {
	$comment = get_comment($comment_id);
	if($comment->comment_parent == 0){
		return false;
	} else {
		return $comment->comment_parent;
	}
}

/*
* Get the title of the commented post
* Parameters : int $post_id
* Return : string
*/
function getCommentPost($post_id) {
	$post = get_post($post_id);
	if($post->post_type == "dressings") {
		if(get_field('proprietaire', $post_id)['display_name'] == wp_get_current_user()->data->display_name) {
			return 'votre dressing';
		} else {
			return 'le <b>'.$post->post_title.'</b>';
		}
	// } elseif($post->post_type == "produits") {
	// 	return $post->post_title;
	} else {
		return '<b>'.$post->post_title.'</b>';
	}
}

/*
* Check if the comment is in the scope level
* Parameters : int $post_id
* Return : string
*/
function checkCommentLocation($comment_id, $scope_location) {
	$comment = get_comment($comment_id);
	$comment_author = $comment->user_id;
	return checkUserLocation($comment_author, $scope_location);
}

/*
* Gets the number of likes of a comment
* Parameters : int $comm_id
* Return : int
*/
function getCommentLikesNumber($comm_id) {
	$likes = get_field('likes', 'comment_'.$comm_id);
	if($likes) {
		return count($likes);
	} else {
		return 0;
	}
}

/*
* Gets the number of childs of a comment
* Parameters : int $comm_id
* Return : int
*/
function getCommentChildNumber($comm_id) {
	$args = array(
        'type'           => 'comment',
		'post_status'    => 'publish',
		'parent'		 => $comm_id,
        'order'          => 'DESC',
        'orderby'        => 'comment_date',
        'nopaging' => true
    );
    $comments_query = new WP_Comment_Query;
	$comments = $comments_query->query( $args );

	return count($comments);
}

/*
* Generate the product title form its subcategory and its brand
* Parameters : int $post_id
* Return : string
*/
function generateProductTitle($post_id) {

	$categorie = get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id);
	$sous_categorie = get_field('sous_categorie_'.get_field('categorie-'.strtolower(get_field('categorie-parente', $post_id)), $post_id)['value'], $post_id)['label'];
	$marque = get_field('marque', $post_id);

	if($sous_categorie == 'Autres') {
		if($categorie['value'] == 'lingerie') {
			$sous_categorie = 'Article';
		} elseif($categorie['value'] == 'makeup') {
			$sous_categorie = 'Produit de beauté';
		} elseif($categorie['value'] == 'sports') {
			$sous_categorie = 'sport';
		}
	}

	if($categorie['value'] == 'sports') {
		return 'Équipement de '.$sous_categorie.' '.$marque;
	} else {
		return $sous_categorie.' '.$marque;
	}
}

/*
* Get the size of the product
* Parameters : int $post_id
* Return : string
*/
function getProductSize($post_id) {
	if(get_field('taille-vetements-femme', $post_id)) {
		return  get_field('taille-vetements-femme', $post_id);
	} elseif(get_field('taille-chaussures-femme', $post_id)) {
		return get_field('taille-chaussures-femme', $post_id);
	} elseif(get_field('taille-chaussures-enfant', $post_id)) {
		return get_field('taille-chaussures-enfant', $post_id);
	}elseif(get_field('taille-vetements-enfant', $post_id)) {
		return get_field('taille-vetements-enfant', $post_id);
	} else {
		return false;
	}
}

/*
* Get the number of likes of a post
* Parameters : int $post_id
* Return : int
*/
function getLikesNumber($post_id) {
	$likes = get_field('likes', $post_id);
	if($likes) {
		return count($likes);
	} else {
		return 0;
	}
}

/*
* Get the number of comments of a post
* Parameters : int $post_id
* Return : int
*/
function getCommentsNumber($post_id) {
	return get_comments_number($post_id);
}

/*
* Generate the html to output in the chat if a user share a product
* Parameters : int $post_id
* Return : string
*/
function productToChat($post_id) {
	$user = get_field('proprietaire', $post_id);
	$image = str_replace('"', '\'', get_the_post_thumbnail($post_id));
	$action = get_field('action', $post_id);
	$post_type = 'produits';
	$message = "<div data-id='". $post_id ."' class='produit' onclick='openChatProduct(". $post_id .")'><div class='produit-carousel'>".$image."</div><div class='infos-wrapper'><h3 class='h1'>". get_the_title($post_id) ."</h3><div class='user'><img src='". get_field('photo_profil', 'user_'.$user['ID']) ."' alt=''><p><a href='". get_permalink(get_field('dressing', 'user_'.$user['ID'])) ."'>". ucfirst($user['display_name']) ."</a></p></div>";

	if( $action[0] == 'À vendre' && count($action) == 1) {
	    $message .= "<div class='infos'><p><b>". get_field('prix', $post_id)."€</b></p></div>";
	} elseif($action[1]) {
	    $message .= "<div class='infos'><p><b>À swaper ou ".get_field('prix', $post_id)."€</b></p></div>";
	} else {
		$message .= "<div class='infos'><p><b>À swaper</b></p></div>";
	}
	$message .= "</div></div>";

	return $message;
}

/*
* Generate the html to output in the chat if a user share a swap-place
* Parameters : int $post_id
* Return : string
*/
function swapplaceToChat($post_id) {
	$images = get_field('images', $post_id);

	$message = "<div data-id='". $post_id ."' class='swapplace' onclick='openChatSwapplace(". $post_id .")'><div class='swapplace-carousel'><img src='".$images[0]."'></div><div class='infos-wrapper'><h3 class='h1'>". get_the_title($post_id) ."</h3><p class='address'>".get_field('adresse', $post_id)."</p></div></div>";

	return $message;
}


/*
* Check if the post is liked by the current user
* Parameters : int $post_id
* Return : bool
*/
function isPostLiked($post_id) {
	$user_id = get_current_user_id();
	$dressing_id = get_field('dressing', 'user_'.$user_id);
	$likes = get_field('likes', $post_id);
	if(is_array($likes)) {
		foreach($likes as $like){
			if($like == $dressing_id) {
				return true;
			}
		}
	}
	return false;
}

/*
* Check if the comment is liked by the current user
* Parameters : int $post_id, int comm_id
* Return : bool
*/
function isCommentLiked($post_id, $comm_id) {
	$user_id = get_current_user_id();
	$dressing_id = get_field('dressing', 'user_'.$user_id);
	$likes =  get_field('likes', "comment_".$comm_id);
	foreach($likes as $like){
		if($like == $dressing_id) {
			return true;
		} else {
			return false;
		}
	}
}

/*
* Get the number of notifications of the user
* Parameters : int $user_id
* Return : int
*/
function getNotifNumber($user_id) {
	$notifs = get_field('notifications', 'user_'.$user_id);
	if($notifs) {
		return count($notifs);
	} else {
		return 0;
	}
}

/*
* Check if the swap-place is in the city of the scope
* Parameters : int $post_id, array scope_array
* Return : bool
*/
function isSwapPlaceInCity($post_id, $scope_array){
	preg_match('/[0-9]{5}/', get_field('adresse', $post_id), $matches);
	$post_location = $matches[0];
	if(in_array($post_location, $scope_array['scope'])) {
		return true;
	} else {
		return false;
	}
}

/*
* Displays the post list
* Parameters : array $postlist
* Return : none
*/
function displayPosts($postlist) {
	if($postlist["featured"] && !empty($postlist["featured"]) && $postlist["featured"] != null) {
		foreach($postlist["featured"] as $key => $post) {
			if($key == 'cdc' && $post != null) {
				print '<p class="h1" style="margin-top: 20px">On adore</p>';
				set_query_var( 'post', $post );
				get_template_part( 'partials/content/content', 'produits' );
			} elseif($key == 'popular' && $post != null) {
				print '<p class="h1" style="margin-top: 20px">Get it, le produit le plus liké</p>';
				set_query_var( 'post', $post );
				get_template_part( 'partials/content/content', 'produits' );
				// Since we do not want to show the most popular product again
				$skip = $post;
			} elseif($key == 'vip' && $post != null) {
				set_query_var( 'user', $post );
				get_template_part( 'partials/content/content', 'vip' );
			} elseif($key == 'map' && $post != null) {
				set_query_var( 'swapplaces', $post );
				set_query_var('map_scope', 'scope');
				get_template_part( 'partials/content/content', 'map' );
			}
		}
	}
	if($postlist["scope"] && !empty($postlist["scope"]) && $postlist["scope"] != null){
		foreach($postlist["scope"] as $key => $post) {
			if($post == 'nodata') {
				get_template_part( 'partials/content/content', 'noproducts' );
			} elseif($post[0] == 'comments') {
				set_query_var( 'comm', $post[1] );
				get_template_part( 'partials/content/content', 'comments' );
			} elseif($post[0] == 'produits') {
				// Skip the $skip post
				if(isset($skip)) {
					if( $skip != $post[1]) {
						set_query_var( 'post', $post[1] );
						get_template_part( 'partials/content/content', 'produits' );
					}
				} else {
					set_query_var( 'post', $post[1] );
					get_template_part( 'partials/content/content', 'produits' );
				}
			} elseif($post[0] == 'swapplaces') {
				set_query_var( 'post', $post[1] );
				get_template_part( 'partials/content/content', 'swapplaces' );
			}
		}
	}
	if($postlist["more"] && !empty($postlist["more"]) && $postlist["more"] != null){
		if($postlist["more"][0] != 'nodata') {
			// Get the department name
			if(strpos($_GET['more'], '%2C')) {
				$nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.substr($_GET['more'], 0, 2).'?fields=region'))->region->nom;
			} else {
				$nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.$_GET['more'].'?fields=nom'))->nom;
			}
			print '<p class="h2">Plus d\'actualités de <br><span class="scope-level">'.$nom.'</span></p>';
		}
		foreach($postlist["more"] as $key => $post) {
			if($post[0] == 'comments') {
				set_query_var( 'comm', $post[1] );
				get_template_part( 'partials/content/content', 'comments' );
			} elseif($post[0] == 'produits') {
				set_query_var( 'post', $post[1] );
				get_template_part( 'partials/content/content', 'produits' );
			} elseif($post[0] == 'swapplaces') {
				set_query_var( 'post', $post[1] );
				get_template_part( 'partials/content/content', 'swapplaces' );
			} elseif($post[0] == 'map') {
				set_query_var( 'swapplaces', $post[1] );
				set_query_var('map_scope', 'more');
				get_template_part( 'partials/content/content', 'map' );
			}
		}
	}
	if($postlist["even_more"] && !empty($postlist["even_more"]) && $postlist["even_more"] != null){
		if($postlist["even_more"][0] != 'nodata') {
			// Get the region name
			$nom = json_decode(file_get_contents('https://geo.api.gouv.fr/departements/'.substr($_GET['even_more'], 0, 2).'?fields=region'))->region->nom;
			print '<p class="h2">Plus d\'actualités de <br><span class="scope-level">'.$nom.'</span></p>';
		}
		foreach($postlist["even_more"] as $key => $post) {
			if($post[0] == 'comments') {
				set_query_var( 'comm', $post[1] );
				get_template_part( 'partials/content/content', 'comments' );
			} elseif($post[0] == 'produits') {
				set_query_var( 'post', $post[1] );
				get_template_part( 'partials/content/content', 'produits' );
			} elseif($post[0] == 'swapplaces') {
				set_query_var( 'post', $post[1] );
				get_template_part( 'partials/content/content', 'swapplaces' );
			} elseif($post[0] == 'map') {
				set_query_var( 'swapplaces', $post[1] );
				set_query_var('map_scope', 'even_more');
				get_template_part( 'partials/content/content', 'map' );
			}
		}
	}
}

/* 
* Function for usort, sort posts by distance, see https://www.geodatasource.com/developers/php
* Parameters : array $postlist, array $scope_position
* Return : none
*/
function sortByDistance(&$postlist, $scope_position) {
    usort($postlist, function($a, $b) use ($scope_position) {

		$scope_lat = $scope_position[0];
		$scope_lng = $scope_position[1];

		if($a[0] == 'produits') {
			$owner = get_field('proprietaire', $a[1])['ID'];
			$a_lat = get_field('lat', 'user_'.$owner);
			$a_lng = get_field('lng', 'user_'.$owner);
		} elseif($a[0] == 'swapplaces') {
			$a_lat = get_field('lat', $a[1]);
			$a_lng = get_field('lng', $a[1]);
		} elseif($a[0] == 'comments') {
			$comment_post_id = get_comment($a[1])->comment_post_ID;
			$comment_post_type = get_post_type($comment_post_id);
			if($comment_post_type == 'produits'){
				$owner = get_field('proprietaire', $comment_post_id)['ID'];
				$a_lat = get_field('lat', 'user_'.$owner);
				$a_lng = get_field('lng', 'user_'.$owner);
			} elseif($comment_post_type == 'swapplaces') {
				$a_lat = get_field('lat', $comment_post_id);
				$a_lng = get_field('lng', $comment_post_id);
			} elseif($comment_post_type == 'dressings') {
				$owner = get_field('proprietaire', $comment_post_id)['ID'];
				$a_lat = get_field('lat', 'user_'.$owner);
				$a_lng = get_field('lng', 'user_'.$owner);
			}
		}

		if($b[0] == 'produits') {
			$owner = get_field('proprietaire', $b[1])['ID'];
			$b_lat = get_field('lat', 'user_'.$owner);
			$b_lng = get_field('lng', 'user_'.$owner);
		} elseif($b[0] == 'swapplaces') {
			$b_lat = get_field('lat', $b[1]);
			$b_lng = get_field('lng', $b[1]);
		} elseif($b[0] == 'comments') {
			$comment_post_id = get_comment($b[1])->comment_post_ID;
			$comment_post_type = get_post_type($comment_post_id);
			if($comment_post_type == 'produits'){
				$owner = get_field('proprietaire', $comment_post_id)['ID'];
				$b_lat = get_field('lat', 'user_'.$owner);
				$b_lng = get_field('lng', 'user_'.$owner);
			} elseif($comment_post_type == 'swapplaces') {
				$b_lat = get_field('lat', $comment_post_id);
				$b_lng = get_field('lng', $comment_post_id);
			} elseif($comment_post_type == 'dressings') {
				$owner = get_field('proprietaire', $comment_post_id)['ID'];
				$b_lat = get_field('lat', 'user_'.$owner);
				$b_lng = get_field('lng', 'user_'.$owner);
			}
		}

		if(isset($a_lat) && isset($a_lng) && isset($b_lat) && isset($b_lng)) {
			if (($a_lat == $b_lat) && ($a_lng == $b_lng)) {
				return 0;
			} else {
				if(is_string($scope_lat)) {
					$scope_lat = floatval($scope_lat);
				}
				if(is_string($scope_lng)) {
					$scope_lng = floatval($scope_lng);
				}
				if(is_string($a_lat)) {
					$a_lat = floatval($a_lat);
				}
				if(is_string($a_lng)) {
					$a_lng = floatval($a_lng);
				}
				if(is_string($b_lat)) {
					$b_lat = floatval($b_lat);
				}
				if(is_string($b_lng)) {
					$b_lng = floatval($b_lng);
				}

				$a_theta = $scope_lng - $a_lng;
				$a_dist = sin(deg2rad($scope_lat)) * sin(deg2rad($a_lat)) +  cos(deg2rad($scope_lat)) * cos(deg2rad($a_lat)) * cos(deg2rad($a_theta));
				$a_dist = acos($a_dist);
				$a_dist = rad2deg($a_dist);
				$a_km = $a_dist * 60 * 1.1515 * 1.609344;
	
				$b_theta = $scope_lng - $b_lng;
				$b_dist = sin(deg2rad($scope_lat)) * sin(deg2rad($b_lat)) +  cos(deg2rad($scope_lat)) * cos(deg2rad($b_lat)) * cos(deg2rad($b_theta));
				$b_dist = acos($b_dist);
				$b_dist = rad2deg($b_dist);
				$b_km = $b_dist * 60 * 1.1515 * 1.609344;
	
				return ($a_km < $b_km) ? -1 : 1;
			}
		} else {
			return 0;
		}
    });
}


/* 
* Function for usort, sort posts by distance, see https://www.geodatasource.com/developers/php
* Parameters : array $postlist, array $scope_position
* Return : none
*/
function sortSwapplacesByDistance(&$swapplaces, $scope_position) {
    usort($swapplaces, function($a, $b) use ($scope_position) {

		$scope_lat = $scope_position[0];
		$scope_lng = $scope_position[1];

		$a_lat = get_field('lat', $a);
		$a_lng = get_field('lng', $a);
		$b_lat = get_field('lat', $b);
		$b_lng = get_field('lng', $b);

		if(isset($a_lat) && isset($a_lng) && isset($b_lat) && isset($b_lng)) {
			if (($a_lat == $b_lat) && ($a_lng == $b_lng)) {
				return 0;
			} else {
				if(is_string($scope_lat)) {
					$scope_lat = floatval($scope_lat);
				}
				if(is_string($scope_lng)) {
					$scope_lng = floatval($scope_lng);
				}
				if(is_string($a_lat)) {
					$a_lat = floatval($a_lat);
				}
				if(is_string($a_lng)) {
					$a_lng = floatval($a_lng);
				}
				if(is_string($b_lat)) {
					$b_lat = floatval($b_lat);
				}
				if(is_string($b_lng)) {
					$b_lng = floatval($b_lng);
				}

				$a_theta = $scope_lng - $a_lng;
				$a_dist = sin(deg2rad($scope_lat)) * sin(deg2rad($a_lat)) +  cos(deg2rad($scope_lat)) * cos(deg2rad($a_lat)) * cos(deg2rad($a_theta));
				$a_dist = acos($a_dist);
				$a_dist = rad2deg($a_dist);
				$a_km = $a_dist * 60 * 1.1515 * 1.609344;
	
				$b_theta = $scope_lng - $b_lng;
				$b_dist = sin(deg2rad($scope_lat)) * sin(deg2rad($b_lat)) +  cos(deg2rad($scope_lat)) * cos(deg2rad($b_lat)) * cos(deg2rad($b_theta));
				$b_dist = acos($b_dist);
				$b_dist = rad2deg($b_dist);
				$b_km = $b_dist * 60 * 1.1515 * 1.609344;
	
				return ($a_km < $b_km) ? -1 : 1;
			}
		} else {
			return 0;
		}
    });
}


/* 
* Function for usort, sort posts by likes
* Parameters : array $a, array $b
* Return : none
*/
function sortByPopular($a, $b) {
	if($a[0] == 'comments') {
		$a_likes = getCommentLikesNumber($a[1]);
	} else {
		$a_likes =  getLikesNumber($a[1]);
	}
	if($b[0] == 'comments') {
		$b_likes = getCommentLikesNumber($b[1]);
	} else {
		$b_likes =  getLikesNumber($b[1]);
	}

	if ($a_likes == $b_likes) {
        return 0;
    }
    return ($a_likes > $b_likes) ? -1 : 1;
}

/* 
* Function for usort, sort posts by number of comments
* Parameters : array $a, array $b
* Return : none
*/
function sortByBusiest($a, $b) {
	if($a[0] == 'comments') {
		$a_likes = getCommentChildNumber($a[1]);
	} else {
		$a_likes =  getCommentsNumber($a[1]);
	}
	if($b[0] == 'comments') {
		$b_likes = getCommentChildNumber($b[1]);
	} else {
		$b_likes =  getCommentsNumber($b[1]);
	}

	if ($a_likes == $b_likes) {
        return 0;
    }
    return ($a_likes > $b_likes) ? -1 : 1;
}

/* 
* Function for usort, sort posts by recent
* Parameters : array $a, array $b
* Return : none
*/
function sortByRecent($a, $b) {
	if($a[0] == 'comments') {
		$a_time = get_comment_date('d-m-Y H:i:s', $a[1]);
	} else {
		$a_time = get_the_date('d-m-Y H:i:s', $a[1]);
	}
	if($b[0] == 'comments') {
		$b_time = get_comment_date('d-m-Y H:i:s', $b[1]);
	} else {
		$b_time = get_the_date('d-m-Y H:i:s', $b[1]);
	}
	
	$a_date = date_create($a_time);
	$b_date = date_create($b_time);

	if ($a_time == $b_time) {
        return 0;
	}
	$interval = date_diff($a_date, $b_date)->format('%R');

    return ($interval == '-') ? -1 : 1;
}

/* 
* Sort posts
* Parameters : array $postlist, string order
* Return : array $postlist
*/
function sortPosts($postlist, $order) {
	$nodata = array(
        "scope" => false,
        "more" => false,
        "even_more" => false
    );
	$map = array(
        "scope" => false,
        "more" => false,
        "even_more" => false
    );
	foreach($postlist as $scope => $array) {
		if($array[0] == "nodata") {
			$nodata[$scope] = true;
			unset($postlist[$scope][0]);
		} elseif($array[0][0] == "map") {
			$map[$scope] = $array[0];
			unset($postlist[$scope][0]);
		}
	}
	if($order == 'recent' || $order == 'oldest') {
		usort($postlist['scope'], "sortByRecent");
		usort($postlist['more'], "sortByRecent");
		usort($postlist['even_more'], "sortByRecent");
	} elseif($order == 'distance') {
		$scope_position = getScopeLatLng(getScope($_GET));
		usort($postlist['scope'], "sortByRecent");
		sortByDistance($postlist['more'], $scope_position);
		sortByDistance($postlist['even_more'], $scope_position);
	} elseif($order == 'popular') {
		usort($postlist['scope'], "sortByPopular");
		usort($postlist['more'], "sortByPopular");
		usort($postlist['even_more'], "sortByPopular");
	} elseif($order == 'busiest') {
		usort($postlist['scope'], "sortByBusiest");
		usort($postlist['more'], "sortByBusiest");
		usort($postlist['even_more'], "sortByBusiest");
	}
	if($order == 'oldest') {
		$postlist['scope'] = array_reverse($postlist['scope']);
		$postlist['more'] = array_reverse($postlist['more']);
		$postlist['even_more'] = array_reverse($postlist['even_more']);
	}
	foreach($nodata as $scope => $no_products) {
		if($no_products == true) {
			array_unshift($postlist[$scope], "nodata");
		}
	}
	foreach($map as $scope => $has_map) {
		if($has_map != false) {
			array_unshift($postlist[$scope], $map[$scope]);
		}
	}
	return $postlist;
}

/* 
* Check if a discussion is read
* Parameters : int $partner_id
* Return : bool
*/
function isDiscussionRead($partner_id) {
	$user_id = get_current_user_id();
	$notifs = get_field('notifications', 'user_'.$user_id);
	// We use notifications to determine if a discussion has been read or not
	foreach($notifs as $notif) {
		if($notif[notification] == ucfirst(get_userdata($partner_id)->data->display_name).' t\'a envoyé un message.') {
			return false;
		}
	}
	return true;
}

/* 
* Sends notifications to the user according to the event type
* Parameters : int $reciever_id, string $event, string  $link, int $event_on
* Return : bool
*/
function notify($reciever_id, $event, $link, $event_on){

	$user_id = get_current_user_id();
	// We start building the notification text with the username 
	$text = '<b>'.ucfirst(get_userdata($user_id)->data->user_login).'</b>';

	// Depending on the event type, we write the according text
	if($event == 'like') {
		$text .= ' a aimé ';
	} elseif($event == 'comment') {
		$text .= ' a commenté sur ';
	} elseif($event == 'answer') {
		$text .= ' a répondu à ton commentaire sur ';
	} elseif($event == 'chat') {
		$text .= ' t\'a envoyé un message.';
	} elseif($event == 'sell') {
		$text .= ' te demande de confirmer la vente de ';
	} elseif($event == 'nosell') {
		$text .= ' n\'a pas confirmé la vente de ';
	} elseif($event == 'swap') {
		$text .= ' te demande de confirmer l\'échange de ';
	} elseif($event == 'noswap') {
		$text .= ' n\'a pas confirmé la l\'échange de ';
	}

	// -1 means it's a message notification
	if($event_on != -1) {
		if(get_post_type($event_on) == 'dressings') {
			$text .= 'ton dressing.';
		} elseif($event == 'swap') {
			$event_on_array = explode(',', $event_on);
			$text .= '<b>'.get_the_title($event_on_array[0]).'</b>';
			$text .= ' avec ';
			$text .= '<b>'.get_the_title($event_on_array[1]).'</b>';
		} elseif($event == 'noswap') {
			$event_on_array = explode(',', $event_on);
			$text .= '<b>'.get_the_title($event_on_array[0]).'</b>';
			$text .= ' avec ';
			$text .= '<b>'.get_the_title($event_on_array[1]).'</b>';
		} else {
			$text .= '<b>'.get_the_title($event_on).'</b>';
		}
	}

	// We add the notification to the users notifications
	$notifs = get_field('notifications', 'user_'.$reciever_id);
	$row = array(
		'field_5e010b055c732' => $text,
		'field_5e46c91b81847' => $event,
		'field_5e46cd0c70674' => $user_id,
		'field_5e46cd2270675' => $event_on,
		'field_5e27c9016e023' => $link,
	);

	add_row('field_5e010aea5c731', $row, 'user_'.$reciever_id);
}

/* 
* Ajax function to like a post, see the ajax.swapchic.js file for more infos
* Parameters : string $type, int $post_id, string $post_type
* Return : none
*/
function ajaxLike() {
	$user_id = get_current_user_id();
	$dressing_id = get_field('dressing', 'user_'.$user_id);
	$type = $_POST['type'];
	$post_id = $_POST['post_id'];
	$post_type = $_POST['post_type'];
	$likes = get_field('likes', $post_id);

	
	if($type == 'like') {
		if($post_type == 'produits') {
			// We add the product id to the List of all the user's liked products
			$produits_favoris = get_field('produits', 'user_'.$user_id);
			if(!is_array($produits_favoris)) {
				$produits_favoris = array($post_id);
			} else {
				array_push($produits_favoris, $post_id);
			}
			update_field( 'field_5dea2fa99db69', $produits_favoris, 'user_'.$user_id );

			// We add the user id to the List of all the product's likes
			$produits_likes = get_field('likes', $post_id);
			if(!is_array($produits_likes)) {
				$produits_likes = array($dressing_id);
			} else {
				array_push($produits_likes, $dressing_id);
			}
			update_field( 'field_5e010b45a5f33', $produits_likes, $post_id);

			// We notify the owner of the like
			$reciever_id = get_field('proprietaire', $post_id)['ID'];
			notify($reciever_id, 'like', get_permalink($post_id), $post_id);

		} elseif($post_type == 'dressings') {

			// We add the dressing id to the List of all the user's liked dressings
			$dressings_favoris = get_field('utilisateurs', 'user_'.$user_id);
			if(!is_array($dressings_favoris)) {
				$dressings_favoris = array($post_id);
			} else {
				array_push($dressings_favoris, $post_id);
			}
			update_field( 'field_5dea2fc89db6a', $dressings_favoris, 'user_'.$user_id );

			// We add the user id to the List of all the dressing's likes
			$dressings_likes = get_field('likes', $post_id);
			if(!is_array($dressings_likes)) {
				$dressings_likes = array($dressing_id);
			} else {
				array_push($dressings_likes, $dressing_id);
			}
			update_field( 'field_5e010b54c3f7e', $dressings_likes, $post_id);

			// We notify the owner of the like
			$reciever_id = get_field('proprietaire', $post_id)['ID'];
			notify($reciever_id, 'like', get_permalink($post_id), $post_id);

		} elseif($post_type == 'swapplaces') {

			// We add the swap-place id to the List of all the user's liked swap-places
			$swapplaces_favoris = get_field('swap-places', 'user_'.$user_id);
			if(!is_array($swapplaces_favoris)) {
				$swapplaces_favoris = array($post_id);
			} else {
				array_push($swapplaces_favoris, $post_id);
			}
			update_field( 'field_5dea30099db6c', $swapplaces_favoris, 'user_'.$user_id );

			// We add the user id to the List of all the swap-place's likes
			$swapplaces_likes = get_field('likes', $post_id);
			if(!is_array($swapplaces_likes)) {
				$swapplaces_likes = array($dressing_id);
			} else {
				array_push($swapplaces_likes, $dressing_id);
			}
			update_field( 'field_5e010b33abbea', $swapplaces_likes, $post_id);
		} elseif($post_type == 'comments') {
			
			// We add the comment id to the List of all the user's liked comments
			$comment_id = $_POST['comment_id'];
			$commentaires_aimes = get_field('commentaires_aimes', 'user_'.$user_id);
			if(!is_array($commentaires_aimes)) {
				$commentaires_aimes = array(array('comment' => $comment_id));
			} else {
				array_push($commentaires_aimes, array('comment' => $comment_id));
			}
			update_field( 'field_5e247106758d5', $commentaires_aimes, 'user_'.$user_id );

			// We add the user id to the List of all the comment's likes
			$commentaires_likes = get_field('likes', 'comment_'.$comment_id);
			if(!is_array($commentaires_likes)) {
				$commentaires_likes = array($dressing_id);
			} else {
				array_push($commentaires_likes, $dressing_id);
			}
			update_field( 'field_5e1c2b79e302c', $commentaires_likes, 'comment_'.$comment_id);

		} elseif($post_type == 'posts') {

			// We add the blog post id to the List of all the user's liked blog posts
			$posts_aimés = get_field('articles_aimés', 'user_'.$user_id);
			if(!is_array($posts_aimés)) {
				$posts_aimés = array($post_id);
			} else {
				array_push($posts_aimés, $post_id);
			}
			update_field( 'field_5e81c05a4463e', $posts_aimés, 'user_'.$user_id );

			// We add the user id to the List of all the blog post's likes
			$posts_likes = get_field('likes', $post_id);
			if(!is_array($posts_likes)) {
				$posts_likes = array($dressing_id);
			} else {
				array_push($posts_likes, $dressing_id);
			}
			update_field( 'field_5e81804daa391', $posts_likes, $post_id);
		}
	} else {
		if($post_type == 'produits') {

			// We remove the product id from the List of the user's liked products
			$produits_favoris = get_field('produits', 'user_'.$user_id);
			$i = 0;
			foreach($produits_favoris as $produit_favori) {
				if($produit_favori == $post_id) {
					unset($produits_favoris[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5dea2fa99db69', $produits_favoris, 'user_'.$user_id );
			
			// We remove the user id from the List of the product's likes
			$produits_likes = get_field('likes', $post_id);
			$i = 0;
			foreach($produits_likes as $produit_like) {
				if($produit_like == $dressing_id) {
					unset($produits_likes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e010b45a5f33', $produits_likes, $post_id);

		} elseif($post_type == 'dressings') {

			// We remove the dressing id from the List of the user's liked dressings
			$dressings_favoris = get_field('utilisateurs', 'user_'.$user_id);
			$i = 0;
			foreach($dressings_favoris as $dressing_favori) {
				if($dressing_favori == $post_id) {
					unset($dressings_favoris[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5dea2fc89db6a', $dressings_favoris, 'user_'.$user_id );

			// We remove the user id from the List of the dressing's likes
			$dressings_likes = get_field('likes', $post_id);
			$i = 0;
			foreach($dressings_likes as $dressing_like) {
				if($dressing_like == $dressing_id) {
					unset($dressings_likes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e010b54c3f7e', $dressings_likes, $post_id);

		} elseif($post_type == 'swapplaces') {

			// We remove the swap-place id from the List of  the user's liked swap-places
			$swapplaces_favoris = get_field('swap-places', 'user_'.$user_id);
			$i = 0;
			foreach($swapplaces_favoris as $swapplace_favori) {
				if($swapplace_favori == $post_id) {
					unset($swapplaces_favoris[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5dea30099db6c', $swapplaces_favoris, 'user_'.$user_id );

			// We remove the user id from the List of the swap-place's likes
			$swapplaces_likes = get_field('likes', $post_id);
			$i = 0;
			foreach($swapplaces_likes as $swapplace_like) {
				if($swapplace_like == $dressing_id) {
					unset($swapplaces_likes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e010b33abbea', $swapplaces_likes, $post_id);
		} elseif($post_type == 'comments') {

			// We remove the comment id from the List of all the user's liked comments
			$comment_id = $_POST['comment_id'];
			$commentaires_aimes = get_field('commentaires_aimes', 'user_'.$user_id);
			$i = 0;
			foreach($commentaires_aimes as $commentaire_aime) {
				if(in_array($comment_id, $commentaire_aime)) {
					unset($commentaires_aimes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e247106758d5', $commentaires_aimes, 'user_'.$user_id );
			
			// We remove the user id from the List of all the comment's likes
			$commentaires_likes = get_field('likes', 'comment_'.$comment_id);
			$i = 0;
			foreach($commentaires_likes as $commentaires_like) {
				if($commentaires_like == $dressing_id) {
					unset($commentaires_likes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e1c2b79e302c', $commentaires_likes, 'comment_'.$comment_id);
		} elseif($post_type == 'posts') {

			// We remove the blog post id from the List of the user's liked blog posts
			$posts_aimés = get_field('articles_aimés', 'user_'.$user_id);
			$i = 0;
			foreach($posts_aimés as $posts_aimé) {
				if($posts_aimé == $post_id) {
					unset($posts_aimés[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e81c05a4463e', $posts_aimés, 'user_'.$user_id );

			// We remove the user id from the List of all the blog post's likes
			$posts_likes = get_field('likes', $post_id);
			$i = 0;
			foreach($posts_likes as $posts_like) {
				if($posts_like == $dressing_id) {
					unset($posts_likes[$i]);
					break;
				}
				$i++;
			}
			update_field( 'field_5e81804daa391', $posts_likes, $post_id);
		}
	}
	die();
}
add_action( 'wp_ajax_ajaxLike', 'ajaxLike' );

/* 
* Ajax function to display a post's comment, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id
* Return : none
*/
function ajaxGetComments() {
	$post_id = $_POST['post_id'];
	$comments = get_comments(array('post_id' => $post_id));
	$i = 0;
	foreach($comments as $comment) {
		// We display only comments that are not childs
        if($comment->comment_parent != 0) {
			unset($comments[$i]);
		}
		$i++;
	}
	set_query_var( 'comments_array', $comments);
	echo get_template_part( 'partials/content/content', 'comment' );
	die();
}
add_action( 'wp_ajax_ajaxGetComments', 'ajaxGetComments' );
add_action( 'wp_ajax_nopriv_ajaxGetComments', 'ajaxGetComments' );


/* 
* Ajax function to display a comment's child, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $comment_id
* Return : none
*/
function ajaxGetCommentAnswers() {
	$comment_id = $_POST['comment_id'];
	$post_id = $_POST['post_id'];

	$args = array(
        'post_id'        => $post_id,
        'type'           => 'comment',
		'post_status'    => 'publish',
		'parent'		 => $comment_id,
        'order'          => 'DESC',
        'orderby'        => 'comment_date',
        'nopaging'       => true
    );
    $comments_query = new WP_Comment_Query;
	$comments = $comments_query->query( $args );

	set_query_var( 'comments_array', $comments );
	echo get_template_part( 'partials/content/content', 'comment' );
	die();
}
add_action( 'wp_ajax_ajaxGetCommentAnswers', 'ajaxGetCommentAnswers' );

/* 
* Ajax function to comment a post, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, string $content
* Return : none
*/
function ajaxComment(){
	$post_id = $_POST['post_id'];
	$content = $_POST['content'];
	$user_id = get_current_user_id();

	$commentdata = array(
		'comment_author' => wp_get_current_user()->data->display_name,
		'comment_content' => $content,
		'comment_post_ID' => $post_id,
		'user_id' => $user_id
	);
	$comments_array = array(get_comment(wp_insert_comment($commentdata)));

	if(get_post_type($post_id) == 'dressings' || get_post_type($post_id) == 'produits') {
		$reciever_id = get_field('proprietaire', $post_id)['ID'];
		notify($reciever_id, 'comment', get_permalink($post_id), $post_id);
	}

	set_query_var( 'comments_array', $comments_array);
	echo get_template_part( 'partials/content/content', 'comment' );
	die();
}
add_action( 'wp_ajax_ajaxComment', 'ajaxComment' );


/* 
* Ajax function to comment a comment, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $comment_id, string $content
* Return : none
*/
function ajaxAnswerComment(){
	$post_id = $_POST['post_id'];
	$content = $_POST['content'];
	$comment_id = $_POST['comment_id'];
	$user_id = get_current_user_id();

	$commentdata = array(
		'comment_author' => wp_get_current_user()->data->display_name,
		'comment_content' => $content,
		'comment_post_ID' => $post_id,
		'comment_parent' => $comment_id,
		'user_id' => $user_id
	);
	$comments_array = array(get_comment(wp_insert_comment($commentdata)));

	$reciever_id = get_comment($comment_id)->user_id;
	if($reciever_id != $user_id) {
		notify($reciever_id, 'answer', get_permalink($post_id), $post_id);
	}
	set_query_var( 'comments_array', $comments_array);
	echo get_template_part( 'partials/content/content', 'comment' );
	die();
}
add_action( 'wp_ajax_ajaxAnswerComment', 'ajaxAnswerComment' );


/* 
* Ajax function to save a custom alert, see the ajax.swapchic.js file for more infos
* Parameters : array $params, string $name
* Return : none
*/
function ajaxSaveAlert(){
	$params = $_POST['params'];
	$name = $_POST['name'];
	$user_id = get_current_user_id();
	$param_string = '?';
	foreach($params as $index => $param) {
		if($index != 0) {
			$param_string .= '&';
		}
		$param_string .= $param[0].'='.$param[1];
	}

	$alertes = get_field('alertes', 'user_'.$user_id);
	foreach($alertes as $alerte) {
		if($name == $alerte['alerte_nom']) {
			echo 'erreur_nom';
			die();
		}
	}

	$row = array(
		'field_5e244720af7ad' => $name,
		'field_5dea305c9db6e' => $param_string
	);

	if(add_row('field_5dea30469db6d', $row, 'user_'.$user_id)) {
	 	echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxSaveAlert', 'ajaxSaveAlert' );

/* 
* Ajax function to delete a custom alert, see the ajax.swapchic.js file for more infos
* Parameters : array $params, string $name
* Return : none TODO
*/
function ajaxDeleteFilterSet(){
	$user_id = get_current_user_id();
	$alerte_index = $_POST['index'];

	$alertes = get_field('alertes', 'user_'.$user_id);
	if(delete_row('field_5dea30469db6d', $alerte_index + 1, 'user_'.$user_id)) {
		echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxDeleteFilterSet', 'ajaxDeleteFilterSet' );

/* 
* Ajax function to notify a user, see notify(), see also the ajax.swapchic.js file for more infos
* Parameters : int $reciever_id, string $event, string  $link, int $event_on
* Return : none
*/
function ajaxNotify(){
	$reciever_id = $_POST['reciever_id'];
	$event = $_POST['event'];
	$link = $_POST['link'];
	$event_on = $_POST['event_on'];
	$user_id = get_current_user_id();

	$text = ucfirst(get_userdata($user_id)->data->user_login);

	if($event == 'like') {
		$text .= ' a aimé ';
	} elseif($event == 'comment') {
		$text .= ' a commenté sur ';
	} elseif($event == 'answer') {
		$text .= ' a répondu à ton commentaire sur ';
	} elseif($event == 'chat') {
		$text .= ' t\'a envoyé un message.';
	} elseif($event == 'sell') {
		$text .= ' te demande de confirmer la vente de ';
	} elseif($event == 'swap') {
		$text .= ' te demande de confirmer l\'échange de ';
	}

	if($event_on != -1) {
		if(get_post_type($event_on) == 'dressings') {
			$text .= 'votre dressing.';
		}
		$text .= get_the_title($event_on);
	}

	$notifs = get_field('notifications', 'user_'.$reciever_id);

	foreach($notifs as $notif) {
		if($notif['notification'] == $text) {
			echo 'already notified';
			die();
		}
	}
	
	// Send mail if it s a new message
	if( $event == 'chat' ) {

		$post = get_page_by_path( substr($link, 34, strlen($link) - 35), OBJECT, 'discussions' );
	
		$discussion_text =  explode("\n", get_field('discussion', $post->ID));
		$last_msg = $discussion_text[count($discussion_text) - 2];
		preg_match('/<p>.*<\/p>/', $last_msg, $matches );
		$message = substr($matches[0], 3, strlen($matches[0]) - 7 );
			
		if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
			$to = get_userdata($reciever_id)->data->user_email;
			$subject = ucfirst(get_userdata($user_id)->data->user_login)." t'a envoyé un nouveau message sur Swap-Chic";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['name'] = get_userdata($user_id)->data->user_login;
			$variables['link'] = $link;
			$variables['message'] = $message;
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/Message.html" );
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			// Only send it if the notified user is offline
			if(get_user_meta($reciever_id, 'asdb-loggedin')[0] != 1) {
				echo 'not connected';
				mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
			}
		}
	}

	$row = array(
		'field_5e010b055c732' => $text,
		'field_5e46c91b81847' => $event,
		'field_5e46cd0c70674' => $user_id,
		'field_5e27c9016e023' => $link,
	);

	if(add_row('field_5e010aea5c731', $row, 'user_'.$reciever_id)) {
		echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxNotify', 'ajaxNotify' );

/* 
* Ajax function to validate a prodct, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id
* Return : none
*/
function ajaxValidate(){
	$post_id = $_POST['post_id'];
	$user_id = get_field('proprietaire', $post_id)['ID'];
	$dressing_id = get_field('dressing', 'user_'.$user_id);

	$removebg_response = setProductThumbnail($post_id);
	echo $removebg_response;
	die();
	$image = get_field('images', $post_id)[0];

	$status_code = $removebg_response->getStatusCode();

	if($status_code == 200) {
		// If the background removal went correctly, we register the new image and set it as thumbnail of the product 
		$upload_dir  = wp_upload_dir();
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . 'no-bg' . DIRECTORY_SEPARATOR;

		$file = substr($image, strrpos($image, '/') + 1, strlen($image));
		$file_name = substr($file_name, 0, strlen($file_name) - 4)."-no-bg.png";
		$hashed_file_name = md5( $file_name . microtime() ) . '_' . $file_name;

		$fp = fopen($upload_path.$hashed_file_name, "wb");
		fwrite($fp, $res->getBody());
		fclose($fp);

		$attachment = array(
			'post_mime_type' => 'image/png',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($hashed_file_name) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'guid'           => $upload_dir['baseurl'] . '/' . 'no-bg' . '/' . basename( $hashed_file_name )
		);

		$attach_id = wp_insert_attachment( $attachment, $upload_dir['basedir'] . '/' . 'no-bg' . '/' . $hashed_file_name );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_path . $hashed_file_name );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail($post_id, $attach_id);

		wp_update_post(
				array(
				'ID'    =>  $post_id,
				'post_status'   =>  'publish'
			)
		);
		
		$product = get_the_title($post_id);

		// Send mail to user 
		if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
			$to = get_userdata($user_id)->data->user_email;
			$subject = "Bravo, ton article ".$product." figure maintenant dans notre catalogue !";
			$from = "noreply@swap-chic.com";
			$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			$variables = array();
			$variables['name'] = ucfirst(get_userdata($user_id)->data->user_login);
			$variables['product'] = $product;
			$variables['link'] = 'https://'.$_SERVER['HTTP_HOST'].'/?post_type=produits&p='.$post_id;
			$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/accept.html");
			foreach($variables as $key => $value) {
				$template = str_replace('{{ '.$key.' }}', $value, $template);
			}
			mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
		}
		echo $status_code;
		die();
	} else {
		echo $status_code;
		die();
	}
}
add_action( 'wp_ajax_ajaxValidate', 'ajaxValidate' );


/* 
* Ajax function to deny a prodct, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id
* Return : none
*/
function ajaxUnvalidate(){
	$post_id = $_POST['post_id'];
	$user_id = get_field('proprietaire', $post_id)['ID'];
	$dressing_id = get_field('dressing', 'user_'.$user_id);

	$date = date('d/m/Y H:i:s');

	// Register the date and time of the denial
	update_field('field_5e3aebf852e92', $date, $post_id);

	$product = get_the_title($post_id);

	// Send mail to user
	if(substr($_SERVER['SERVER_NAME'], 0, 3) != 'dev'){
		$to = get_userdata($user_id)->data->user_email;
		$subject = "Désolée, ton article ".$product." n'a pas été approuvé, essaye à nouveau !";
		$from = "noreply@swap-chic.com";
		$headers = "Reply-To: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "Return-Path: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "From: Swap-Chic <noreply@swap-chic.com>\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		$variables = array();
		$variables['product'] = $product;
		$variables['link'] = get_the_permalink($dressing_id);
		$template = file_get_contents( ABSPATH . "wp-content/themes/Swap-Chic/assets/mails/deny.html");
		foreach($variables as $key => $value) {
			$template = str_replace('{{ '.$key.' }}', $value, $template);
		}
		mail($to, '=?utf-8?B?'.base64_encode($subject).'?=', $template, $headers);
	}

	die();
}
add_action( 'wp_ajax_ajaxUnvalidate', 'ajaxUnvalidate' );


/* 
* Ajax function to delete a notification, see the ajax.swapchic.js file for more infos
* Parameters : int $notif_index
* Return : none
*/
function ajaxDeleteNotify(){
	$user_id = get_current_user_id();
	$notif_index = $_POST['id'];

	$notifs = get_field('notifications', 'user_'.$user_id);
	if(delete_row('field_5e010aea5c731', $notif_index + 1, 'user_'.$user_id)) {
		echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxDeleteNotify', 'ajaxDeleteNotify' );

/* 
* Ajax function to set the featured product, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $old_post_id,
* Return : none
*/
function ajaxSetCoupDeCoeur(){
	$post_id = $_POST['post_id'];
	$old_post_id = $_POST['old_post_id'];

	update_field('field_5dea2ad32cf88', 1, $post_id);
	update_field('field_5dea2ad32cf88', 0, $old_post_id);

	echo 'ok';

	die();
}
add_action( 'wp_ajax_ajaxSetCoupDeCoeur', 'ajaxSetCoupDeCoeur' );


/* 
* Ajax function to delete a product, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $old_post_id,
* Return : none
*/
function ajaxDeleteProduct(){
	$post_id = $_POST['post_id'];
	if(get_field('proprietaire', $post_id)['ID'] == get_current_user_id()) {
		if(get_field('is_coup_de_coeur', $post_id) == 1) {
			// Delete a featured product after 24h
			wp_schedule_single_event( time() + 86400, 'delete_cdc_event', array($post_id) );
			die();
		} else {
			wp_delete_post($post_id);
			echo 'ok';
		}
	}
}
add_action( 'wp_ajax_ajaxDeleteProduct', 'ajaxDeleteProduct' );

/* 
* Ajax function to send a sale confiramtion, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id,
* Return : none
*/
function ajaxSendSellConfirmation(){
	$post_id = $_POST['post_id'];
	$partner_id = $_POST['partner_id'];
	if(get_field('proprietaire', $post_id)['ID'] == get_current_user_id()) {
		update_field('field_5e463f175c853', 1, $post_id);
	 	notify($partner_id, 'sell', get_the_permalink($post_id), $post_id);
		echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxSendSellConfirmation', 'ajaxSendSellConfirmation' );

/* 
* Ajax function to confirme a sale, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id,
* Return : none
*/
function ajaxConfirmSell(){
	$post_id = $_POST['post_id'];
	$partner_id = $_POST['partner_id'];
	$user_id = get_current_user_id();
	$notifs = get_field('notifications', 'user_'.$user_id);
	if(get_field('proprietaire', $post_id)['ID'] == $partner_id) {
		$notif_index = 0;
		foreach($notifs as $notif) {
			if($notif[provenance] == $partner_id && $notif[sujet] == $post_id && $notif[event] == 'sell') {
				// Delete sale confiramtion notification
				if(delete_row('field_5e010aea5c731', $notif_index + 1, 'user_'.$user_id)) {
					// Increase the total number of sale of the partner
					if(get_field('field_5e462755c9952', 'user_'.$partner_id) == null || empty(get_field('field_5e462755c9952', 'user_'.$partner_id))) {
						$partner_total_sell = 1;
					} else {
						$partner_total_sell = get_field('field_5e462755c9952', 'user_'.$partner_id) + 1;
					}
					update_field('field_5e462755c9952', $partner_total_sell,  'user_'.$partner_id);
					wp_delete_post($post_id);
					echo 'ok';
					echo 'DELETE';
					die();
				}
			}
			$notif_index ++;
		}
	}
	die();
}
add_action( 'wp_ajax_ajaxConfirmSell', 'ajaxConfirmSell' );

/* 
* Ajax function to deny a sale, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id,
* Return : none
*/
function ajaxDenySell(){
	$post_id = $_POST['post_id'];
	$partner_id = $_POST['partner_id'];
	$user_id = get_current_user_id();
	$notifs = get_field('notifications', 'user_'.$user_id);
	if(get_field('proprietaire', $post_id)['ID'] == $partner_id) {
		$notif_index = 0;
		foreach($notifs as $notif) {
			if($notif[provenance] == $partner_id && $notif[sujet] == $post_id && $notif[event] == 'sell') {
				if(delete_row('field_5e010aea5c731', $notif_index + 1, 'user_'.$user_id)) {
					update_field('field_5e463f175c853', 0, $post_id);
					notify($partner_id, 'nosell', get_the_permalink($post_id), $post_id);
					echo 'ok';
					echo 'DELETE';
					die();
				}
			}
			$notif_index ++;
		}
	}
	die();
}
add_action( 'wp_ajax_ajaxDenySell', 'ajaxDenySell' );

/* 
* Ajax function to send a swap confiramtion, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id, int $partner_post_id,
* Return : none
*/
function ajaxSendSwapConfirmation(){
	$post_id = $_POST['post_id'];
	$partner_id = $_POST['partner_id'];
	$partner_post_id = $_POST['partner_post_id'];
	if(get_field('proprietaire', $post_id)['ID'] == get_current_user_id()) {
		update_field('field_5e463f175c853', 1, $post_id);
		notify($partner_id, 'swap', get_the_permalink($post_id), $post_id.",".$partner_post_id);
		echo 'ok';
	}
	die();
}
add_action( 'wp_ajax_ajaxSendSwapConfirmation', 'ajaxSendSwapConfirmation' );


/* 
* Ajax function to confirme a swap, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id, int $partner_post_id,d,
* Return : none
*/
function ajaxConfirmSwap(){
	$post_id = $_POST['post_id'];
	$partner_post_id = $_POST['partner_post_id'];
	$partner_id = $_POST['partner_id'];
	$user_id = get_current_user_id();
	$notifs = get_field('notifications', 'user_'.$user_id);
	if(get_field('proprietaire', $partner_post_id)['ID'] == $partner_id) {
		$notif_index = 0;
		foreach($notifs as $notif) {
			if($notif[provenance] == $partner_id && $notif[sujet] == $partner_post_id.','.$post_id && $notif[event] == 'swap') {
				// Delete swap confiramtion notification
				if(delete_row('field_5e010aea5c731', $notif_index + 1, 'user_'.$user_id)) {
					if(wp_delete_post($post_id) && wp_delete_post($partner_post_id)){
						// Increase the total number of swap of the partner
						if(get_field('field_5e462755c9952', 'user_'.$partner_id) == null || empty(get_field('field_5e462755c9952', 'user_'.$partner_id))) {
							$partner_total_swap = 1;
						} else {
							$partner_total_swap = get_field('field_5e462755c9952', 'user_'.$partner_id) + 1;
						}
						// Increase the total number of swap of the user
						if(get_field('field_5e462755c9952', 'user_'.$user_id) == null || empty(get_field('field_5e462755c9952', 'user_'.$user_id))) {
							$user_total_swap = 1;
						} else {
							$user_total_swap = get_field('field_5e462755c9952', 'user_'.$user_id) + 1;
						}
						update_field('field_5e462755c9952', $partner_total_swap,  'user_'.$partner_id);
						update_field('field_5e462755c9952', $user_total_swap,  'user_'.$user_id);
						echo 'ok';
						echo 'DELETE';
						die();
					}
				}
			}
			$notif_index ++;
		}
	}
	die();
}
add_action( 'wp_ajax_ajaxConfirmSwap', 'ajaxConfirmSwap' );

/* 
* Ajax function to deny a swap, see the ajax.swapchic.js file for more infos
* Parameters : int $post_id, int $partner_id, int $partner_post_id,d,
* Return : none
*/
function ajaxDenySwap(){
	$post_id = $_POST['post_id'];
	$partner_post_id = $_POST['partner_post_id'];
	$partner_id = $_POST['partner_id'];
	$user_id = get_current_user_id();
	$notifs = get_field('notifications', 'user_'.$user_id);
	if(get_field('proprietaire', $partner_post_id)['ID'] == $partner_id) {
		$notif_index = 0;
		foreach($notifs as $notif) {
			if($notif[provenance] == $partner_id && $notif[sujet] == $partner_post_id.','.$post_id && $notif[event] == 'swap') {
				if(delete_row('field_5e010aea5c731', $notif_index + 1, 'user_'.$user_id)) {
					update_field('field_5e463f175c853', 0, $partner_post_id);
					notify($partner_id, 'noswap', get_the_permalink($partner_post_id), $post_id.','.$partner_post_id);
					echo 'ok';
					die();
				}
			}
			$notif_index ++;
		}
	}
	die();
}
add_action( 'wp_ajax_ajaxDenySwap', 'ajaxDenySwap' );


/* 
* Ajax function to set a user status as active, see the ajax.swapchic.js file for more infos
* Parameters : none
* Return : none
*/
function ajaxSetActiveUser(){
	$user = wp_get_current_user();
	update_user_meta($user->ID, 'asdb-loggedin', true);
	die();
}
add_action( 'wp_ajax_ajaxSetActiveUser', 'ajaxSetActiveUser' );

/* 
* Ajax function to set a user status as inactive, see the ajax.swapchic.js file for more infos
* Parameters : none
* Return : none
*/
function ajaxSetInactiveUser() {
	$user = wp_get_current_user();
	update_user_meta($user->ID, 'asdb-loggedin', false);
	die();
}
add_action( 'wp_ajax_ajaxSetInactiveUser', 'ajaxSetInactiveUser' );

/* 
* Sets a user status as inactive on logout, see the ajax.swapchic.js file for more infos
* Parameters : none
* Return : none
*/
function delete_user_logged_meta() {
	$user = wp_get_current_user();
	update_user_meta($user->ID, 'asdb-loggedin', false);
}
add_action('wp_logout', 'delete_user_logged_meta');


/* 
* Ajax function to get the state of the chat, see the chat.swapchic.js file for more infos
* Parameters : array $ids, int $post_id
* Return : string $log
*/
function chatGetState() {
	$current_user_id = $_POST['ids'][0];
	$partner_id = $_POST['ids'][1];
	$post_id = $_POST['post_id'];
	$log['post_id'] = $post_id;
	$lines = explode("\n", get_field('discussion', $post_id));
	$log['discussion'] = $lines; 
	$log['state'] = count($lines); 
	foreach ($lines as $line_num => $line) {
		if ($line_num >= $state){
			$text[] =  $line = str_replace("\n", "", $line);
		}
	}
	$log['text'] = $text; 
    echo json_encode($log);
	die();
}
add_action( 'wp_ajax_chatGetState', 'chatGetState' );

/* 
* Ajax function to update the chat, see the chat.swapchic.js file for more infos
* Parameters : int $state, array $ids, int $post_id
* Return : string $log
*/
function chatUpdate() {
	$state = $_POST['state'];
	$current_user_id = $_POST['ids'][0];
	$partner_id = $_POST['ids'][1];
	$post_id = $_POST['post_id'];
	$lines = explode("\n", get_field('discussion', $post_id));
	$count =  count($lines);
	if ($state == $count){
		$log['state'] = $state;
		$log['text'] = false;
	} else {
		$text = array();
		$log['state'] = $state + count($lines) - $state;
		foreach ($lines as $line_num => $line) {
			// Since lines always ends with a \n, we comapre the line number to the state less one
			if ($line_num >= $state - 1){
				$text[] = $line = str_replace("\n", "", $line);
			}
		}
		$log['text'] = $text; 
	}
    echo json_encode($log);
	die();
}
add_action( 'wp_ajax_chatUpdate', 'chatUpdate' );

/* 
* Ajax function to send a message to the chat, see the chat.swapchic.js file for more infos
* Parameters : array $ids, string $message, int $post_id
* Return : string $log
*/
function chatSend() {
	$current_user_id = $_POST['ids'][0];
	$partner_id = $_POST['ids'][1];
	$message = $_POST['message'];
	$post_id = $_POST['post_id'];

	// We get the actual discussion log and we search for the last message date
	$chatlog = get_field('discussion', $post_id);
	$last_msg_date = substr($chatlog, strrpos($chatlog, "<span class='day'>") + 18, 5);

	setlocale(LC_TIME, 'fr_FR.utf8','fra'); 
	
	// If the actual date is not the same day as the last message's date, we add to the message a new day message
	if(date('d/m') > $last_msg_date) {
		$chatlog .= "<div class='new-day'>" . strftime('%A %d %B') . "<br>" . date('H:i') . "</div>" . "\n";
	} else {
		// If the actual date is the same day as the last message's date
		$last_msg_time = substr($chatlog, strrpos($chatlog, "<span class='hour'>") + 19, 5);
		$minute_gap = sqrt(pow(substr($last_msg_time, 3) - date('i'), 2));
		$hour_gap = sqrt(pow(substr($last_msg_time, 0, 2) - date('H'), 2));
		if($minute_gap >= 30) {
			// If the actual time is 30 min later than the last message time
			$chatlog .= "<div class='new-day'>" . date('H:i') . "</div>" . "\n";
		} elseif($hour_gap >= 1) {
			// If the actual time is at least an hour later than the last message time
			$chatlog .= "<div class='new-day'>" . date('H:i') . "</div>" . "\n";
		}
	}

	if(substr($message, 0, strpos($message, ' ')) == 'POST_PRODUCT') {
		// If the message is a product, we remove the 'POST_PRODUCT' msg
		$message = substr($message, 13, strlen($message));
		$chatlog .= "<div class='msg-product-wrapper' data-from='" . $current_user_id . "'>". $message . "<div class='msg-time'><span class='day'>" . date('d/m') ." </span><span class='hour'>" . date('H:i') . "</span></div></div>" . "\n";
	} elseif(substr($message, 0, strpos($message, ' ')) == 'POST_SWAPPLACE') {
		// If the message is a swap-place, we remove the 'POST_SWAPPLACE' msg
		$message = substr($message, 15, strlen($message));
		$chatlog .= "<div class='msg-swapplace-wrapper' data-from='" . $current_user_id . "'>". $message . "<div class='msg-time'><span class='day'>" . date('d/m') ." </span><span class='hour'>" . date('H:i') . "</span></div></div>" . "\n";
	} elseif (($message) != "\n") {
		// If the message is a string
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		$message = htmlentities(strip_tags($_POST['message']));
		if (preg_match($reg_exUrl, $message, $url)) {
			$message = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a>', $message);
		} 
		$chatlog .= "<div class='msg-wrapper' data-from='" . $current_user_id . "'><p>" . str_replace("\n", "</p><p>", $message) . "</p><div class='msg-time'><span class='day'>" . date('d/m') ." </span><span class='hour'>" . date('H:i') . "</span></div></div>" . "\n";
	}
	
	// We update the discussion field with the new chatlog
	update_field('discussion', $chatlog, $post_id);

    echo json_encode($log);
	die();
}
add_action( 'wp_ajax_chatSend', 'chatSend' );

/* 
* Ajax function to open the chat with another user, see the chat.swapchic.js file for more infos
* Parameters : int $user_id, int $partner_id
* Return : string $post_url
*/
function chatOpen() {
	$current_user_id = $_POST['user_id'];
	$partner_id = $_POST['partner_id'];

	// We search for a discussion post with both users id as 'utilisateur_1' and 'utilisateur_2'
	$args = array(
        'post_type' => 'discussions',
        'orderby' => 'modified',
        'order' => 'DESC',
        'nopaging' => true,
        'meta_query' => array(
            'relation' => 'OR',
            array(
				'relation' => 'AND',
				array(
					'key'     => 'utilisateur_1',
					'value'   => $current_user_id,
					'compare' => '='
				),
				array(
					'key'     => 'utilisateur_2',
					'value'   => $partner_id,
					'compare' => '='
				)
			),
			array(
				'relation' => 'AND',
				array(
					'key'     => 'utilisateur_1',
					'value'   => $partner_id,
					'compare' => '='
				),
				array(
					'key'     => 'utilisateur_2',
					'value'   => $current_user_id,
					'compare' => '='
				)
            ),
        ),
    );
    
    $the_query = new WP_Query( $args );

    if ( $the_query->have_posts() ) {
		// If the post exists, we get its permalink
        while ( $the_query->have_posts() ) { 
            $the_query->the_post();
			$post_id = get_the_id();
			$post_url = get_the_permalink($post_id);
        }
	} else {

		// If the post does not exist, we create it then we get its permalink
		$discussion = array(
			'utilisateur_1' => $current_user_id,
			'utilisateur_2' => $partner_id
		);

		$post_data = array(
			'post_type' => 'discussions',
			'post_title' => 'Discussion entre '.ucfirst(get_userdata($discussion['utilisateur_1'])->data->user_login).' et '.ucfirst(get_userdata($discussion['utilisateur_2'])->data->user_login),
			'post_status' => 'publish'
		);

		$post_id = wp_insert_post( $post_data );
		
		update_field('utilisateur_1', $discussion['utilisateur_1'], $post_id);
		update_field('utilisateur_2', $discussion['utilisateur_2'], $post_id);
		update_field('discussion', "", $post_id);

		$post_url = get_the_permalink($post_id);
	}
	
	echo $post_url;
	die();
}
add_action( 'wp_ajax_chatOpen', 'chatOpen' );


/* 
* Adds a new image size
*/
add_image_size( 'share-thumbnail', 600, 600 );

// Register the  new image size for use in Add Media modal
add_filter( 'image_size_names_choose', 'swapchic_custom_sizes' );
function swapchic_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'share-thumbnail' => __( 'Share Thumbnail' )
    ) );
}

/* 
* Setup theme
* Parameters : none
* Return : none
*/
function swapchic_setup() {

	//Register theme for translation
	load_theme_textdomain( 'swapchic' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	//Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	//Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus(
		array(
			'story'    => __( 'Notre histoire', 'swapchic' ),
			'about'    => __( 'À propos', 'swapchic' ),
			'news'    => __( 'News', 'swapchic' ),
			'sitemap'    => __( 'Plan du site', 'swapchic' ),
			'contact' => __( 'Contact', 'swapchic' ),
		)
	);
}
add_action( 'after_setup_theme', 'swapchic_setup' );

/* 
* Adds the product custom post type
* Parameters : none
* Return : none
*/
function swapchic_product_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Produits', 'Post Type General Name'),
		'singular_name'       => _x( 'Produit', 'Post Type Singular Name'),
		'menu_name'           => __( 'Produits'),
		'all_items'           => __( 'Tous les produits'),
		'view_item'           => __( 'Voir les produits'),
		'add_new_item'        => __( 'Ajouter un nouveau produit'),
		'add_new'             => __( 'Ajouter'),
		'edit_item'           => __( 'Editer le produit'),
		'update_item'         => __( 'Modifier le produit'),
		'search_items'        => __( 'Rechercher un produit'),
		'not_found'           => __( 'Non trouvé'),
		'not_found_in_trash'  => __( 'Non trouvé dans la corbeille'),
	);

	$args = array(
		'label'               => __( 'Produits'),
		'description'         => __( 'Tous sur produits'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		'show_in_rest'       => true,
		'hierarchical'        => false,
		'public'              => true,
		'has_archive'         => true,
		'menu_icon' 		  => 'dashicons-tag',
		'rewrite'			  => array( 'slug' => 'produits'),

	);

	register_post_type( 'produits', $args );

}
add_action( 'init', 'swapchic_product_custom_post_type', 0 );

/* 
* Adds the dressing custom post type
* Parameters : none
* Return : none
*/
function swapchic_dressing_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Dressings', 'Post Type General Name'),
		'singular_name'       => _x( 'Dressing', 'Post Type Singular Name'),
		'menu_name'           => __( 'Dressings'),
		'all_items'           => __( 'Tous les dressings'),
		'view_item'           => __( 'Voir les dressings'),
		'add_new_item'        => __( 'Ajouter un nouveau dressing'),
		'add_new'             => __( 'Ajouter'),
		'edit_item'           => __( 'Editer le dressing'),
		'update_item'         => __( 'Modifier le dressing'),
		'search_items'        => __( 'Rechercher un dressing'),
		'not_found'           => __( 'Non trouvé'),
		'not_found_in_trash'  => __( 'Non trouvé dans la corbeille'),
	);

	$args = array(
		'label'               => __( 'Dressings'),
		'description'         => __( 'Tous sur dressings'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		'show_in_rest'       => true,
		'hierarchical'        => false,
		'public'              => true,
		'has_archive'         => true,
		'menu_icon' 		  => 'dashicons-category',
		'rewrite'			  => array( 'slug' => 'dressings'),

	);

	register_post_type( 'dressings', $args );
}
add_action( 'init', 'swapchic_dressing_custom_post_type', 0 );

/* 
* Adds the swap-place custom post type
* Parameters : none
* Return : none
*/
function swapchic_swapplace_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Swap-places', 'Post Type General Name'),
		'singular_name'       => _x( 'Swap-place', 'Post Type Singular Name'),
		'menu_name'           => __( 'Swap-places'),
		'all_items'           => __( 'Toutes les swap-places'),
		'view_item'           => __( 'Voir les swap-places'),
		'add_new_item'        => __( 'Ajouter une nouvelle swap-place'),
		'add_new'             => __( 'Ajouter'),
		'edit_item'           => __( 'Editer la swap-place'),
		'update_item'         => __( 'Modifier la swap-place'),
		'search_items'        => __( 'Rechercher une swap-place'),
		'not_found'           => __( 'Non trouvée'),
		'not_found_in_trash'  => __( 'Non trouvée dans la corbeille'),
	);

	$args = array(
		'label'               => __( 'Swap-places'),
		'description'         => __( 'Tous sur swap-places'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		'show_in_rest'        => true,
		'hierarchical'        => false,
		'public'              => true,
		'has_archive'         => true,
		'menu_icon' 		  => 'dashicons-location',
		'rewrite'			  => array( 'slug' => 'swap-places'),

	);

	register_post_type( 'swapplaces', $args );

}
add_action( 'init', 'swapchic_swapplace_custom_post_type', 0 );

function swapchic_discussion_custom_post_type() {

	$labels = array(
		'name'                => _x( 'Discussions', 'Post Type General Name'),
		'singular_name'       => _x( 'Discussion', 'Post Type Singular Name'),
		'menu_name'           => __( 'Discussions'),
		'all_items'           => __( 'Toutes les discussions'),
		'view_item'           => __( 'Voir les discussions'),
		'add_new_item'        => __( 'Ajouter une nouvelle discussion'),
		'add_new'             => __( 'Ajouter'),
		'edit_item'           => __( 'Editer la discussion'),
		'update_item'         => __( 'Modifier la discussion'),
		'search_items'        => __( 'Rechercher une discussion'),
		'not_found'           => __( 'Non trouvée'),
		'not_found_in_trash'  => __( 'Non trouvée dans la corbeille'),
	);

	$args = array(
		'label'               => __( 'Discussions'),
		'description'         => __( 'Tous sur discussions'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		'show_in_rest'        => true,
		'hierarchical'        => false,
		'public'              => true,
		'has_archive'         => true,
		'menu_icon' 		  => 'dashicons-format-chat',
		'rewrite'			  => array( 'slug' => 'discussions'),

	);

	register_post_type( 'discussions', $args );

}
add_action( 'init', 'swapchic_discussion_custom_post_type', 0 );


/* 
* Sets the swap-place latitude and longitude
* Parameters : int $post_id, string $adresse
* Return : none
*/
function swapplaceGet($post_id, $adresse) {
	$url = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$adresse.'&key=AIzaSyCTCLEBH0SHbmMFfYNSDAsDyoGmq4oLFDw'));
	if($url->status == 'OK') {
		update_field('lat', $url->results[0]->geometry->location->lat, $post_id);
		update_field('lng', $url->results[0]->geometry->location->lng, $post_id);
	} elseif($url->status == 'OVER_QUERY_LIMIT') {
		sleep(1);
		swapplaceGet($post_id, $adresse);
	} else {
		echo $post_id." FAIL TO HAVE THIS SWAPPLACE LOCATION: ".get_the_title($post_id)."\n";
	}
}

/* 
* TODO
* Parameters : int $post_id, string $adresse
* Return : none
*/
function checkProductTiltleTmp($title) {
	$args = array (
        'post_title' => $title,
        'post_type' => 'produits'
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
		return false;
    } else {
		return true;
	}
}

/* 
* Register and enqueue the styles and scripts
* Parameters : none
* Return : none
*/
function swapchic_enqueue_styles() {

	wp_enqueue_style( 'swapchic-config-style', get_template_directory_uri() . '/style.css', array(), null );
	wp_enqueue_style( 'slick-style', get_template_directory_uri() . '/assets/css/slick.css', array(), null );
	wp_enqueue_style( 'slick-theme-style', get_template_directory_uri() . '/assets/css/slick-theme.css', array(), null );
	wp_enqueue_style( 'select2-style', get_template_directory_uri() . '/assets/css/select2.min.css', array(), null );
	wp_enqueue_style( 'cropper-style', get_template_directory_uri() . '/assets/css/cropper.min.css', array(), null );
	wp_enqueue_style( 'swapchic-style', get_template_directory_uri() . '/assets/css/swapchic.css', array(), false );
	wp_enqueue_script( 'wp-api' );
	wp_enqueue_script( 'jquery', get_template_directory_uri() .'/assets/js/jquery.min.js');
	wp_enqueue_script( 'jquery-ui', get_template_directory_uri() .'/assets/js/jquery-ui.min.js', array('jquery'));
	wp_enqueue_script( 'google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCTCLEBH0SHbmMFfYNSDAsDyoGmq4oLFDw', array(), '3', true );
	wp_enqueue_script( 'slick-script', get_template_directory_uri() .'/assets/js/slick.min.js', array('jquery'));
	wp_enqueue_script( 'select2-script', get_template_directory_uri() .'/assets/js/select2.min.js', array('jquery'));
	wp_enqueue_script( 'cropper-script', get_template_directory_uri() .'/assets/js/cropper.min.js', array('jquery'));
	wp_enqueue_script( 'jquery-cropper-script', get_template_directory_uri() .'/assets/js/jquery-cropper.min.js', array('jquery', 'cropper-script'));
	wp_enqueue_script( 'cookie-script', get_template_directory_uri() .'/assets/js/js.cookie.min.js', array('jquery'));
	wp_enqueue_script( 'swapchic-ajax', get_template_directory_uri() .'/assets/js/ajax.swapchic.js', array('jquery'));
	wp_enqueue_script( 'swapchic-chat', get_template_directory_uri() .'/assets/js/chat.swapchic.js', array('jquery'));
	wp_enqueue_script( 'swapchic-script', get_template_directory_uri() .'/assets/js/swapchic.js', array('jquery'));
	wp_enqueue_script( 'swapchic-map', get_template_directory_uri() .'/assets/js/map.swapchic.js', array('google-map'), time());
	wp_localize_script( 'swapchic-ajax', 'swapchic_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
	wp_localize_script( 'swapchic-chat', 'swapchic_chat', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action( 'wp_enqueue_scripts', 'swapchic_enqueue_styles', 100);


/* 
* Hide admin bar to contributors
* Parameters : none
* Return : none
*/
function showAdminBar() {
	if(is_user_logged_in()){
		$user = wp_get_current_user();
		if($user->roles[0] == 'subscriber' || $user->roles[0] == 'contributor') {
			return false;
		} elseif($user->roles[0] == 'administrator' || $user->roles[0] == 'editor' ) {
			return true;
		}
	}
}
add_filter('show_admin_bar', 'showAdminBar');


/* 
* Remove access to the dashboard to contributors
* Parameters : none
* Return : none
*/
function my_custom_dashboard_access_handler() {
   // Check if the current page is an admin page
   // && and ensure that this is not an ajax call
   if ( is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
      //Get all capabilities of the current user
      $user = get_userdata( get_current_user_id() );
      $caps = ( is_object( $user) ) ? array_keys($user->allcaps) : array();

      //All capabilities/roles listed here are not able to see the dashboard
      $block_access_to = array('subscriber', 'contributor');

      if(array_intersect($block_access_to, $caps)) {
         wp_redirect( home_url() );
         exit;
      }
   }
}
add_action( 'init', 'my_custom_dashboard_access_handler');

//Minify css, js and html by script
class FLHM_HTML_Compression
{
    protected $flhm_compress_css = true;
    protected $flhm_compress_js = true;
    protected $flhm_info_comment = true;
    protected $flhm_remove_comments = true;
    protected $html;
    public function __construct($html)
    {
        if (!empty($html))
        {
            $this->flhm_parseHTML($html);
        }
    }
    public function __toString()
    {
        return $this->html;
    }
    protected function flhm_bottomComment($raw, $compressed)
    {
        $raw = strlen($raw);
        $compressed = strlen($compressed);
        $savings = ($raw-$compressed) / $raw * 100;
        $savings = round($savings, 2);
        return '<!--HTML compressed, size saved '.$savings.'%. From '.$raw.' bytes, now '.$compressed.' bytes-->';
    }
    protected function flhm_minifyHTML($html)
    {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        $html = '';
        foreach ($matches as $token)
        {
            $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
            $content = $token[0];
            if (is_null($tag))
            {
                if ( !empty($token['script']) )
                {
                    $strip = $this->flhm_compress_js;
                }
                else if ( !empty($token['style']) )
                {
                    $strip = $this->flhm_compress_css;
                }
                else if ($content == '<!--wp-html-compression no compression-->')
                {
                    $overriding = !$overriding;
                    continue;
                }
                else if ($this->flhm_remove_comments)
                {
                    if (!$overriding && $raw_tag != 'textarea')
                    {
                        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                    }
                }
            }
            else
            {
                if ($tag == 'pre' || $tag == 'textarea')
                {
                    $raw_tag = $tag;
                }
                else if ($tag == '/pre' || $tag == '/textarea')
                {
                    $raw_tag = false;
                }
                else
                {
                    if ($raw_tag || $overriding)
                    {
                        $strip = false;
                    }
                    else
                    {
                        $strip = true;
                        $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                        $content = str_replace(' />', '/>', $content);
                    }
                }
            }
            if ($strip)
            {
                $content = $this->flhm_removeWhiteSpace($content);
            }
            $html .= $content;
        }
        return $html;
    }
    public function flhm_parseHTML($html)
    {
        $this->html = $this->flhm_minifyHTML($html);
        if ($this->flhm_info_comment)
        {
            $this->html .= "\n" . $this->flhm_bottomComment($html, $this->html);
        }
    }
    protected function flhm_removeWhiteSpace($str)
    {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        while (stristr($str, '  '))
        {
            $str = str_replace('  ', ' ', $str);
        }
        return $str;
    }
}
function flhm_wp_html_compression_finish($html)
{
    return new FLHM_HTML_Compression($html);
}
function flhm_wp_html_compression_start()
{
    ob_start('flhm_wp_html_compression_finish');
}
add_action('get_header', 'flhm_wp_html_compression_start');

if( !function_exists( 'theme_pagination' ) ) {
	
    function theme_pagination() {
	
	global $wp_query, $wp_rewrite;
	$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
	
	$pagination = array(
		'base' => @add_query_arg('page','%#%'),
		'format' => '',
		'total' => $wp_query->max_num_pages,
		'current' => $current,
	        'show_all' => false,
	        'end_size'     => 1,
	        'mid_size'     => 2,
		'type' => 'list',
		'next_text' => '»',
		'prev_text' => '«'
	);
	
	if( $wp_rewrite->using_permalinks() )
		$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
	
	if( !empty($wp_query->query_vars['s']) )
		$pagination['add_args'] = array( 's' => str_replace( ' ' , '+', get_query_var( 's' ) ) );
		
	echo str_replace('page/1/','', paginate_links( $pagination ) );
    }	
}