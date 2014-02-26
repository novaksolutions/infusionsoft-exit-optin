<?php

/*
Plugin Name: Infusionsoft Exit Optin
Plugin URI: http://novaksolutions.com/wordpress-plugins/infusionsoft-exit-optin/
Description: Trigger a web form pop-up when the user's mouse leaves the page.
Author: Novak Solutions
Version: 1.0.4
Author URI: http://novaksolutions.com/
*/

/**
 * Check if the SDK is loaded
 */
function novaksolutions_exit_missing_sdk() {
    if( !is_plugin_active( 'infusionsoft-sdk/infusionsoft-sdk.php' )){
        echo "<div class=\"error\"><p><strong><em>Infusionsoft Exit Optin</em> requires the <em>Infusionsoft SDK</em> plugin. Please install and activate the <em>Infusionsoft SDK</em> plugin.</strong></p></div>";
    }
}
add_action( 'admin_notices', 'novaksolutions_exit_missing_sdk' );

/**
 * Determine the current user's WP role.
 *
 * @global object $current_user
 * @return int
 */
function novaksolutions_exit_get_user_role() {
    global $current_user;

    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    $user_role = ucwords($user_role);

    $roles = array(
        'Everyone' => 1,
        'Subscriber' => 2,
        'Contributor' => 3,
        'Author' => 4,
        'Editor' => 5,
        'Administrator' => 6,
    );

    if(isset($roles[$user_role])){
        return $roles[$user_role];
    }else{
        return 1;
    }
}

// Include admin functionality
include(dirname(__FILE__).'/admin_init.php');

/**
 * Register and enqueue required JS and CSS files.
 */
function novaksolutions_exit_scripts() {
    // CSS
    wp_register_style( 'magnific-popup', plugins_url('css/magnific-popup.css', __FILE__) );

    wp_enqueue_style( 'magnific-popup' );

    // JS
    wp_register_script( 'magnific-popup', plugins_url('js/magnific-popup.js', __FILE__), array('jquery') );

    wp_enqueue_script( 'magnific-popup' );
}
add_action( 'wp_enqueue_scripts', 'novaksolutions_exit_scripts' );

/**
 * Output the popup code to the footer of the page.
 */
function novaksolutions_exit_plugin_footer() {
    // Don't spit out optin form code if they haven't selected a web form
    if(!get_option('novaksolutions_exit_web_form_snippet')) return;
    if(get_option('novaksolutions_exit_role', '1') > novaksolutions_exit_get_user_role()) return;

    ?>
<style>
.mfp-iframe, .mfp-iframe-holder .mfp-content {
    width: <?php echo get_option('novaksolutions_exit_width', '500'); ?>px !important;
    height: <?php echo get_option('novaksolutions_exit_height', '300'); ?>px !important;
}
</style>
<script>
jQuery(document).ready(function() {
    novaksolutionsExit = {};
    novaksolutionsExit.createCookie = function(name, value, days) {
        var expires;

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        } else {
            expires = "";
        }
        document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
    }

    novaksolutionsExit.readCookie = function(name) {
        var nameEQ = escape(name) + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    novaksolutionsExit.eraseCookie = function(name) {
        novaksolutionsExit.createCookie(name, "0", -1);
    }

    novaksolutionsExit.increment = function() {
        var current = novaksolutionsExit.readCookie('ns_exit');
        if(isNaN(current) || current == 'undefined' || current == null) current = 0;

        return parseInt(current) + 1;
    }

    novaksolutionsExit.shown = false;

    jQuery(document).on("mouseleave", function(){
        if(novaksolutionsExit.shown == false && novaksolutionsExit.readCookie('ns_exit') < <?php echo get_option('novaksolutions_exit_max_views', '1'); ?>) {
            jQuery.magnificPopup.open({
                items: {
                    src: '<?php echo get_option('novaksolutions_exit_web_form_snippet'); ?>',
                    type: 'iframe'
                }
            });

            novaksolutionsExit.createCookie('ns_exit', novaksolutionsExit.increment(), 30);
            novaksolutionsExit.shown = true;
        }
    });
});
</script>
    <?php
}
add_action('wp_footer', 'novaksolutions_exit_plugin_footer');
