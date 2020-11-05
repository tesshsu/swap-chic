/*
* Return the url path as an array
* Parameters : none
* Return : array
*/
function urlToArray() {
    return window.location.pathname.split('/');
}

/*
* Hide and remove loader
* Parameters : none
* Return : none
*/
function loaderHide() {
    jQuery('.loader').fadeOut(function(){
        jQuery('.loader').remove();
    });
}


/*
* Get the actual scope
* Parameters : none
* Return : array scope_array
*/
function getScope() {
    var scope_array = {
        "scope": [],
        "more": [],
        "even_more": []
    };

    var search = window.location.search;
    
    var scope = search.substr(1, search.length).split('&');
    for(var j = 0; j < scope.length; j++) {
        // Look for the name of the parameter
        var scope_level = scope[j].substr(0, scope[j].indexOf("="));
        // Look for the value(s) of the parameter
        scope[j] = scope[j].substr(scope[j].indexOf("=") + 1, scope[j].length);
        
        // Split the values depending on the character
        if(scope[j].indexOf('%2C') >= 0 ) {
            scope_array[scope_level] = scope[j].split('%2C');
        } else if(scope[j].indexOf(',') >= 0) {
            scope_array[scope_level] = scope[j].split(',');
        } else {
            scope_array[scope_level] = scope[j];
        }
    }

    if(scope_array["scope"][0] == null) {
        return false;
    } else {
        return scope_array;
    }
}

/*
* Get the lowest scope level
* Parameters : array scope
* Return : string 'ville', 'departement', region
*/
function getLowestScopeLevel(scope) {
    if(scope['scope'].indexOf('%2C') >= 0 ) {
        scope_array = scope['scope'].split('%2C');
        if(scope_array[0].length > 2) {
            return 'ville';
        } else {
            return 'region';
        }
    } else if(scope['scope'].indexOf(',') >= 0) {
        scope_array = scope['scope'].split(',');
        if(scope_array[0].length > 2) {
            return 'ville';
        } else {
            return 'region';
        }
    } else if(Array.isArray(scope['scope'])){
        if(scope['scope'][0].length > 2) {
            return 'ville';
        } else {
            return 'region';
        }        
    } else {
        scope_array = scope['scope'];
        if(scope_array.length > 2) {
            return 'ville';
        } else {
            return 'departement';
        }
    }
}

/*
* Get an adress  from longitude and latitude 
* Parameters : object pos, google.maps.Geocoder geocoder
* Return : Promise
*/
function reverseGeocode(pos, geocoder) {
    return new Promise(function(resolve, reject) {
        geocoder.geocode( { 'location': pos}, function(results, status) {
            if (status == 'OK') {
                resolve(results);
            } else if (status == 'OVER_QUERY_LIMIT') {
                setTimeout(function() {
                    return reverseGeocode(pos, geocoder);
                }, 300);
            } else {
                reject();
            }
        });
    });
}

/*
* Replace the current scope
* Parameters : array || string scope, array || string more, array || string even_more, string other_params
* Return : none
*/
function replaceScope(scope, more = null, even_more = null, other_params = null) {
    var scope_array = '';
    var more_array = '';
    var even_more_array = '';
    var href = window.location.origin + window.location.pathname;
    if(Array.isArray(scope)) {
        for(var i = 0; i < scope.length; i++) {
            if(i == 0) {
                scope_array += '?scope='+scope[i];
            } else {
                scope_array += '%2C'+scope[i];
            }
        }
        href += scope_array;
    } else {
        scope = '?scope=' + scope;
        href += scope;
    }
    if(more != null) {
        if(Array.isArray(more)) {
            for(var i = 0; i < more.length; i++) {
                if(i == 0) {
                    more_array += '&more='+more[i];
                } else {
                    more_array += '%2C'+more[i];
                }
            }
            href += more_array;
        } else {
            more = '&more=' + more;
            href += more;
        }
    }
    if(even_more != null) {
        if(Array.isArray(even_more)) {
            for(var i = 0; i < even_more.length; i++) {
                if(i == 0) {
                    even_more_array += '&even_more='+even_more[i];
                } else {
                    even_more_array += '%2C'+even_more[i];
                }
            }
            href += even_more_array;
        } else {
            even_more = '&even_more=' + even_more;
            href += even_more;
        }
    }
    if(other_params != null) {
        href += other_params;
    }
    window.location.replace(href);
}

/*
* Get longitude and latitude from an address
* Parameters : string address, google.maps.Geocoder geocoder
* Return : Promise
*/
function geocodeAddress(address, geocoder) {
    return new Promise(function(resolve, reject) {
        geocoder.geocode( { 'address': address+' France'}, function(results, status) {
            if (status == 'OK') {
                resolve(results);
            } else if (status == 'OVER_QUERY_LIMIT') {
                setTimeout(function() {
                    return geocodeAddress(address, geocoder);
                }, 300);
            } else {
                reject(new Error('Could\'nt find address'));
            }
        });
    });
}

