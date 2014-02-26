<?php

global $novaksolutions_exit_webforms;

/**
 * Add links to plugin listing.
 *
 * @param array $links
 * @param string $file
 * @return array
 */
function novaksolutions_exit_plugin_action_links( $links, $file ) {
    if ( $file == plugin_basename( dirname(__FILE__).'/infusionsoft-exit-optin.php' ) ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=novaksolutions_exit_admin_menu' ) . '">'.__( 'Settings' ).'</a>';
        $links[] = '<a href="http://novaksolutions.com/integrations/wordpress/?utm_source=wordpress&utm_medium=link&utm_content=exit-optin&utm_campaign=more-plugins">More Plugins by Novak Solutions</a>';
    }

    return $links;
}
add_filter('plugin_action_links', 'novaksolutions_exit_plugin_action_links', 10, 2);

/**
 * Define settings fields.
 */
function novaksolutions_exit_admin_init(){
    add_option('novaksolutions_exit_web_form_snippet', '', null, 'no');

    // Add the section to reading settings so we can add our
    // fields to it
    add_settings_section('novaksolutions_exit_setting_section_popup',
        'Pop-up Options',
        null,
        'novaksolutions-exit-settings');

    // Add the field with the names and function to use for our new
    // settings, put it in our new section
    add_settings_field('novaksolutions_exit_web_form',
        'Web Form',
        'novaksolutions_exit_callback_function_web_form',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');

    add_settings_field('novaksolutions_exit_web_form_snippet',
        'Hosted URL',
        'novaksolutions_exit_callback_function_web_form_snippet',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');

    add_settings_field('novaksolutions_exit_role',
        'Minimum Role',
        'novaksolutions_exit_callback_function_role',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');

    add_settings_field('novaksolutions_exit_width',
        'Width',
        'novaksolutions_exit_callback_function_width',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');

    add_settings_field('novaksolutions_exit_height',
        'Height',
        'novaksolutions_exit_callback_function_height',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');

    add_settings_field('novaksolutions_exit_max_views',
        'Max Views',
        'novaksolutions_exit_callback_function_max_views',
        'novaksolutions-exit-settings',
        'novaksolutions_exit_setting_section_popup');


    // Register our setting so that $_POST handling is done for us and
    // our callback function just has to echo the <input>
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_web_form');
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_web_form_snippet');
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_role');
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_width', 'novaksolutions_exit_sanitize_absint');
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_height', 'novaksolutions_exit_sanitize_absint');
    register_setting('novaksolutions-exit-settings', 'novaksolutions_exit_max_views', 'novaksolutions_exit_sanitize_absint');
}
add_action('admin_init', 'novaksolutions_exit_admin_init');

/**
 * Make sure the value is a positive integer.
 *
 * @param string $value
 * @return int
 */
function novaksolutions_exit_sanitize_absint($value) {
    $value = absint($value);
    return $value === 0 ? '' : $value;
}

/**
 * Display web form dropdown.
 */
function novaksolutions_exit_callback_function_web_form() {
    global $novaksolutions_exit_webforms;

    echo '<select name="novaksolutions_exit_web_form">';
    echo "<option></option>";

    foreach($novaksolutions_exit_webforms as $id => $name)
    {
        $value = array(
            'id' => $id,
            'name' => $name,
        );
        $value = htmlspecialchars(serialize($value));

        $current = htmlspecialchars_decode(get_option('novaksolutions_exit_web_form'));
        $current = str_replace("\r\n", "\n", $current);
        $current = unserialize($current);

        echo "<option value=\"$value\"";

        if(isset($current['id']) && $id == $current['id']){
            echo ' selected="selected"';
        }

        echo ">" . htmlspecialchars($name) . "</option>";
    }

    echo '</select>';
}

/**
 * Display web form URL.
 */
function novaksolutions_exit_callback_function_web_form_snippet() {
    $snippet = get_option('novaksolutions_exit_web_form_snippet');
    if(!empty($snippet)){
        echo htmlspecialchars($snippet) . '<br />';
        echo '<span class="description">This URL will automatically be updated when you click Save Changes.</span>';
    }else{
        echo 'Please select a web form and click <em>Save Changes</em>.';
    }
}

/**
 * Display minimum role field.
 */
function novaksolutions_exit_callback_function_role() {
    global $novaksolutions_exit_webforms;

    $roles = array(
        'Everyone' => 1,
        'Subscriber' => 2,
        'Contributor' => 3,
        'Author' => 4,
        'Editor' => 5,
        'Administrator' => 6,
    );

    echo '<select name="novaksolutions_exit_role">';
    foreach($roles as $role => $id){
        echo "<option value=\"$id\"";
        if($id == get_option('novaksolutions_exit_role', '1')) echo " selected=\"selected\"";
        echo ">$role</option>";
    }

    echo '</select><br />';
    echo '<span class="description">The Exit Optin will only be shown to visitors that are assigned the minimum role or greater. We recommend setting this to Administrator while testing.</span>';
}

/**
 * Display width field.
 */
function novaksolutions_exit_callback_function_width() {
    echo '<input type="text" name="novaksolutions_exit_width" value="' . get_option('novaksolutions_exit_width', '500') . '" />px';
}

/**
 * Display height field.
 */
function novaksolutions_exit_callback_function_height() {
    echo '<input type="text" name="novaksolutions_exit_height" value="' . get_option('novaksolutions_exit_height', '300') . '" />px';
}

/**
 * Display max view field.
 */
function novaksolutions_exit_callback_function_max_views() {
    echo '<input type="text" name="novaksolutions_exit_max_views" value="' . get_option('novaksolutions_exit_max_views', '1') . '" /><br />';
    echo '<span class="description">How many times should each visitor be allowed to see the Exit Optin pop-up.</span>';
}

/**
 * Add settings page to menu.
 */
function novaksolutions_exit_add_admin_menu(){
    add_menu_page( "Infusionsoft Exit Optin", "Exit Optin", "edit_plugins", "novaksolutions_exit_admin_menu", 'novaksolutions_exit_display_admin_page');
    add_submenu_page( "novaksolutions_exit_admin_menu", "Infusionsoft Exit Optin", "Settings", "edit_plugins", "novaksolutions_exit_admin_menu", 'novaksolutions_exit_display_admin_page');
}
add_action('admin_menu', 'novaksolutions_exit_add_admin_menu');

/**
 * Get list of web forms from Infusionsoft.
 *
 * @return array
 */
function novaksolutions_exit_get_webforms(){

    if( !is_plugin_active( 'infusionsoft-sdk/infusionsoft-sdk.php' )){
        $products = array();
        return $products;
    }

    $enough_to_go = false;
    $valid_key = true;
    if(get_option('infusionsoft_sdk_app_name') != '' && get_option('infusionsoft_sdk_api_key') != ''){
        $enough_to_go = true;

        try{
            Infusionsoft_AppPool::addApp(new Infusionsoft_App(get_option('infusionsoft_sdk_app_name') . '.infusionsoft.com', get_option('infusionsoft_sdk_api_key')));
            $webforms = Infusionsoft_WebFormService::getMap();
        } catch(Infusionsoft_Exception $e) {
            $enough_to_go = false;

            if($e == '[InvalidKey]Invalid Key') {
                $valid_key = false;
                add_settings_error("infusionsoft_sdk_api_key", "infusionsoft_sdk_api_key", "The API key you entered is invalid.", "error");
            }
        }
    } else {
        $webforms = array();

        if(!get_option('infusionsoft_sdk_app_name')){
            add_settings_error("infusionsoft_sdk_app_name", "infusionsoft_sdk_app_name", "Please enter your Infusionsoft app name.", "error");
        }

        if(!get_option('infusionsoft_sdk_api_key')){
            $valid_key = false;
            add_settings_error("infusionsoft_sdk_api_key", "infusionsoft_sdk_api_key", "Please enter your Infusionsoft API key.", "error");
        }
    }

    if($enough_to_go && Infusionsoft_DataService::ping()){
        try{
            Infusionsoft_DataService::findByField(new Infusionsoft_Contact(), 'Id', -1);
        } catch(Exception $e){
            add_settings_error("infusionsoft_sdk_api_key", "infusionsoft_sdk_api_key", "The API key you entered is invalid.", "error");
        }
    } else {
        if($valid_key){
            add_settings_error("infusionsoft_sdk_app_name", "infusionsoft_sdk_app_name", "The app name you entered is invalid.", "error");
        }
    }

    if(!empty($webforms)){
        $webform = htmlspecialchars_decode(get_option('novaksolutions_exit_web_form'));
        $webform = str_replace("\r\n", "\n", $webform);
        $webform = unserialize($webform);
        if(isset($webform['id'])){
            try {
                $js = Infusionsoft_WebFormService::getHostedURL($webform['id']);
                update_option( 'novaksolutions_exit_web_form_snippet', $js );
            } catch(Exception $e) {
                update_option( 'novaksolutions_exit_web_form', '' );
                update_option( 'novaksolutions_exit_web_form_snippet', '' );
            }
        }
    }

    return $webforms;
}

/**
 * Display links to WP.org and Novak Solutions.
 */
function novaksolutions_exit_display_link_back(){
    echo '<h2>Like this plugin?</h2>';
    echo '<p>If you found this plugin useful, please <a href="http://wordpress.org/support/view/plugin-reviews/infusionsoft-exit-optin">rate it in the plugin directory</a>.</p>';
    echo '<p>Visit <a href="http://novaksolutions.com/?utm_source=wordpress&utm_medium=link&utm_campaign=exit">Novak Solutions</a> to find dozens of free tips, tricks, and tools to help you get the most out of Infusionsoft®.</p>';
}

/**
 * Display settings page and link back.
 */
function novaksolutions_exit_display_admin_page(){
    global $novaksolutions_exit_webforms;

    echo '<h2>Infusionsoft Exit Optin Settings</h2>';

    $novaksolutions_exit_webforms = novaksolutions_exit_get_webforms();
    settings_errors();

    echo '<form method="POST" action="options.php">';
    settings_fields('novaksolutions-exit-settings');   //pass slug name of page
    do_settings_sections('novaksolutions-exit-settings');    //pass slug name of page
    submit_button();
    echo '</form>';

    novaksolutions_exit_display_link_back();
}
