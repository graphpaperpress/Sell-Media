<?php

/**
 * Register Theme Settings
 *
 * Register theme options array to hold all theme options.
 *
 * @link    http://codex.wordpress.org/Function_Reference/register_setting  Codex Reference: register_setting()
 *
 * @param   string      $option_group       Unique Settings API identifier; passed to settings_fields() call
 * @param   string      $option_name        Name of the wp_options database table entry
 * @param   callback    $sanitize_callback  Name of the callback function in which user input data are sanitized
 */
register_setting(
    // $option_group
    sell_media_get_current_plugin_id() . '_options',
    // $option_name
    sell_media_get_current_plugin_id() . '_options',
    // $sanitize_callback
    'sell_media_plugin_options_validate'
);


/**
 * Theme register_setting() sanitize callback
 *
 * Validate and whitelist user-input data before updating Theme
 * Options in the database. Only whitelisted options are passed
 * back to the database, and user-input data for all whitelisted
 * options are sanitized.
 *
 * @link    http://codex.wordpress.org/Data_Validation  Codex Reference: Data Validation
 *
 * @param   array   $input  Raw user-input data submitted via the Theme Settings page
 * @return  array   $input  Sanitized user-input data passed to the database
 *
 * @global  array   Settings Page Tab definitions
 *
 */
function sell_media_plugin_options_validate( $input ) {

    global $sell_media_plugin_tabs;

    // This is the "whitelist": current settings
    $valid_input = (array) sell_media_get_plugin_options();
    // Get the array of Theme settings, by Settings Page tab
    $settingsbytab = sell_media_get_plugin_settings_by_tab();
    // Get the array of option parameters
    $option_parameters = sell_media_get_plugin_option_parameters();
    // Get the array of option defaults
    $option_defaults = sell_media_get_plugin_option_defaults();
    // Get list of tabs

    // Determine what type of submit was input
    $submittype = 'submit';
    foreach ( $sell_media_plugin_tabs as $tab ) {
        $resetname = 'reset-' . $tab['name'];
        if ( ! empty( $input[$resetname] ) ) {
            $submittype = 'reset';
        }
    }

    // Determine what tab was input
    $submittab = '';
    foreach ( $sell_media_plugin_tabs as $tab ) {
        $submitname = 'submit-' . $tab['name'];
        $resetname = 'reset-' . $tab['name'];
        if ( ! empty( $input[ $submitname ] ) || ! empty( $input[ $resetname ] ) ) {
            $submittab = $tab['name'];
        }
    }
    // Get settings by tab
    $tabsettings = isset( $settingsbytab[ $submittab ] ) ? $settingsbytab[ $submittab ] : array();
    if( empty( $tabsettings ) )
        return $input;

    // Loop through each tab setting
    foreach ( $tabsettings as $setting ) {

        // If no option is selected, set the default
        $valid_input[ $setting ] = ( ! isset( $input[ $setting ] ) ? $option_defaults[ $setting ] : $input[ $setting ] );

        // If submit, validate/sanitize $input
        if ( 'submit' == $submittype ) {

            // Get the setting details from the defaults array
            $optiondetails = $option_parameters[ $setting ];
            // Get the array of valid options, if applicable
            $valid_options = ( isset( $optiondetails['valid_options'] ) ? $optiondetails['valid_options'] : false );

            // Validate checkbox fields
            if ( 'checkbox' == $optiondetails['type'] ) {
                // If input value is set and is true, return true; otherwise return false
                if( isset( $input[ $setting ] ) && is_array( $input[ $setting ] ) ) :
                    foreach( $input[ $setting ] as $key => $checkbox ) :
                        if( isset( $checkbox ) && 'on' == $checkbox ) {
                            $valid_input[ $setting ][] =  true;
                        }
                    endforeach;
                else:
                    $valid_input[ $setting ] = ( ( isset( $input[ $setting ] ) && true == $input[ $setting ] ) ? true : false );
                endif;
            }
            // Validate radio button fields
            else if ( 'radio' == $optiondetails['type'] ) {
                // Only update setting if input value is in the list of valid options
                $valid_input[ $setting ] = ( array_key_exists( $input[ $setting ], $valid_options ) ? $input[ $setting ] : $valid_input[ $setting ] );
            }
            // Validate select fields
            else if ( 'select' == $optiondetails['type'] ) {
                // Only update setting if input value is in the list of valid options
                $valid_input[ $setting ] = ( array_key_exists( $setting, $valid_options ) ? $input[ $setting ] : $valid_input[ $setting ] );
            }
            // Validate text input and textarea fields
            else if ( ( 'text' == $optiondetails['type'] || 'textarea' == $optiondetails['type'] || 'number' == $optiondetails['type'] ) ) {
                // Validate no-HTML content
                if ( 'nohtml' == $optiondetails['sanitize'] ) {
                    // Pass input data through the wp_filter_nohtml_kses filter
                    $valid_input[ $setting ] = wp_filter_nohtml_kses( $input[ $setting ] );
                }
                // Validate HTML content
                if ( 'html' == $optiondetails['sanitize'] ) {
                    // Pass input data through the wp_filter_kses filter
                    $valid_input[ $setting ] = addslashes( $input[ $setting ] );
                }
                // Validate Slug
                if ( 'slug' == $optiondetails['sanitize'] ) {
                    $valid_input[ $setting ] = sanitize_title( $input[ $setting ] );
                }
            }
        }
        // If reset, reset defaults
        elseif ( 'reset' == $submittype ) {
            // Set $setting to the default value
            $valid_input[ $setting ] = $option_defaults[ $setting ];
        }
    }
    return $valid_input;

}