jQuery(document).ready(function() {
    var maps = {};
    var user_lat_lng = false;
    var scope = getScope();
    var scope_lvl = false
    var more = [];
    var geolocation_pos = false;
    var geocoder = new google.maps.Geocoder();
    var lat_lng = new google.maps.LatLng(-34.397, 150.644);
    var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, polylineOptions: {strokeColor: "#3c3c3c"}});
    var directionsService = new google.maps.DirectionsService();
    var map_options = { zoom: 14, center: lat_lng, disableDefaultUI: true, mapTypeId: google.maps.MapTypeId.ROADMAP, styles: [{featureType: "poi", stylers: [{ visibility: "off" }]}] };
    var user_icon = {url: window.location.origin+"/wp-content/themes/Swap-Chic/assets/images/user_marker.png"};
    var swapplace_icon = {url: window.location.origin+"/wp-content/themes/Swap-Chic/assets/images/swapplace_marker.png"};
    var user_marker = [];
    var marker_array = [];

    /*
    * Scope setup
    */
    if(scope == false || scope["scope"][0] == "") { 
        if(window.location.pathname == "/actualites/" 
        || window.location.pathname == "/swap-places-2/" 
        || window.location.pathname == "/messagerie/nouvelle-discussion/") { 
            // If no scope is set and the user is on one of the relevant pages
            var other_params = '&'+window.location.search.substr(1, window.location.search.length - 1);
            // console.log('Localisation pas encore définie');

            if(navigator.geolocation) {
                // If the user's browser supports geolocation

                // Set a timeout for user to accept or deny geolocation request (why : https://stackoverflow.com/questions/9219540/how-to-handle-when-user-closes-physical-location-prompt-in-firefox-and-chrome)
                var etimeout = setTimeout(onError, 10000);
                navigator.geolocation.getCurrentPosition(onSuccess, onError, {timeout:5000});

                function onSuccess(geolocation_pos) {
                    // Once we get the user's geolocation
                    
                    clearTimeout(etimeout);
                    // console.log('Position renvoyé par le service de geolocalisation : '+'\n    Latitude : '+geolocation_pos.coords.latitude+'\n    Longitude : '+geolocation_pos.coords.longitude);

                    var geolocation_str = {lat: geolocation_pos.coords.latitude, lng: geolocation_pos.coords.longitude};
                    var geocode_promise = reverseGeocode(geolocation_str, geocoder);
                    geocode_promise.then(
                        function(value) {  // Promise resolved
                            // console.log(value[0]);
                            // console.log('Adresse correspondante : \n'+value[0].formatted_address);
                            scope = [];
                            more = [];
                            even_more = [];
                            for(var i = 0; i < value.length; i++) {
                                // We look for the zip code value 
                                if(value[i].types == "postal_code") {
                                    // We get the other zip code of the city and the department code
                                    jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+value[i].address_components[0].long_name+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value) {
                                        for(var i = 0; i < commune_value[0].codesPostaux.length; i++) {
                                            scope.push(commune_value[0].codesPostaux[i]);
                                        }
                                        more = commune_value[0].codeDepartement;
                                        // Then we get the other department codes form the region
                                        jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[0].codeRegion+"/departements?fields=nom,code").then(function(dpt_value){
                                            for(var i = 0; i < dpt_value.length; i++) {
                                                if(dpt_value[i].code != more) {
                                                    even_more.push(dpt_value[i].code);
                                                }
                                            }
                                            replaceScope(scope, more, even_more, other_params);
                                        });
                                    });
                                }
                            }
                        },
                        function() {  // Promise rejected
                            // If the geocoding fails, we use the registered zip code of the current user
                            // console.log('Erreur, on utilise le code postal associé au compte : '+jQuery('[data-basescope]').attr('data-basescope'));
                            scope = [];
                            more = [];
                            even_more = [];
                            // We get the other zip code of the city and the department code
                            jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+jQuery('[data-basescope]').attr('data-basescope')+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value) {
                                    for(var i = 0; i < commune_value[0].codesPostaux.length; i++) {
                                        scope.push(commune_value[0].codesPostaux[i]);
                                    }
                                    more = commune_value[0].codeDepartement;
                                    // Then we get the other department codes form the region
                                jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[0].codeRegion+"/departements?fields=nom,code").then(function(dpt_value){
                                    for(var i = 0; i < dpt_value.length; i++) {
                                        if(dpt_value[i].code != more) {
                                            even_more.push(dpt_value[i].code);
                                        }
                                    }
                                    replaceScope(scope, more, even_more);
                                });
                            });
                        }
                    );
                }

                function onError() {
                    // If the user denies the geolocation request
                    clearTimeout(etimeout);
                    // We alert him once that, if needed, he can change his choice
                    if ( Cookies.get("alert_geolocation") != 1  && window.location.pathname.split('/')[1] == 'actualites') { 
                        alert('Chère membre,\n Tu as refusé de partager tes données de géolocalisation avec Swap-Chic, si dans le futur tu change d\'avis, rends toi dans les options de ton navigateur ou dans les paramètres de ton appareil !\n Bon shopping 😉');
                        Cookies.set("alert_geolocation", 1, { expires: 36500 });
                    }
                    // We use the registered zip code of the current user
                    // console.log('On utilise le code postal associé au compte : '+jQuery('[data-basescope]').attr('data-basescope'));
                    scope = [];
                    more = [];
                    even_more = [];
                    // We get the other zip code of the city and the department code
                    jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+jQuery('[data-basescope]').attr('data-basescope')+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value) {
                            for(var i = 0; i < commune_value[0].codesPostaux.length; i++) {
                                scope.push(commune_value[0].codesPostaux[i]);
                            }
                            more = commune_value[0].codeDepartement;
                            // Then we get the other department codes form the region
                        jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[0].codeRegion+"/departements?fields=nom,code").then(function(dpt_value){
                            for(var i = 0; i < dpt_value.length; i++) {
                                if(dpt_value[i].code != more) {
                                    even_more.push(dpt_value[i].code);
                                }
                            }
                            replaceScope(scope, more, even_more, other_params);
                        });
                    });
                };

            } else {
                // If the user's browser does not support navigator.geolocation
                // We use the registered zip code of the current user                
                // console.log('Le navigateur ne supporte pas le service de géolocalisation, on utilise le code postal associé au compte : '+jQuery('[data-basescope]').attr('data-basescope'));
                scope = [];
                more = [];
                even_more = [];
                // We get the other zip code of the city and the department code
                jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+jQuery('[data-basescope]').attr('data-basescope')+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value) {
                    for(var i = 0; i < commune_value[0].codesPostaux.length; i++) {
                        scope.push(commune_value[0].codesPostaux[i]);
                    }
                    more = commune_value[0].codeDepartement;
                    // Then we get the other department codes form the region
                    jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[0].codeRegion+"/departements?fields=nom,code").then(function(dpt_value){
                        for(var i = 0; i < dpt_value.length; i++) {
                            if(dpt_value[i].code != more) {
                                even_more.push(dpt_value[i].code);
                            }
                        }
                        replaceScope(scope, more, even_more, other_params);
                    });
                });
            }
        }
    }

    if( scope != false) {
        scope_lvl = getLowestScopeLevel(scope);
        if(Array.isArray(scope["scope"])) {
            var target = scope["scope"][0];
        } else {
            var target = scope["scope"];
        }

        
        // Display the associated name with the lowest scope level
        if(jQuery('.scope-toggle').length) {
            // Missing data from google 
            if(target == '31000') {
                jQuery('.scope').html('Toulouse');
                clearTimeout(loader_timeout);
                loaderHide();
            } else if(target == 'Nantes' || target == '44100') {
                jQuery('.scope').html('Nantes');
                clearTimeout(loader_timeout);
                loaderHide();
            } else {
                geocoder.geocode( { 'address': target+' France'}, function(results, status) {
                    if(status == 'OK') {
                        if(scope_lvl != 'departement') {
                            jQuery('.scope').html(results[0].address_components[1].short_name);
                        } else {
                            jQuery('.scope').html(results[0].address_components[0].short_name);
                        }
                    } else {
                        console.log(target);
                        console.log(status);
                    }
                    clearTimeout(loader_timeout);
                    loaderHide();
                    /*if ( Cookies.get("hide-helps") == 1 ) {
                        jQuery('html, body').css('overflow', 'visible');
                    } else {
                        jQuery("html, body").scrollTop(0);
                    }*/
                });
            }
        } else {
            clearTimeout(loader_timeout);
            loaderHide();
        }

        // When the user request to see another location
        jQuery('#scope-modal input[type=submit]').click(function(e){
            e.preventDefault();
            var new_scope = jQuery('#scope-modal input[name=scope]').val();
            // We geocode the new address
            geocodeAddress(new_scope, geocoder).then(function(value){
                // console.log(value);
                if(value[0].address_components[0].types[0] == "administrative_area_level_1") {
                    // If it's a region, we get all its departments, setup the scope and replace the old one
                    jQuery.getJSON("https://geo.api.gouv.fr/regions?nom="+value[0].address_components[0].long_name+"&fields=nom,code").then(function(region_value){
                        jQuery.getJSON("https://geo.api.gouv.fr/regions/"+region_value[0].code+"/departements?fields=nom,code").then(function(region_values) {
                            scope = [];
                            for(var i = 0; i < region_values.length; i++) {
                                scope.push(region_values[i].code);
                            }
                            replaceScope(scope);
                        });
                    });
                } else if(value[0].address_components[0].types[0] == "administrative_area_level_2") {
                    // If it's a department, we get all its region's other departments, setup the scope and replace the old one
                    jQuery.getJSON("https://geo.api.gouv.fr/departements?nom="+value[0].address_components[0].long_name+"&fields=nom,code,codeRegion").then(function(dpt_value){
                        scope = dpt_value[0].code;
                        jQuery.getJSON("https://geo.api.gouv.fr/regions/"+dpt_value[0].codeRegion+"/departements?fields=nom,code").then(function(region_value) {
                            more = [];
                            for(var i = 0; i < region_value.length; i++) {
                                if(region_value[i].code != scope){
                                    more.push(region_value[i].code);
                                }
                            }
                            replaceScope(scope, more);
                        });
                    });
                } else if(value[0].address_components[0].types[0] == "locality") {
                    // If it's a city

                    jQuery.getJSON("https://geo.api.gouv.fr/communes?nom="+value[0].address_components[0].long_name+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value){
                        for(var i = 0; i < commune_value.length; i++) {
                            // The API return all cities matching the name, so we search for the right one
                            if(commune_value[i].nom == value[0].address_components[0].long_name) {
                                scope = [];
                                more = [];
                                even_more = [];
                                // We setup the scope with the zip codes
                                scope.push(commune_value[i].codesPostaux);
                                // We setup the 'more' level with the department code
                                more = commune_value[i].codeDepartement;
                                // We look for the region other department
                                jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[i].codeRegion+"/departements?fields=nom,code").then(function(commune_values){
                                    for(var i = 0; i < commune_values.length; i++) {
                                        if(commune_values[i].code != more) {
                                            even_more.push(commune_values[i].code);
                                        }
                                    }
                                    replaceScope(scope, more, even_more);
                                });
                            }
                        }
                    });
                } else if(value[0].address_components[0].types[0] == "postal_code") {
                    // If it's a zip code

                    scope = [];
                    // We look for the city's other zip codes the we setup the scope
                    jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+value[0].address_components[0].long_name+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value) {
                        for(var i = 0; i < commune_value[0].codesPostaux.length; i++) {
                            scope.push(commune_value[0].codesPostaux[i]);
                        }
                        // We setup the 'more' level with the department code
                        more = commune_value[0].codeDepartement;
                        even_more = [];
                        // We get the region other departments 
                        jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[0].codeRegion+"/departements?fields=nom,code").then(function(region_value){
                            for(var i = 0; i < region_value.length; i++) {
                                if(region_value[i].code != more) {
                                    even_more.push(region_value[i].code);
                                }
                            }
                            replaceScope(scope, more, even_more);
                        });
                    });
                } else if(value[0].address_components[1].types[0] == "locality") {
                    // If it's a city again, see above
                    jQuery.getJSON("https://geo.api.gouv.fr/communes?nom="+value[0].address_components[1].long_name+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(commune_value){
                        for(var i = 0; i < commune_value.length; i++) {
                            if(commune_value[i].nom == value[0].address_components[1].long_name) {
                                scope = [];
                                more = [];
                                even_more = [];
                                scope.push(commune_value[i].codesPostaux);
                                more = commune_value[i].codeDepartement;
                                jQuery.getJSON("https://geo.api.gouv.fr/regions/"+commune_value[i].codeRegion+"/departements?fields=nom,code").then(function(commune_values){
                                    for(var i = 0; i < commune_values.length; i++) {
                                        if(commune_values[i].code != more) {
                                            even_more.push(commune_values[i].code);
                                        }
                                    }
                                    replaceScope(scope, more, even_more);
                                });
                            }
                        }
                    });
                } else {
                    jQuery('#scope-modal form').prepend('<p class="error">Valeur invalide...</p>');
                    jQuery('#scope-modal input[type=text]').css('border', '1px solid red');
                }
            }, function() {
                jQuery('#scope-modal form').prepend('<p class="error">Valeur invalide...</p>');
                jQuery('#scope-modal input[type=text]').css('border', '1px solid red');
            });
        });

        // When the user request to see his location through geolocation
        jQuery('#scope-modal #geolocalisation').click(function(e){
            /*
            *  REFER TO THE SCOPE SETUP PART, line 202
            */
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition( function(pos) {
                    geolocation_pos = pos;
                    //console.log('Position renvoyé par le service de geolocalisation : '+'\n    Latitude : '+geolocation_pos.coords.latitude+'\n    Longitude : '+geolocation_pos.coords.longitude);
                    var geolocation_str = {lat: geolocation_pos.coords.latitude, lng: geolocation_pos.coords.longitude};
                    reverseGeocode(geolocation_str, geocoder).then(function(value){
                       // alert('Adresse correspondante : \n'+value[0].formatted_address);
                        scope = [];
                        more = [];
                        even_more = [];
                        for(var i = 0; i < value.length; i++) {
                            if(value[i].types == "postal_code") {
                                jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+value[i].address_components[0].long_name+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(dpt_value) {
                                    for(var i = 0; i < dpt_value[0].codesPostaux.length; i++) {
                                        scope.push(dpt_value[0].codesPostaux[i]);
                                    }
                                    more = dpt_value[0].codeDepartement;
                                    jQuery.getJSON("https://geo.api.gouv.fr/regions/"+dpt_value[0].codeRegion+"/departements?fields=nom,code").then(function(commune_values){
                                        for(var i = 0; i < commune_values.length; i++) {
                                            if(commune_values[i].code != more) {
                                                even_more.push(commune_values[i].code);
                                            }
                                        }
                                        replaceScope(scope, more, even_more);
                                    });
                                });
                            }
                        }
                    });
                }, function() {
                   alert('Swap-Chic n\'a pas l\'autorisation d\'utiliser ta geolocalistaion.\nTu peux modifier cet option dans les paramètres de ton navigateur ou de ton appareil.');
                });
            } else {
                alert('Swap-Chic n\'a pas l\'autorisation d\'utiliser ta geolocalistaion.\nTu peux modifier cet option dans les paramètres de ton navigateur ou de ton appareil.');
                scope = [];
                more = [];
                even_more = [];
                jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+jQuery('[data-basescope]').attr('data-basescope')+"&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre").then(function(dpt_value) {
                    for(var i = 0; i < dpt_value[0].codesPostaux.length; i++) {
                        scope.push(dpt_value[0].codesPostaux[i]);
                    }
                    more = dpt_value[0].codeDepartement;
                    jQuery.getJSON("https://geo.api.gouv.fr/regions/"+dpt_value[0].codeRegion+"/departements?fields=nom,code").then(function(commune_values){
                        for(var i = 0; i < commune_values.length; i++) {
                            if(commune_values[i].code != more) {
                                even_more.push(commune_values[i].code);
                            }
                        }
                        //replaceScope(scope, more, even_more);
                    });
                });
            }
        });

        // If there is a map on the page
        if(jQuery('.map').length) {
            // Setup the location for the maps
            if(jQuery('#map-scope').length) {
                maps['#map-scope'] = new google.maps.Map(document.getElementById('map-scope'), map_options);
            }
            if(jQuery('#map-more').length) {
                maps['#map-more'] = new google.maps.Map(document.getElementById('map-more'), map_options);
            }
            if(jQuery('#map-even_more').length) {
                maps['#map-even_more'] = new google.maps.Map(document.getElementById('map-even_more'), map_options);
            }

            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition( function(pos){
                    // If the user can be located, we put a marker on its location on each map
                    var geolocation_str = {lat: pos.coords.latitude, lng: pos.coords.longitude};
                    reverseGeocode(geolocation_str, geocoder).then(function(value){
                        user_lat_lng =  new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
                        if(jQuery('#map-scope').length) {
                            user_marker['scope'] = new google.maps.Marker({
                                map: maps['#map-scope'],
                                position: user_lat_lng,
                                icon: user_icon
                            });
                            maps['#map-scope'].setCenter(user_lat_lng);
                            // We zoom the maps accordingly
                            if(jQuery('#map-scope').parent().attr('data-level') == 'ville') {
                                maps['#map-scope'].setZoom(15);
                            } else if(jQuery('#map-scope').parent().attr('data-level') == 'departement') {
                                maps['#map-scope'].setZoom(8);
                            } else {
                                maps['#map-scope'].setZoom(6);
                            }
                        }
                        if(jQuery('#map-more').length) {
                            user_marker['more'] = new google.maps.Marker({
                                map: maps['#map-more'],
                                position: user_lat_lng,
                                icon: user_icon
                            });
                            maps['#map-more'].setCenter(user_lat_lng);
                            if(jQuery('#map-more').parent().attr('data-level') == 'departement') {
                                maps['#map-more'].setZoom(8);
                            } else {
                                maps['#map-more'].setZoom(6);
                            }
                        }
                        if(jQuery('#map-even_more').length) {
                            user_marker['even_more'] = new google.maps.Marker({
                                map: maps['#map-even_more'],
                                position: user_lat_lng,
                                icon: user_icon
                            });
                            maps['#map-even_more'].setCenter(user_lat_lng);
                            maps['#map-even_more'].setZoom(6);
                        }
                    });
                }, function() { 
                    // If the user cannot be located, we hide the geolocation based functionalities
                    jQuery('.map-draw').hide();
                    jQuery('.map-reset').hide();
                    jQuery('.map-locate').hide();

                    if(Array.isArray(scope["scope"])) {
                        var target_scope = scope["scope"][0];
                    } else {
                        var target_scope = scope["scope"];
                    }
                    if(Array.isArray(scope["more"])) { 
                        var target_more = scope["more"][0];
                    } else {
                        var target_more = scope["more"];
                    }
                    var target_even_more = scope["even_more"][0];

                    // We get the location of each scope level 
                    if(jQuery('#map-scope').length) {
                        if(jQuery('#map-scope').parent().attr('data-level') == 'ville') {
                            jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+target_scope+"&fields=nom,code,codesPostaux").then(function(ville) {
                                geocodeAddress(ville[0].nom, geocoder).then(function(value){
                                    lat_lng = value[0].geometry.location;
                                    maps['#map-scope'].setCenter(lat_lng);
                                    maps['#map-scope'].setZoom(13);
                                });
                            });
                        } else if(jQuery('#map-scope').parent().attr('data-level') == 'departement') {
                            geocodeAddress(target_scope, geocoder).then(function(value){
                                lat_lng = value[0].geometry.location;
                                maps['#map-scope'].setCenter(lat_lng);
                                maps['#map-scope'].setZoom(8);
                            });
                        } else {
                            geocodeAddress(target_scope, geocoder).then(function(value){
                                lat_lng = value[0].geometry.location;
                                maps['#map-scope'].setCenter(lat_lng);
                                maps['#map-scope'].setZoom(6);
                            });
                        }
                    }
                    if(jQuery('#map-more').length) {
                        if(jQuery('#map-more').parent().attr('data-level') == 'departement') {
                            geocodeAddress(target_more, geocoder).then(function(value){
                                lat_lng = value[0].geometry.location;
                                maps['#map-more'].setCenter(lat_lng);
                                maps['#map-more'].setZoom(8);
                            });
                        } else {
                            geocodeAddress(target_more, geocoder).then(function(value){
                                lat_lng = value[0].geometry.location;
                                maps['#map-more'].setCenter(lat_lng);
                                maps['#map-more'].setZoom(6);
                            });
                        }
                    }
                    if(jQuery('#map-even_more').length) {
                        geocodeAddress(target_even_more, geocoder).then(function(value){
                            lat_lng = value[0].geometry.location;
                            maps['#map-even_more'].setCenter(lat_lng);
                            maps['#map-even_more'].setZoom(6);
                        });
                    } 
                });
            } else {
                // If the user's browser does not support geolocation, refer to line 601
                jQuery('.map-draw').hide();
                jQuery('.map-reset').hide();
                jQuery('.map-locate').hide();
                if(Array.isArray(scope["scope"])) {
                    var target_scope = scope["scope"][0];
                } else {
                    var target_scope = scope["scope"];
                }
                if(Array.isArray(scope["more"])) { 
                    var target_more = scope["more"][0];
                } else {
                    var target_more = scope["more"];
                }
                var target_even_more = scope["even_more"][0];
                if(jQuery('#map-scope').length) {
                    if(jQuery('#map-scope').parent().attr('data-level') == 'ville') {
                        jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal="+target_scope+"&fields=nom,code,codesPostaux").then(function(ville) {
                            geocodeAddress(ville[0].nom, geocoder).then(function(value){
                                lat_lng = value[0].geometry.location;
                                maps['#map-scope'].setCenter(lat_lng);
                                maps['#map-scope'].setZoom(13);
                            });
                        });
                    } else if(jQuery('#map-scope').parent().attr('data-level') == 'departement') {
                        geocodeAddress(target_scope, geocoder).then(function(value){
                            lat_lng = value[0].geometry.location;
                            maps['#map-scope'].setCenter(lat_lng);
                            maps['#map-scope'].setZoom(8);
                        });
                    } else {
                        geocodeAddress(target_scope, geocoder).then(function(value){
                            lat_lng = value[0].geometry.location;
                            maps['#map-scope'].setCenter(lat_lng);
                            maps['#map-scope'].setZoom(6);
                        });
                    }
                }
                if(jQuery('#map-more').length) {
                    if(jQuery('#map-more').parent().attr('data-level') == 'departement') {
                        geocodeAddress(target_more, geocoder).then(function(value){
                            lat_lng = value[0].geometry.location;
                            maps['#map-more'].setCenter(lat_lng);
                            maps['#map-more'].setZoom(8);
                        });
                    } else {
                        geocodeAddress(target_more, geocoder).then(function(value){
                            lat_lng = value[0].geometry.location;
                            maps['#map-more'].setCenter(lat_lng);
                            maps['#map-more'].setZoom(6);
                        });
                    }
                }
                if(jQuery('#map-even_more').length) {
                    geocodeAddress(target_even_more, geocoder).then(function(value){
                        lat_lng = value[0].geometry.location;
                        maps['#map-even_more'].setCenter(lat_lng);
                        maps['#map-even_more'].setZoom(6);
                    });
                } 
            }
        }
        

        jQuery('.map .swapplaces-caroussel .slick-slide:not(.slick-cloned)').each(function(){
            // We get the data of all the swap-places showed under the map
            var map_name =  jQuery(this).parents('.map').children('.map-iframe').attr('id');
            var slide_id = jQuery(this).attr('data-slick-index');
            var post_id = jQuery(this).attr('data-id');
            var post = new wp.api.models.Swapplaces({id: post_id});
            post.fetch().done( function(response) {
                // We put a marker on the right map
                lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng);
                var marker = new google.maps.Marker({
                    map: maps['#'+map_name],
                    position: lat_lng,
                    icon: swapplace_icon
                });
                // We also register the data in a marker_array
                marker_array.push([map_name, response.id, slide_id, marker]);
                
                // If the user clicks on a marker
                google.maps.event.addListener(marker, 'click', function() {
                    for(var i = 0; i < marker_array.length; i++) {
                        // Looks for the right marker in the marker_array
                        if(marker_array[i][3] == marker) {
                            if(user_lat_lng != false) {
                                // Display draw functionality if the user is located
                                jQuery('#'+marker_array[i][0]).parent().children('.map-draw').hide();
                                jQuery('#'+marker_array[i][0]).parent().children('.map-reset').show();
                            }
                            // Turn the carousel to the right swap-place
                            jQuery('#'+marker_array[i][0]).parent().find('.swapplaces-caroussel').slick('slickGoTo', marker_array[i][2]);
                            var offset  =  jQuery('#'+marker_array[i][0]).parent().offset().top;
                            jQuery("html, body").animate({scrollTop:offset}, 300);
                        }
                    }
                });
            });
        });
    
        // When swping throught the swap=places carousel
        jQuery('.map .swapplaces-caroussel').on('beforeChange', function(event, slick, currentSlide, nextSlide){
            var map_name = jQuery(this).parents('.map').children('.map-iframe').attr('id');
            if(user_lat_lng != false) {
                // Display draw functionality if the user is located
                jQuery('#'+map_name).parent().children('.map-reset').hide();
                jQuery('#'+map_name).parent().children('.map-draw').show();
            }
            // We reset the marker on the map
            for(var i = 0; i < marker_array.length; i++) {
                if(marker_array[i][0] == map_name) {
                    marker_array[i][3].setMap(maps['#'+map_name]);
                }
            }
            jQuery(this).find('.slick-slide').each(function(){
                // We look for the next slide
                if(jQuery(this).attr('data-slick-index') == nextSlide) {
                    // We reset the direction trace
                    directionsDisplay.setMap(null);
                    var post_id = jQuery(this).attr('data-id');
                    post = new wp.api.models.Swapplaces({id: post_id});
                    post.fetch().done( function(response) {
                        // We center the map on the right marker 
                        lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng);
                        maps['#'+map_name].setCenter(lat_lng);
                        maps['#'+map_name].setZoom(17);
                    });
                }
            });
        });

        // Redirect user to swap-place page
        jQuery('.map .swapplaces-caroussel-infos .slick-slide .href').click(function(){
            window.location.assign('https://' + window.location.host + '/swap-places/' + jQuery(this).parents('[data-slug]').attr('data-slug'));
        });

        // When asking for directions
        jQuery('.map-draw').click(function() {
            var map_name =  jQuery(this).parents('.map').children('.map-iframe').attr('id');
            // Reset direction trace
            directionsDisplay.setMap(null);
            var post_id = jQuery(this).siblings('.map-bottom').children('.swapplaces-caroussel').find('.slick-current').attr('data-id');
            // Only displays the concerned marker on the map
            for(var i = 0; i < marker_array.length; i++) {
                if(post_id == marker_array[i][1] && marker_array[i][0] == map_name) {
                    marker_array[i][3].setMap(maps['#'+map_name]);
                } else if(post_id != marker_array[i][1] && marker_array[i][0] == map_name) {
                    marker_array[i][3].setMap(null);
                }
            }
            post = new wp.api.models.Swapplaces({id: post_id});
            post.fetch().done( function(response) {
                lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng);
                maps['#'+map_name].setCenter(lat_lng);
                maps['#'+map_name].setZoom(17);
                if(user_lat_lng != false) {
                    // Setup the direction request
                    var start = user_lat_lng;
                    var end = lat_lng;
                    var request = {
                        origin: start,
                        destination: end,
                        travelMode: google.maps.TravelMode.DRIVING
                    };
                    // Send the direction request
                    directionsService.route(request, function(response, status) {
                        if (status == google.maps.DirectionsStatus.OK) {
                            // Display route on map
                            directionsDisplay.setDirections(response);
                            directionsDisplay.setMap(maps['#'+map_name]);
                        } else {
                             alert("Trajet a échoué : " + status);
                        }
                    });
                    directionsDisplay.setMap(maps['#'+map_name]);
                }
            });
            jQuery('.map-draw').hide();
            jQuery('.map-reset').show();
        });

        // Reset map completly and set center on current swap-place
        jQuery('.map-reset').click(function() {
            var map_name =  jQuery(this).parents('.map').children('.map-iframe').attr('id');
            var post_id = jQuery(this).siblings('.map-bottom').children('.swapplaces-caroussel').find('.slick-current').attr('data-id');
            post = new wp.api.models.Swapplaces({id: post_id});
            post.fetch().done( function(response) {
                lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng);
                maps['#'+map_name].setCenter(lat_lng);
                maps['#'+map_name].setZoom(15);
            });
            // Remove route trace
            directionsDisplay.setMap(null)
            for(var i = 0; i < marker_array.length; i++) {
                // Puts back the markers on the map
                if(marker_array[i][0] == map_name) {
                    marker_array[i][3].setMap(maps['#'+map_name]);
                }
            }
            jQuery('.map-reset').hide();
            jQuery('.map-draw').show();
        });

        // Reset map completly and set center on user
        jQuery('.map-locate').click(function() {
            var map_name =  jQuery(this).parents('.map').children('.map-iframe').attr('id');
            maps['#'+map_name].setCenter(user_lat_lng);
            maps['#'+map_name].setZoom(15);
            directionsDisplay.setMap(null)
            for(var i = 0; i < marker_array.length; i++) {
                if(marker_array[i][0] == map_name) {
                    marker_array[i][3].setMap(maps['#'+map_name]);
                }
            }
            jQuery('.map-reset').hide();
            jQuery('.map-draw').show();
        });
    }

    // Show swap-place map
    jQuery('[data-type=swapplace] .map-toggle').click(function(){
        var context = this;
        var swapplace_id = jQuery(context).parents('[data-id]').attr('data-id');

        jQuery(context).siblings('.picture-toggle').css('opacity', '.5');
        jQuery(context).css('opacity', '1');
        jQuery(context).parents('[data-type=swapplace]').children('.swapplace-carousel').hide();
        jQuery(context).parents('[data-type=swapplace]').children('.swapplace-map').show();

        post = new wp.api.models.Swapplaces({id: swapplace_id});
        post.fetch().done( function(response) {
            maps[swapplace_id] = new google.maps.Map(jQuery(context).parents('.infos-wrapper').siblings('.swapplace-map')[0],  map_options);
            lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng)
            maps[swapplace_id].setCenter(lat_lng);
            maps[swapplace_id].setZoom(17);
            var marker = new google.maps.Marker({
                map: maps[swapplace_id],
                position: lat_lng,
                icon: swapplace_icon
            });
            if(user_lat_lng != false) {
                // If the user is located, show route
                var marker = new google.maps.Marker({
                    map: maps[swapplace_id],
                    position: user_lat_lng,
                    icon: user_icon
                });
                var start = user_lat_lng;
                var end = lat_lng;
                var request = {
                    origin: start,
                    destination: end,
                    travelMode: google.maps.TravelMode.DRIVING
                };
                directionsService.route(request, function(response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                        directionsDisplay.setMap(maps[swapplace_id]);
                    } else {
                        // alert("Trajet depuis " + start.toUrlValue(6) + " vers " + end.toUrlValue(6) + " a échoué : " + status);
                    }
                });
                directionsDisplay.setMap(maps[swapplace_id]);
            }
        });
    });

    // Show swap-place picture
    jQuery('[data-type=swapplace] .picture-toggle').click(function(){
        jQuery(this).siblings('.map-toggle').css('opacity', '.5');
        jQuery(this).css('opacity', '1');
        jQuery(this).parents('[data-type=swapplace]').children('.swapplace-carousel').show();
        jQuery(this).parents('[data-type=swapplace]').children('.swapplace-map').hide();
    });

    // Map on swap-place page
    if(jQuery('.swapplace-single').length) {
        map = new google.maps.Map(jQuery('.swapplace-single').children('.swapplace-map')[0], map_options);
        post = new wp.api.models.Swapplaces({id: jQuery('.swapplace-single').attr('data-id') });
        post.fetch().done( function(response) {
            lat_lng = new google.maps.LatLng(response.acf.lat, response.acf.lng);
            map.setCenter(lat_lng);
            map.setZoom(17);
            var marker = new google.maps.Marker({
                map: map,
                position: lat_lng,
                icon: swapplace_icon
            });
        });
    }
});