<?php

/**
 *
 * This file exists to implement support for the Theme Customizer,
 * introduced in WordPress 3.4.
 *
 **/


add_action( 'customize_register', 'gpp_customize_register' );

function gpp_plugin_customize_register( $wp_customize ) {

 	// extending the field type to textarea
	class gpp_CSS_Control extends WP_Customize_Control {
		public $type = 'textarea';

		public function render_content() {
			?>
			<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
			</label>
        <?php
		}
	}

	// enable live update for default options
	$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';


	/**
	 * Globalize the variable that holds
	 * the Settings Page tab definitions
	 *
	 * @global	array	Settings Page Tab definitions
	 */

	/**
	 * Register Theme Opitons tab section in the Theme Customizer
	 *
	 * @todo Add description.
	 *
	 */
	$sectionname = "theme_options";
	$sectiontitle = __( 'Theme Options', 'gpp' );

	$wp_customize->add_section( $sectionname, array(
		'title' => $sectiontitle,
		'priority' => 35
	) );


	$gpp_options = (array) gpp_get_plugin_options();

	$gpp_option_parameters = gpp_get_plugin_option_parameters();

	foreach( $gpp_option_parameters as $option ) {

		$optionname = $option['name'];
		$optiondb = gpp_get_current_plugin_id() . "_options[$optionname]";
		$option_section_name =  $option['section'];

		if( $option['name'] == 'css' ) {

			$wp_customize->add_setting( $optiondb, array(
				'default'		=> $option['default'],
				'type'			=> 'option',
				'capabilities'	=> 'manage_theme_options',
				'transport'     => 'postMessage'
			) );

			// intercept the theme option and control it
			$wp_customize->add_control( new gpp_CSS_Control( $wp_customize, $option['name'], array(
				'label'			=> $option['title'],
				'section'		=> $sectionname,
				'settings'		=> $optiondb,
				'type'   		=> 'textarea'
			) ) );

		}

		if( 'font' == $option['name'] || 'font_alt' == $option['name'] || 'color' == $option['name'] ) {

			$wp_customize->add_setting( $optiondb, array(
				'default'		=> $option['default'],
				'type'			=> 'option',
				'capabilities'	=> 'manage_theme_options',
				'transport'     => 'postMessage'
			) );

			$wp_customize->add_control( $option['name'], array(
				'label'   	=> $option['title'],
				'section' 	=> $sectionname,
				'settings'	=> $optiondb,
				'type'    	=> $option['type'],
				'choices' 	=> gpp_extract_valid_options( $option['valid_options'] )
			) );

		}

		if( 'logo' == $option['name'] ) {

			$wp_customize->add_setting( $optiondb, array(
				'default'		=> $option['default'],
				'type'			=> 'option',
				'capabilities'	=> 'manage_theme_options'
			) );

			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $option['name'], array(
				'label'   	=> $option['title'],
				'section' 	=> $sectionname,
				'settings'	=> $optiondb
			) ) );

		}

	}

	if ( $wp_customize->is_preview() && ! is_admin() ) {
		add_action( 'wp_footer', 'gpp_customize_preview_js', 21 );
	}

}


function gpp_plugin_extract_valid_options( $options ) {
	$new_options = array();
	foreach( $options as $option ) {
		if( isset( $option['parameter'] ) && '' != $option['parameter'] ) {
			$opt =  $option['name'] . ':' . $option['parameter'];
		} else {
			$opt = $option['name'];
		}
		$new_options[ $opt ] = $option['title'];
	}
	return $new_options;
}

/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
 * Used with fonts
 *
 * @since GPP Theme Options 1.0
 */

function gpp_plugin_customize_preview_js() {
	$doc_ready_script = '
		<script type="text/javascript">
			(function($){
				wp.customize( "blogname", function( value ) {
					value.bind( function( to ) {
						$( ".site-title a" ).html( to );
					});
				});

				wp.customize( "blogdescription", function( value ) {
					value.bind( function( to ) {
						$( ".site-description" ).html( to );
					});
				});

				wp.customize( "header_textcolor", function( value ) {
					value.bind( function( to ) {
						$( ".site-title a, .site-description" ).css( "cssText", "color: " + to + " !important;" );
					});
				});

				wp.customize( "' . gpp_get_current_plugin_id() . '_options[logo]", function( value ) {
					value.bind( function( to ) {
						$( ".site-title a" ).html( "<img class=\"sitetitle\" alt=\"' . get_bloginfo( 'name' ) . '\" src=\"" + to + "\">" );
					});
				});

				wp.customize("' . gpp_get_current_plugin_id() . '_options[font]",function(value) {
					value.bind(function(to) {
						$("#fontdiv").remove();
						var googlefont = to.split(",");
						var n = googlefont[0].indexOf(":");
						googlefontfamily = googlefont[0].substring(0, n != -1 ? n : googlefont[0].length);
						$("body").append("<div id=\"fontdiv\"><link href=\"http://fonts.googleapis.com/css?family="+googlefont[0]+"\" rel=\"stylesheet\" type=\"text/css\" /><style type=\"text/css\">	h1, h2, h3, h4, h5, h6 {font-family: \""+googlefontfamily+"\";}</style></div>");
					});
				});

				wp.customize("' . gpp_get_current_plugin_id() . '_options[font_alt]",function(value) {
					value.bind(function(to) {
						$("#fontaltdiv").remove();
						var googlefont = to.split(",");
						var n = googlefont[0].indexOf(":");
						googlefontfamily = googlefont[0].substring(0, n != -1 ? n : googlefont[0].length);
						$("body").append("<div id=\"fontaltdiv\"><link href=\"http://fonts.googleapis.com/css?family="+googlefont[0]+"\" rel=\"stylesheet\" type=\"text/css\" /><style type=\"text/css\">	body, p, h2.site-description {font-family: \""+googlefontfamily+"\";}</style></div>");
					});
				});

				wp.customize( "' . gpp_get_current_plugin_id() . '_options[color]", function( value ) {
					value.bind( function( to ) {
						$( "#gpp-alt-style-css" ).attr( "href", "' . get_template_directory_uri() . '/css/" + to + ".css" );
					});
				});

				wp.customize( "' . gpp_get_current_plugin_id() . '_options[css]", function( value ) {
					value.bind( function( to ) {
						$( "#tempcss" ).remove();
						var googlefont = to.split( "," );
						$( "body" ).append( "<div id=\"tempcss\"><style type=\"text/css\">" + to + "</style></div>" );
					});
				});

			})( jQuery );
		</script>';

	echo $doc_ready_script;
}