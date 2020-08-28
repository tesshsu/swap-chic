function urlToArray() {
    return window.location.pathname.split("/");
}
function loaderHide() {
    jQuery(".loader").fadeOut(function () {
        jQuery(".loader").remove();
    });
}
function getScope() {
    for (var e = { scope: [], more: [], even_more: [] }, o = window.location.search, t = o.substr(1, o.length).split("&"), a = 0; a < t.length; a++) {
        var n = t[a].substr(0, t[a].indexOf("="));
        (t[a] = t[a].substr(t[a].indexOf("=") + 1, t[a].length)), t[a].indexOf("%2C") >= 0 ? (e[n] = t[a].split("%2C")) : t[a].indexOf(",") >= 0 ? (e[n] = t[a].split(",")) : (e[n] = t[a]);
    }
    return null != e.scope[0] && e;
}
function getLowestScopeLevel(e) {
    return e.scope.indexOf("%2C") >= 0
        ? ((scope_array = e.scope.split("%2C")), scope_array[0].length > 2 ? "ville" : "region")
        : e.scope.indexOf(",") >= 0
        ? ((scope_array = e.scope.split(",")), scope_array[0].length > 2 ? "ville" : "region")
        : Array.isArray(e.scope)
        ? e.scope[0].length > 2
            ? "ville"
            : "region"
        : ((scope_array = e.scope), scope_array.length > 2 ? "ville" : "departement");
}
function reverseGeocode(e, o) {
    return new Promise(function (t, a) {
        o.geocode({ location: e }, function (n, r) {
            "OK" == r
                ? t(n)
                : "OVER_QUERY_LIMIT" == r
                ? setTimeout(function () {
                      return reverseGeocode(e, o);
                  }, 300)
                : a();
        });
    });
}
function replaceScope(e, o = null, t = null, a = null) {
    var n = "",
        r = "",
        s = "",
        p = window.location.origin + window.location.pathname;
    if (Array.isArray(e)) {
        for (var c = 0; c < e.length; c++) n += 0 == c ? "?scope=" + e[c] : "%2C" + e[c];
        p += n;
    } else p += e = "?scope=" + e;
    if (null != o)
        if (Array.isArray(o)) {
            for (c = 0; c < o.length; c++) r += 0 == c ? "&more=" + o[c] : "%2C" + o[c];
            p += r;
        } else p += o = "&more=" + o;
    if (null != t)
        if (Array.isArray(t)) {
            for (c = 0; c < t.length; c++) s += 0 == c ? "&even_more=" + t[c] : "%2C" + t[c];
            p += s;
        } else p += t = "&even_more=" + t;
    null != a && (p += a), window.location.replace(p);
}
function geocodeAddress(e, o) {
    return new Promise(function (t, a) {
        o.geocode({ address: e + " France" }, function (n, r) {
            "OK" == r
                ? t(n)
                : "OVER_QUERY_LIMIT" == r
                ? setTimeout(function () {
                      return geocodeAddress(e, o);
                  }, 300)
                : a(new Error("Could'nt find address"));
        });
    });
}
jQuery(document).ready(function () {
    var e = {},
        o = !1,
        t = getScope(),
        a = !1,
        n = [],
        r = !1,
        s = new google.maps.Geocoder(),
        p = new google.maps.LatLng(-34.397, 150.644),
        c = new google.maps.DirectionsRenderer({ suppressMarkers: !0, polylineOptions: { strokeColor: "#3c3c3c" } }),
        i = new google.maps.DirectionsService(),
        l = { zoom: 14, center: p, disableDefaultUI: !0, mapTypeId: google.maps.MapTypeId.ROADMAP, styles: [{ featureType: "poi", stylers: [{ visibility: "off" }] }] },
        user_marker = { url: window.location.origin + "/wp-content/themes/Swap-Chic/assets/images/user_marker.png" },
        sp_marker = { url: window.location.origin + "/wp-content/themes/Swap-Chic/assets/images/swapplace_marker.png" },
        u = [],
        g = [];
    if (!((0 != t && "" != t.scope[0]) || ("/actualites/" != window.location.pathname && "/catalogue/" != window.location.pathname && "/messagerie/nouvelle-discussion/" != window.location.pathname))) {
        var f = "&" + window.location.search.substr(1, window.location.search.length - 1);
        if (navigator.geolocation) {
            var h = setTimeout(y, 1e4);
            function y() {
                clearTimeout(h),
                    1 != Cookies.get("alert_geolocation") &&
                        "actualites" == window.location.pathname.split("/")[1] &&
                        (alert(
                            "Chère membre,\n Tu as refusé de partager tes données de géolocalisation avec Swap-Chic, si dans le futur tu change d'avis, rends toi dans les options de ton navigateur ou dans les paramètres de ton appareil !\n Bon shopping 😉"
                        ),
                        Cookies.set("alert_geolocation", 1, { expires: 36500 })),
                    (t = []),
                    (n = []),
                    (even_more = []),
                    jQuery
                        .getJSON("https://geo.api.gouv.fr/communes?codePostal=" + jQuery("[data-basescope]").attr("data-basescope") + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                        .then(function (e) {
                            for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                            (n = e[0].codeDepartement),
                                jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                    for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                    replaceScope(t, n, even_more, f);
                                });
                        });
            }
            navigator.geolocation.getCurrentPosition(
                function (e) {
                    clearTimeout(h),
                        reverseGeocode({ lat: e.coords.latitude, lng: e.coords.longitude }, s).then(
                            function (e) {
                                (t = []), (n = []), (even_more = []);
                                for (var o = 0; o < e.length; o++)
                                    "postal_code" == e[o].types &&
                                        jQuery
                                            .getJSON("https://geo.api.gouv.fr/communes?codePostal=" + e[o].address_components[0].long_name + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                                            .then(function (e) {
                                                for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                                                (n = e[0].codeDepartement),
                                                    jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                        for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                        replaceScope(t, n, even_more, f);
                                                    });
                                            });
                            },
                            function () {
                                (t = []),
                                    (n = []),
                                    (even_more = []),
                                    jQuery
                                        .getJSON(
                                            "https://geo.api.gouv.fr/communes?codePostal=" +
                                                jQuery("[data-basescope]").attr("data-basescope") +
                                                "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre"
                                        )
                                        .then(function (e) {
                                            for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                                            (n = e[0].codeDepartement),
                                                jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                    for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                    replaceScope(t, n, even_more);
                                                });
                                        });
                            }
                        );
                },
                y,
                { timeout: 5e3 }
            );
        } else
            (t = []),
                (n = []),
                (even_more = []),
                jQuery
                    .getJSON("https://geo.api.gouv.fr/communes?codePostal=" + jQuery("[data-basescope]").attr("data-basescope") + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                    .then(function (e) {
                        for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                        (n = e[0].codeDepartement),
                            jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                replaceScope(t, n, even_more, f);
                            });
                    });
    }
    if (0 != t) {
        if (((a = getLowestScopeLevel(t)), Array.isArray(t.scope))) var v = t.scope[0];
        else v = t.scope;
        if (
            (jQuery(".scope-toggle").length
                ? "31000" == v
                    ? (jQuery(".scope").html("Toulouse"), clearTimeout(loader_timeout), loaderHide())
                    : "Nantes" == v || "44100" == v
                    ? (jQuery(".scope").html("Nantes"), clearTimeout(loader_timeout), loaderHide())
                    : s.geocode({ address: v + " France" }, function (e, o) {
                          "OK" == o ? ("departement" != a ? jQuery(".scope").html(e[0].address_components[1].short_name) : jQuery(".scope").html(e[0].address_components[0].short_name)) : (console.log(v), console.log(o)),
                              clearTimeout(loader_timeout),
                              loaderHide(),
                              1 == Cookies.get("hide-helps") ? jQuery("html, body").css("overflow", "visible") : jQuery("html, body").scrollTop(0);
                      })
                : (clearTimeout(loader_timeout), loaderHide()),
            jQuery("#scope-modal input[type=submit]").click(function (e) {
                e.preventDefault(),
                    geocodeAddress(jQuery("#scope-modal input[name=scope]").val(), s).then(
                        function (e) {
                            "administrative_area_level_1" == e[0].address_components[0].types[0]
                                ? jQuery.getJSON("https://geo.api.gouv.fr/regions?nom=" + e[0].address_components[0].long_name + "&fields=nom,code").then(function (e) {
                                      jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].code + "/departements?fields=nom,code").then(function (e) {
                                          t = [];
                                          for (var o = 0; o < e.length; o++) t.push(e[o].code);
                                          replaceScope(t);
                                      });
                                  })
                                : "administrative_area_level_2" == e[0].address_components[0].types[0]
                                ? jQuery.getJSON("https://geo.api.gouv.fr/departements?nom=" + e[0].address_components[0].long_name + "&fields=nom,code,codeRegion").then(function (e) {
                                      (t = e[0].code),
                                          jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                              n = [];
                                              for (var o = 0; o < e.length; o++) e[o].code != t && n.push(e[o].code);
                                              replaceScope(t, n);
                                          });
                                  })
                                : "locality" == e[0].address_components[0].types[0]
                                ? jQuery
                                      .getJSON("https://geo.api.gouv.fr/communes?nom=" + e[0].address_components[0].long_name + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                                      .then(function (o) {
                                          for (var a = 0; a < o.length; a++)
                                              o[a].nom == e[0].address_components[0].long_name &&
                                                  ((t = []),
                                                  (n = []),
                                                  (even_more = []),
                                                  t.push(o[a].codesPostaux),
                                                  (n = o[a].codeDepartement),
                                                  jQuery.getJSON("https://geo.api.gouv.fr/regions/" + o[a].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                      for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                      replaceScope(t, n, even_more);
                                                  }));
                                      })
                                : "postal_code" == e[0].address_components[0].types[0]
                                ? ((t = []),
                                  jQuery
                                      .getJSON("https://geo.api.gouv.fr/communes?codePostal=" + e[0].address_components[0].long_name + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                                      .then(function (e) {
                                          for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                                          (n = e[0].codeDepartement),
                                              (even_more = []),
                                              jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                  for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                  replaceScope(t, n, even_more);
                                              });
                                      }))
                                : "locality" == e[0].address_components[1].types[0]
                                ? jQuery
                                      .getJSON("https://geo.api.gouv.fr/communes?nom=" + e[0].address_components[1].long_name + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                                      .then(function (o) {
                                          for (var a = 0; a < o.length; a++)
                                              o[a].nom == e[0].address_components[1].long_name &&
                                                  ((t = []),
                                                  (n = []),
                                                  (even_more = []),
                                                  t.push(o[a].codesPostaux),
                                                  (n = o[a].codeDepartement),
                                                  jQuery.getJSON("https://geo.api.gouv.fr/regions/" + o[a].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                      for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                      replaceScope(t, n, even_more);
                                                  }));
                                      })
                                : (jQuery("#scope-modal form").prepend('<p class="error">Valeur invalide...</p>'), jQuery("#scope-modal input[type=text]").css("border", "1px solid red"));
                        },
                        function () {
                            jQuery("#scope-modal form").prepend('<p class="error">Valeur invalide...</p>'), jQuery("#scope-modal input[type=text]").css("border", "1px solid red");
                        }
                    );
            }),
            jQuery("#scope-modal #geolocalisation").click(function (e) {
                navigator.geolocation
                    ? navigator.geolocation.getCurrentPosition(
                          function (e) {
                              reverseGeocode({ lat: (r = e).coords.latitude, lng: r.coords.longitude }, s).then(function (e) {
                                  (t = []), (n = []), (even_more = []);
                                  for (var o = 0; o < e.length; o++)
                                      "postal_code" == e[o].types &&
                                          jQuery
                                              .getJSON(
                                                  "https://geo.api.gouv.fr/communes?codePostal=" + e[o].address_components[0].long_name + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre"
                                              )
                                              .then(function (e) {
                                                  for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                                                  (n = e[0].codeDepartement),
                                                      jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                                          for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                                          replaceScope(t, n, even_more);
                                                      });
                                              });
                              });
                          },
                          function () {
                              alert("Swap-Chic n'a pas l'autorisation d'utiliser ta geolocalistaion.\nTu peux modifier cet option dans les paramètres de ton navigateur ou de ton appareil.");
                          }
                      )
                    : (alert("Swap-Chic n'a pas l'autorisation d'utiliser ta geolocalistaion.\nTu peux modifier cet option dans les paramètres de ton navigateur ou de ton appareil."),
                      (t = []),
                      (n = []),
                      (even_more = []),
                      jQuery
                          .getJSON("https://geo.api.gouv.fr/communes?codePostal=" + jQuery("[data-basescope]").attr("data-basescope") + "&fields=nom,code,codesPostaux,codeDepartement,codeRegion,population&format=json&geometry=centre")
                          .then(function (e) {
                              for (var o = 0; o < e[0].codesPostaux.length; o++) t.push(e[0].codesPostaux[o]);
                              (n = e[0].codeDepartement),
                                  jQuery.getJSON("https://geo.api.gouv.fr/regions/" + e[0].codeRegion + "/departements?fields=nom,code").then(function (e) {
                                      for (var o = 0; o < e.length; o++) e[o].code != n && even_more.push(e[o].code);
                                  });
                          }));
            }),
            jQuery(".map").length)
        )
            if (
                (jQuery("#map-scope").length && (e["#map-scope"] = new google.maps.Map(document.getElementById("map-scope"), l)),
                jQuery("#map-more").length && (e["#map-more"] = new google.maps.Map(document.getElementById("map-more"), l)),
                jQuery("#map-even_more").length && (e["#map-even_more"] = new google.maps.Map(document.getElementById("map-even_more"), l)),
                navigator.geolocation)
            )
                navigator.geolocation.getCurrentPosition(
                    function (t) {
                        reverseGeocode({ lat: t.coords.latitude, lng: t.coords.longitude }, s).then(function (a) {
                            (o = new google.maps.LatLng(t.coords.latitude, t.coords.longitude)),
                                jQuery("#map-scope").length &&
                                    ((u.scope = new google.maps.Marker({ map: e["#map-scope"], position: o, icon: d })),
                                    e["#map-scope"].setCenter(o),
                                    "ville" == jQuery("#map-scope").parent().attr("data-level")
                                        ? e["#map-scope"].setZoom(15)
                                        : "departement" == jQuery("#map-scope").parent().attr("data-level")
                                        ? e["#map-scope"].setZoom(8)
                                        : e["#map-scope"].setZoom(6)),
                                jQuery("#map-more").length &&
                                    ((u.more = new google.maps.Marker({ map: e["#map-more"], position: o, icon: d })),
                                    e["#map-more"].setCenter(o),
                                    "departement" == jQuery("#map-more").parent().attr("data-level") ? e["#map-more"].setZoom(8) : e["#map-more"].setZoom(6)),
                                jQuery("#map-even_more").length && ((u.even_more = new google.maps.Marker({ map: e["#map-even_more"], position: o, icon: d })), e["#map-even_more"].setCenter(o), e["#map-even_more"].setZoom(6));
                        });
                    },
                    function () {
                        if ((jQuery(".map-draw").hide(), jQuery(".map-reset").hide(), jQuery(".map-locate").hide(), Array.isArray(t.scope))) var o = t.scope[0];
                        else o = t.scope;
                        if (Array.isArray(t.more)) var a = t.more[0];
                        else a = t.more;
                        var n = t.even_more[0];
                        jQuery("#map-scope").length &&
                            ("ville" == jQuery("#map-scope").parent().attr("data-level")
                                ? jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal=" + o + "&fields=nom,code,codesPostaux").then(function (o) {
                                      geocodeAddress(o[0].nom, s).then(function (o) {
                                          (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(13);
                                      });
                                  })
                                : "departement" == jQuery("#map-scope").parent().attr("data-level")
                                ? geocodeAddress(o, s).then(function (o) {
                                      (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(8);
                                  })
                                : geocodeAddress(o, s).then(function (o) {
                                      (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(6);
                                  })),
                            jQuery("#map-more").length &&
                                ("departement" == jQuery("#map-more").parent().attr("data-level")
                                    ? geocodeAddress(a, s).then(function (o) {
                                          (p = o[0].geometry.location), e["#map-more"].setCenter(p), e["#map-more"].setZoom(8);
                                      })
                                    : geocodeAddress(a, s).then(function (o) {
                                          (p = o[0].geometry.location), e["#map-more"].setCenter(p), e["#map-more"].setZoom(6);
                                      })),
                            jQuery("#map-even_more").length &&
                                geocodeAddress(n, s).then(function (o) {
                                    (p = o[0].geometry.location), e["#map-even_more"].setCenter(p), e["#map-even_more"].setZoom(6);
                                });
                    }
                );
            else {
                if ((jQuery(".map-draw").hide(), jQuery(".map-reset").hide(), jQuery(".map-locate").hide(), Array.isArray(t.scope))) var j = t.scope[0];
                else j = t.scope;
                if (Array.isArray(t.more)) var w = t.more[0];
                else w = t.more;
                var Q = t.even_more[0];
                jQuery("#map-scope").length &&
                    ("ville" == jQuery("#map-scope").parent().attr("data-level")
                        ? jQuery.getJSON("https://geo.api.gouv.fr/communes?codePostal=" + j + "&fields=nom,code,codesPostaux").then(function (o) {
                              geocodeAddress(o[0].nom, s).then(function (o) {
                                  (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(13);
                              });
                          })
                        : "departement" == jQuery("#map-scope").parent().attr("data-level")
                        ? geocodeAddress(j, s).then(function (o) {
                              (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(8);
                          })
                        : geocodeAddress(j, s).then(function (o) {
                              (p = o[0].geometry.location), e["#map-scope"].setCenter(p), e["#map-scope"].setZoom(6);
                          })),
                    jQuery("#map-more").length &&
                        ("departement" == jQuery("#map-more").parent().attr("data-level")
                            ? geocodeAddress(w, s).then(function (o) {
                                  (p = o[0].geometry.location), e["#map-more"].setCenter(p), e["#map-more"].setZoom(8);
                              })
                            : geocodeAddress(w, s).then(function (o) {
                                  (p = o[0].geometry.location), e["#map-more"].setCenter(p), e["#map-more"].setZoom(6);
                              })),
                    jQuery("#map-even_more").length &&
                        geocodeAddress(Q, s).then(function (o) {
                            (p = o[0].geometry.location), e["#map-even_more"].setCenter(p), e["#map-even_more"].setZoom(6);
                        });
            }
        jQuery(".map .swapplaces-caroussel .slick-slide:not(.slick-cloned)").each(function () {
            var t = jQuery(this).parents(".map").children(".map-iframe").attr("id"),
                a = jQuery(this).attr("data-slick-index"),
                n = jQuery(this).attr("data-id");
            new wp.api.models.Swapplaces({ id: n }).fetch().done(function (n) {
                p = new google.maps.LatLng(n.acf.lat, n.acf.lng);
                var r = new google.maps.Marker({ map: e["#" + t], position: p, icon: m });
                g.push([t, n.id, a, r]),
                    google.maps.event.addListener(r, "click", function () {
                        for (var e = 0; e < g.length; e++)
                            if (g[e][3] == r) {
                                0 != o &&
                                    (jQuery("#" + g[e][0])
                                        .parent()
                                        .children(".map-draw")
                                        .hide(),
                                    jQuery("#" + g[e][0])
                                        .parent()
                                        .children(".map-reset")
                                        .show()),
                                    jQuery("#" + g[e][0])
                                        .parent()
                                        .find(".swapplaces-caroussel")
                                        .slick("slickGoTo", g[e][2]);
                                var t = jQuery("#" + g[e][0])
                                    .parent()
                                    .offset().top;
                                jQuery("html, body").animate({ scrollTop: t }, 300);
                            }
                    });
            });
        }),
            jQuery(".map .swapplaces-caroussel").on("beforeChange", function (t, a, n, r) {
                var s = jQuery(this).parents(".map").children(".map-iframe").attr("id");
                0 != o &&
                    (jQuery("#" + s)
                        .parent()
                        .children(".map-reset")
                        .hide(),
                    jQuery("#" + s)
                        .parent()
                        .children(".map-draw")
                        .show());
                for (var i = 0; i < g.length; i++) g[i][0] == s && g[i][3].setMap(e["#" + s]);
                jQuery(this)
                    .find(".slick-slide")
                    .each(function () {
                        if (jQuery(this).attr("data-slick-index") == r) {
                            c.setMap(null);
                            var o = jQuery(this).attr("data-id");
                            (post = new wp.api.models.Swapplaces({ id: o })),
                                post.fetch().done(function (o) {
                                    (p = new google.maps.LatLng(o.acf.lat, o.acf.lng)), e["#" + s].setCenter(p), e["#" + s].setZoom(17);
                                });
                        }
                    });
            }),
            jQuery(".map .swapplaces-caroussel-infos .slick-slide .href").click(function () {
                window.location.assign("https://" + window.location.host + "/swap-places/" + jQuery(this).parents("[data-slug]").attr("data-slug"));
            }),
            jQuery(".map-draw").click(function () {
                var t = jQuery(this).parents(".map").children(".map-iframe").attr("id");
                c.setMap(null);
                for (var a = jQuery(this).siblings(".map-bottom").children(".swapplaces-caroussel").find(".slick-current").attr("data-id"), n = 0; n < g.length; n++)
                    a == g[n][1] && g[n][0] == t ? g[n][3].setMap(e["#" + t]) : a != g[n][1] && g[n][0] == t && g[n][3].setMap(null);
                (post = new wp.api.models.Swapplaces({ id: a })),
                    post.fetch().done(function (a) {
                        if (((p = new google.maps.LatLng(a.acf.lat, a.acf.lng)), e["#" + t].setCenter(p), e["#" + t].setZoom(17), 0 != o)) {
                            var n = { origin: o, destination: p, travelMode: google.maps.TravelMode.DRIVING };
                            i.route(n, function (o, a) {
                                a == google.maps.DirectionsStatus.OK ? (c.setDirections(o), c.setMap(e["#" + t])) : alert("Trajet a échoué : " + a);
                            }),
                                c.setMap(e["#" + t]);
                        }
                    }),
                    jQuery(".map-draw").hide(),
                    jQuery(".map-reset").show();
            }),
            jQuery(".map-reset").click(function () {
                var o = jQuery(this).parents(".map").children(".map-iframe").attr("id"),
                    t = jQuery(this).siblings(".map-bottom").children(".swapplaces-caroussel").find(".slick-current").attr("data-id");
                (post = new wp.api.models.Swapplaces({ id: t })),
                    post.fetch().done(function (t) {
                        (p = new google.maps.LatLng(t.acf.lat, t.acf.lng)), e["#" + o].setCenter(p), e["#" + o].setZoom(15);
                    }),
                    c.setMap(null);
                for (var a = 0; a < g.length; a++) g[a][0] == o && g[a][3].setMap(e["#" + o]);
                jQuery(".map-reset").hide(), jQuery(".map-draw").show();
            }),
            jQuery(".map-locate").click(function () {
                var t = jQuery(this).parents(".map").children(".map-iframe").attr("id");
                e["#" + t].setCenter(o), e["#" + t].setZoom(15), c.setMap(null);
                for (var a = 0; a < g.length; a++) g[a][0] == t && g[a][3].setMap(e["#" + t]);
                jQuery(".map-reset").hide(), jQuery(".map-draw").show();
            });
    }
    jQuery("[data-type=swapplace] .map-toggle").click(function () {
        var t = this,
            a = jQuery(t).parents("[data-id]").attr("data-id");
        jQuery(t).siblings(".picture-toggle").css("opacity", ".5"),
            jQuery(t).css("opacity", "1"),
            jQuery(t).parents("[data-type=swapplace]").children(".swapplace-carousel").hide(),
            jQuery(t).parents("[data-type=swapplace]").children(".swapplace-map").show(),
            (post = new wp.api.models.Swapplaces({ id: a })),
            post.fetch().done(function (n) {
                (e[a] = new google.maps.Map(jQuery(t).parents(".infos-wrapper").siblings(".swapplace-map")[0], l)), (p = new google.maps.LatLng(n.acf.lat, n.acf.lng)), e[a].setCenter(p), e[a].setZoom(17);
                new google.maps.Marker({ map: e[a], position: p, icon: sp_marker });
                if (0 != o) {
                    new google.maps.Marker({ map: e[a], position: o, icon: user_marker });
                    var r = { origin: o, destination: p, travelMode: google.maps.TravelMode.DRIVING };
                    i.route(r, function (o, t) {
                        t == google.maps.DirectionsStatus.OK && (c.setDirections(o), c.setMap(e[a]));
                    }),
                        c.setMap(e[a]);
                }
            });
    }),
        jQuery("[data-type=swapplace] .picture-toggle").click(function () {
            jQuery(this).siblings(".map-toggle").css("opacity", ".5"),
                jQuery(this).css("opacity", "1"),
                jQuery(this).parents("[data-type=swapplace]").children(".swapplace-carousel").show(),
                jQuery(this).parents("[data-type=swapplace]").children(".swapplace-map").hide();
        }),
        jQuery(".swapplace-single").length &&
            ((map = new google.maps.Map(jQuery(".swapplace-single").children(".swapplace-map")[0], l)),
            (post = new wp.api.models.Swapplaces({ id: jQuery(".swapplace-single").attr("data-id") })),
            post.fetch().done(function (e) {
                (p = new google.maps.LatLng(e.acf.lat, e.acf.lng)), map.setCenter(p), map.setZoom(17);
                new google.maps.Marker({ map: map, position: p, icon: m });
            }));
});