/**
 * Globalize the variable that holds
 * the Settings Page tab definitions
 *
 * @global  array   Settings Page Tab definitions
 */
global $sell_media_plugin_tabs;

/**
 * Call add_settings_section() for each Settings
 *
 * Loop through each Theme Settings page tab, and add
 * a new section to the Theme Settings page for each
 * section specified for each tab.
 *
 * @link    http://codex.wordpress.org/Function_Reference/add_settings_section  Codex Reference: add_settings_section()
 *
 * @param   string      $sectionid  Unique Settings API identifier; passed to add_settings_field() call
 * @param   string      $title      Title of the Settings page section
 * @param   callback    $callback   Name of the callback function in which section text is output
 * @param   string      $pageid     Name of the Settings page to which to add the section; passed to do_settings_sections()
 */
foreach ( $sell_media_plugin_tabs as $tab ) {
    $tabname = $tab['name'];
    $tabsections = $tab['sections'];
    foreach ( $tabsections as $section ) {

        add_settings_section( 'sell_media_' . $section['name'] . '_section', $section['title'], 'sell_media_plugin_sections_callback', 'sell_media_' . $tabname . '_tab' );

    }
}

/**
 * Callback for add_settings_section()
 *
 * Generic callback to output the section text
 * for each Plugin settings section.
 *
 * @param   array   $section_passed Array passed from add_settings_section()
 */
function sell_media_plugin_sections_callback( $section_passed ) {
    global $sell_media_plugin_tabs;
    foreach ( $sell_media_plugin_tabs as $tabname => $tab ) {
        $tabsections = $tab['sections'];
        foreach ( $tabsections as $sectionname => $section ) {
            if ( 'sell_media_' . $sectionname . '_section' == $section_passed['id'] ) {
                ?>
                <p><?php echo $section['description']; ?></p>
                <?php
            }
        }
    }
}

/**
 * Globalize the variable that holds
 * all the Theme option parameters
 *
 * @global  array   Theme options parameters
 */
global $option_parameters;
$option_parameters = sell_media_get_plugin_option_parameters();

/**
 * Call add_settings_field() for each Setting Field
 *
 * Loop through each Theme option, and add a new
 * setting field to the Theme Settings page for each
 * setting.
 *
 * @link    http://codex.wordpress.org/Function_Reference/add_settings_field    Codex Reference: add_settings_field()
 *
 * @param   string      $settingid  Unique Settings API identifier; passed to the callback function
 * @param   string      $title      Title of the setting field
 * @param   callback    $callback   Name of the callback function in which setting field markup is output
 * @param   string      $pageid     Name of the Settings page to which to add the setting field; passed from add_settings_section()
 * @param   string      $sectionid  ID of the Settings page section to which to add the setting field; passed from add_settings_section()
 * @param   array       $args       Array of arguments to pass to the callback function
 */
foreach ( $option_parameters as $option ) {
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiontab = $option['tab'];
    $optionsection = $option['section'];
    $optiontype = $option['type'];
    add_settings_field(
        // $settingid
        'sell_media_setting_' . $optionname,
        // $title
        $optiontitle,
        // $callback
        'sell_media_plugin_setting_callback',
        // $pageid
        'sell_media_' . $optiontab . '_tab',
        // $sectionid
        'sell_media_' . $optionsection . '_section',
        // $args
        $option
    );
}

/**
 * Callback for get_settings_field()
 */
function sell_media_plugin_setting_callback( $option ) {
    $sell_media_options = (array) sell_media_get_plugin_options();

    $option_parameters = sell_media_get_plugin_option_parameters();
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiondescription = $option['description'];
    $fieldtype = $option['type'];
    $fieldname = sell_media_get_current_plugin_id() . "_options[ { $optionname } ]";

    $attr = $option_parameters[ $option['name'] ];
    $value = $sell_media_options[ $optionname ];

    //Determine the type of input field
    switch ( $fieldtype ) {

        //Render Text Input
        case 'text': sell_media_plugin_field_text( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render Number Input
        case 'number': sell_media_plugin_field_number( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render Range Input
        case 'range': sell_media_plugin_field_range( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render Password Input
        case 'password': sell_media_plugin_field_password( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render textarea options
        case 'textarea': sell_media_plugin_field_textarea( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render select dropdowns
        case 'select': sell_media_plugin_field_select( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render radio dropdowns
        case 'radio': sell_media_plugin_field_radio( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render radio image dropdowns
        case 'radio_image': sell_media_plugin_field_radio_image( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render checkboxes
        case 'checkbox': sell_media_plugin_field_checkbox( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render color picker
        case 'color': sell_media_plugin_field_color( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render uploaded image
        case 'image': sell_media_plugin_field_image( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render uploaded gallery
        case 'gallery': sell_media_plugin_field_gallery( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        //Render uploaded gallery
        case 'html': sell_media_plugin_field_html( $value, $attr );
        echo '<span class="option-description">' . $option['description'] . '</span>';
        break;

        default:
        break;

    }
}