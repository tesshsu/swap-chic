<?php

define( 'WP_CACHE', true );    // Added by WP Rocket.

define('WP_MEMORY_LIMIT', '256M');
/**
* La configuration de base de votre installation WordPress.
* Ce fichier contient les réglages de configuration suivants : réglages MySQL,
* préfixe de table, clés secrètes, langue utilisée, et ABSPATH. * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'swapchicqndbuser' );


/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'swapchicqndbuser' );


/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '3BXd2wMmXR' );


/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'swapchicqndbuser.mysql.db' );


/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );


/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'FPR=*{0GN1@(fXk(NzhyTDP#I]G(J/fcr{WQMRQ#`%eg@AnZ0z8b<joZqNt]~HtX' );

define( 'SECURE_AUTH_KEY',  'k_{Z[jRY9kb+Ypp2z:l`nBu?k!{KNaaVGF+_R*49wqbWZ77*;zSsu,TXyD5d^ByX' );

define( 'LOGGED_IN_KEY',    'VP2k5~qSNMTi563:[khI?#nu&G:wIEDx65HJ.)$ `Z.HwTNdgrml?g&*wk/Iq@ >' );

define( 'NONCE_KEY',        '_y/.(O;tEg-cl:^LYb03Zjf^=I;v!dprG|tU8.:EOzyg.:6PI{@ ]!+UmX5+0t]t' );

define( 'AUTH_SALT',        ':-Cv :@S&d~uf$)tAG-|OR{-gA7L}PL:0-+gd,dSm&$w8bz0raA7bdcL36`76R].' );

define( 'SECURE_AUTH_SALT', 'h0b>#gTHt|WS/@3xm!K<pmwH,[p<JYNe8b,nH0H-fDYosO[3:F+:_opNDfX@c7Ff' );

define( 'LOGGED_IN_SALT',   'tfaorSvVar(,ef6_KP$:h{0faeya_R8bLgU;|qp=9/B`Z]P<)Fdh 7Ib.tzO~bH1' );

define( 'NONCE_SALT',       'qvgat<%^pxLAup9(),S~C1|/=d{Vg:5v?f-4kp-E~G qE!z5?gG5xk)qAF(iuU5$' );

/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

// Mail Definitions

define( 'SMTP_USER',   'noreply@swap-chic.com' );  // Username to use for SMTP authentication
define( 'SMTP_PASS',   'swapchicauto' );          // Password to use for SMTP authentication
define( 'SMTP_HOST',   'ssl0.ovh.net' );          // The hostname of the mail server
define( 'SMTP_FROM',   'noreply@swap-chic.com' ); // SMTP From email address
define( 'SMTP_NAME',   'Swap-Chic' );             // SMTP From name
define( 'SMTP_PORT',   '587' );                   // SMTP port number - likely to be 25, 465 or 587
define( 'SMTP_SECURE', 'tls' );                   // Encryption system to use - ssl or tls
define( 'SMTP_AUTH',    true );                   // Use SMTP authentication (true|false)
define( 'SMTP_DEBUG',   0 );                      // for debugging purposes only set to 1 or 2


/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
// define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');

//Disable cache
define( 'WP_CACHE', true );    // Added by WP Rocket.

define('WP_DEBUG_LOG', true);

define('WP_HOME','https://swap-chic.com');
define('WP_SITEURL','https://swap-chic.com');
