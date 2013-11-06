<?php
/**
 * Define the Tabs appearing on the Theme Options page
 * Tabs contains sections
 * Options are assigned to both Tabs and Sections
 * See README.md for a full list of option types
 */

$logo = get_template_directory_uri() . "/images/logo.png";

$general_settings_tab = array(
    "name" => "general_plugin_tab",
    "title" => __( "Plugin General", "sell_media" ),
    "sections" => array(
        "general_plugin_section_1" => array(
            "name" => "general_plugin_section_1",
            "title" => __( "General", "sell_media" ),
            "description" => __( "", "sell_media" )
        )
    )
);

sell_media_register_plugin_option_tab( $general_settings_tab );

$colors_plugin_tab = array(
    "name" => "colors_plugin_tab",
    "title" => __( "Plugin Colors", "sell_media" ),
    "sections" => array(
        "colors_plugin_section_1" => array(
            "name" => "colors_plugin_section_1",
            "title" => __( "Colors", "sell_media" ),
            "description" => __( "", "sell_media" )
        )
    )
);

sell_media_register_plugin_option_tab( $colors_plugin_tab );

 /**
 * The following example shows you how to register theme options and assign them to tabs and sections:
*/
$options = array(
    'chromatic_logo' => array(
        "tab" => "general_plugin_tab",
        "name" => "chromatic_logo",
        "title" => "Logo",
        "description" => __( "Use a transparent png or jpg image", "sell_media" ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "image",
        "default" => $logo
    ),

    'chromatic_custom_favicon' => array(
        "tab" => "general_plugin_tab",
        "name" => "chromatic_custom_favicon",
        "title" => "Favicon",
        "description" => __( "Use a transparent png or ico image", "sell_media" ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "image",
        "default" => ""
    ),

    'font' => array(
        "tab" => "general_plugin_tab",
        "name" => "font",
        "title" => "Headline Font",
        "description" => __( '<a href="' . get_option('siteurl') . '/wp-admin/admin-ajax.php?action=fonts&font=header&height=600&width=640" class="thickbox">Preview and choose a font</a>', "sell_media" ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "Abel:400",
        "valid_options" => sell_media_plugin_font_array()
    ),

    'font_alt' => array(
        "tab" => "general_plugin_tab",
        "name" => "font_alt",
        "title" => "Body Font",
        "description" => __( '<a href="' . get_option('siteurl') . '/wp-admin/admin-ajax.php?action=fonts&font=body&height=600&width=640" class="thickbox">Preview and choose a font</a>', "sell_media" ),
        "section" => "general_plugin_section_1",
        "since" => "1.0",
        "id" => "general_plugin_section_1",
        "type" => "select",
        "default" => "Lora:400,700,400italic",
        "valid_options" => sell_media_plugin_font_array()
    ),
    'chromatic_alt_css' => array(
        "tab" => "colors_plugin_tab",
        "name" => "chromatic_alt_css",
        "title" => "Color",
        "description" => __( "Select a color palette", "sell_media" ),
        "section" => "colors_plugin_section_1",
        "since" => "1.0",
        "id" => "colors_plugin_section_1",
        "type" => "select",
        "default" => "",
        "valid_options" => array(
            "default" => array(
                "name" => "default",
                "title" => __( "Default", "sell_media" )
            ),
            "dark" => array(
                "name" => "dark",
                "title" => __( "Dark", "sell_media" )
            )
        )
    ),

    "css" => array(
        "tab" => "colors_plugin_tab",
        "name" => "css",
        "title" => "Custom CSS",
        "description" => __( "Add some custom CSS to your theme.", "sell_media" ),
        "section" => "colors_plugin_section_1",
        "since" => "1.0",
        "id" => "colors_plugin_section_1",
        "type" => "textarea",
        "sanitize" => "html",
        "default" => ""
    )

);

sell_media_register_plugin_options( $options );
