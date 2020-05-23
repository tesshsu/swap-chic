# Swap-Chic



Swap-Chic is a custom Wordpress theme built with Advanced Control Fields, jQuery and PHP, using the Google Maps API and the Administrative Division API of the french government.





# Introduction

## About the project



Disclaimer : Since the development did not follow any project managment and no precise specifications were provided, the resulting code is not of the highest quality.



Still, it's mostly basic PHP and jQuery, with this page, the code comments and the appropriate documentation, it should be understandable.



For the documentations, you can find them here :



[Wordpress](https://developer.wordpress.org/)



[PHP](https://www.php.net/docs.php)



[jQuery](https://api.jquery.com/)



[Cropper.js](https://github.com/fengyuanchen/cropperjs/blob/master/README.md)



[Slick](https://kenwheeler.github.io/slick/)



[ACF](https://www.advancedcustomfields.com/resources/)



[Google Maps](https://developers.google.com/maps/documentation/javascript/tutorial)



[Administrative Division API](https://geo.api.gouv.fr/decoupage-administratif)



## Data types



Swap-Chic works with 4 custom post types :



Products (or Produits), swap-places, dressings and discussions.



You can find their definitions [here](https://swap-chic.com/wp-admin/edit.php?post_type=acf-field-group)



They are fairly self-explainatory, we did not use the usual WooCommerce or similar plugins to manage our products because we did not need most of the functionnalities brought by them.



Also, a dressing is basically just an array of products, and it could have been a simple field directly added to user profiles, but since the users must be able to comment on dressings, the easy way was to create a custom post type (since Wordpdress doesn't allow users to comment on user profiles).



## Important details



In some pages, you'll find the mention of 'scope', it's how we define which data is displayed and in which order. It presents itself as follows :



```php

$scope = array (

    'scope' => array(

    // Can be a list of zip codes to define a city, 

    // or a single department code to define a department,

    // or a list of department codes to define a region

    ),

    'more' => array(

    // Can be a a single department code to define a department,

    // or a list of department codes to define a region

    ),

    'even_more' => array(

    // Can be a a list of department codes to define a region

    )

)

```

The scope level 'scope' is always set, and depending on its value(s), the other level are set, or not. 

For example, if the scope level 'scope' is a list of zip codes defining a city, the 'more' level will be the code of the department in which the city is and the 'even_more' level will be the list of the codes of the other depatments of the region.



We define the scope in map.swapchic.js, more information there.



# Known issues



The loading on the "Fil d'actualités" page is insanely long, and apparently, loading post (or even just images) asynchronously does not help, and showing post in a page by page format is not what the client wants.



We got a problem in Safari where the browser answers with the following error message : "Safari can’t open the page. Cannot decode raw data (NSURLErrorDomain:-1015)".
We fixed this by disabling gzip compression, however disabling gzip compression means that sharing a post on Facebook will send no image. Which is also a problem but way less important than the Safari bug.



# Relevant pages



Basic explanation of each relevant pages



## Ajout de produit (Add product)



The page where users fill up the form to add a product



## Alertes enregistrées (Saved alerts)



The page where users can find their saved alerts, an alert is simply an url search string which launches an advanced search



## Articles à valider (Products awaiting validation)



The page where admin can validate, or not, user products



## Catalogue (Catalog)



The page where users can see all specific post types in a specific scope level



## Connexion / Inscription (Sign in / Sign up)



The page where users log in and register



## Coups de coeur (Featured products)



The page where admin select which products are featured



## Éditer mon profil (Edit profile)



The page where users can edit their profile



## Editer produit (Edit product)



The page where users can edit a product



## Fil d’actualités (News feed)



The page where users can see all the new stuff happening in the defined scope



## Introduction



The page where users can discover the concept before registering or logging in

	

## Inviter vos amies (Invite your friends)



The page where users can send an invite mail to their friends



## Liste de souhait, Membres suivies, Swap-places favorites (Whishlist, Followed members, Favorites swap-places)



The page where users can see their liked products, dressings or swap-places



## Messagerie (Messaging)



The page where users can see all their discussions and notifications



## Nouvelle discussion (New discussion)



The page where users can start a new discussion



## Proposer une swap-place (Suggest a swap-place)



The page where users can suggest swap-places



## Recherche avancée (Advanced search)



The page where users can select 



## Stats



The page where admin can see diverse statistics about the websote

