<?php

/**
 * Settings
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

/**
 * Init our settings
 * @since 1.8.5
 */
function sell_media_init_settings() {
	$settings = sell_media_get_plugin_options();

	/**
	 * Define the Tabs appearing on the Theme Options page
	 * Tabs contains sections
	 * Options are assigned to both Tabs and Sections
	 * See README.md for a full list of option types
	 */

	// General Tab
	$general_settings_tab = array(
		"name" => "sell_media_general_settings",
		"title" => __( "General", "sell_media" ),
		"sections" => array(
			"general_plugin_section_1" => array(
				"name" => "general_plugin_section_1",
				"title" => __( "General", "sell_media" ),
				"description" => ""
			)
		)
	);
	sell_media_register_plugin_option_tab( apply_filters( 'sell_media_general_tab', $general_settings_tab ) );


	// Size & Price Tab
	$size_price_tab = array(
		"name" => "sell_media_size_settings",
		"title" => __( "Pricing", "sell_media" ),
		"sections" => array(
			"size_price_plugin_section_1" => array(
				"name" => "size_price_plugin_section_1",
				"title" => __( "Pricing", "sell_media" ),
				"description" => ""
			)
		)
	);
	sell_media_register_plugin_option_tab( apply_filters('sell_media_size_price_tab', $size_price_tab) );


	// Payment Tab
	$payment_tab = array(
		"name" => "sell_media_payment_settings",
		"title" => __("Payment","sell_media"),
		"sections" => array(
			"payment_section_1" => array(
				"name" => "payment_section_1",
				"title" => __("Payment","sell_media"),
				"description" => ""
				)
			)
		);
	sell_media_register_plugin_option_tab( apply_filters('sell_media_payment_tab', $payment_tab) );


	// Email Tab
	$email_tab = array(
		"name" => "email_plugin_tab",
		"title" => __("Email","sell_media"),
		"sections" => array(
			"email_section_1" => array(
				"name" => "email_section_1",
				"title" => __("Email Settings","sell_media"),
				"description" => ""
				)
			)
		);
	sell_media_register_plugin_option_tab( $email_tab );


	// Misc Tab
	$misc_tab = array(
		"name" => "misc_plugin_tab",
		"title" => __("Misc","sell_media"),
		"sections" => array(
			"misc_section_1" => array(
				"name" => "misc_section_1",
				"title" => __("Misc","sell_media"),
				"description" => ""
				)
			)
		);
	sell_media_register_plugin_option_tab( apply_filters( 'sell_media_misc_tab', $misc_tab ) );



	/**
	 * The following example shows you how to register theme options and assign them to tabs and sections:
	 */
	$options = array(
		// General Tab
		"test_mode" => array(
			"tab" => "sell_media_general_settings",
			"name" => "test_mode",
			"title" => __( "Mode","sell_media" ),
			"description" => sprintf( __( "Select Live Mode to accept real transactions. Select Test Mode to accept test transactions (%s).", "sell_media" ), "<a href='https://developer.paypal.com/' target='_blank'>PayPal sandbox account required</a>" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 1,
			"valid_options" => array(
				1 => array(
					'name' => 1,
					'title' => __( 'Test Mode', 'sell_media' ),
					),
				0 => array(
					'name' => 0,
					'title' => __( 'Live Mode', 'sell_media' )
					)
			)
		),
		"checkout_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "checkout_page",
			"title" => __("Checkout Page","sell_media"),
			"description" => __( "Select the page that contains the checkout shortcode <code>[sell_media_checkout]</code>", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"thanks_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "thanks_page",
			"title" => __("Thanks Page","sell_media"),
			"description" => __( "Select the page that contains the thanks shortcode <code>[sell_media_thanks]</code>", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"dashboard_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "dashboard_page",
			"title" => __("Dashboard Page","sell_media"),
			"description" => __( "Select the page that contains the dashboard shortcode<code>[sell_media_download_list]</code>", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"login_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "login_page",
			"title" => __("Login Page","sell_media"),
			"description" => __( "Select the page that contains the login shortcode <code>[sell_media_login_form]</code>", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"search_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "search_page",
			"title" => __("Search Page","sell_media"),
			"description" => __( "Select the page that contains the search shortcode <code>[sell_media_searchform]</code>", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"lightbox_page" => array(
			"tab" => "sell_media_general_settings",
			"name" => "lightbox_page",
			"title" => __("Lightbox Page","sell_media"),
			"description" => __( "Select the page that contains the lightbox shortcode <code>[sell_media_lightbox]</code> or select \"None\" to disable the lightbox functionality altogether.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => '',
			"valid_options" => sell_media_pages_options()
		),
		"customer_notification" => array(
			"tab" => "sell_media_general_settings",
			"name" => "customer_notification",
			"title" => __("Customer Notification","sell_media"),
			"description" => __( "Notify the customer of their site registration.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 1,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"style" => array(
			"tab" => "sell_media_general_settings",
			"name" => "style",
			"title" => __("Style","sell_media"),
			"description" => __( "Choose the style of your theme. Sell Media will load styles to match your theme.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => "light",
			"valid_options" => array(
				"dark" => array(
					"name" => "dark",
					"title" => __("Dark","sell_media")
					),
				"light" => array(
					"name" => "light",
					"title" => __("Light","sell_media")
					)
			)
		),
		"layout" => array(
			"tab" => "sell_media_general_settings",
			"name" => "layout",
			"title" => __("Page Layout","sell_media"),
			"description" => __("Select your layout preference for single Sell Media entries. If your theme already has a sidebar, you probably want to select the 'One column' option.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => "sell-media-single-one-col",
			"valid_options" => array(
				"two_col" => array(
					"name" => "sell-media-single-two-col",
					"title" => __( "Two columns", "sell_media" ),
				),
				"one_col" => array(
					"name" => "sell-media-single-one-col",
					"title" => __( "One column", "sell_media" ),
				)
			)
		),
		"thumbnail_crop" => array(
			"tab" => "sell_media_general_settings",
			"name" => "thumbnail_crop",
			"title" => __("Thumbnail Crop","sell_media"),
			"description" => __("Select the crop for thumbnails appearing on archives and galleries. If you select 'Square Crop,' you might need to use the Regenerate Thumbnails plugin to resize previously uploaded images.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "2.1.2",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => "medium",
			"valid_options" => array(
				"medium" => array(
					"name" => "medium",
					"title" => __( "No Crop", "sell_media" ),
				),
				"sell_media_square" => array(
					"name" => "sell_media_square",
					"title" => __( "Square Crop", "sell_media" ),
				)
			)
		),
		"thumbnail_layout" => array(
			"tab" => "sell_media_general_settings",
			"name" => "thumbnail_layout",
			"title" => __("Thumbnail Layout","sell_media"),
			"description" => __("Select the layout preferences for thumbnails appearing on archives and in galleries.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "2.1.2",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => "sell-media-three-col",
			"valid_options" => array(
				"sell-media-one-col" => array(
					"name" => "sell-media-one-col",
					"title" => __( "One Column", "sell_media" ),
				),
				"sell-media-two-col" => array(
					"name" => "sell-media-two-col",
					"title" => __( "Two Columns", "sell_media" ),
				),
				"sell-media-three-col" => array(
					"name" => "sell-media-three-col",
					"title" => __( "Three Columns", "sell_media" ),
				),
				"sell-media-four-col" => array(
					"name" => "sell-media-four-col",
					"title" => __( "Four Columns", "sell_media" ),
				),
				"sell-media-five-col" => array(
					"name" => "sell-media-five-col",
					"title" => __( "Five Columns", "sell_media" ),
				),
				"sell-media-masonry" => array(
					"name" => "sell-media-masonry",
					"title" => __( "Masonry Layout", "sell_media" ),
				),
				"sell-media-horizontal-masonry" => array(
					"name" => "sell-media-horizontal-masonry",
					"title" => __( "Horizontal Masonry Layout", "sell_media" ),
				),
			)
		),
		"titles" => array(
			"tab" => "sell_media_general_settings",
			"name" => "titles",
			"title" => __("Titles","sell_media"),
			"description" => __( "Show product titles on archives.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 0,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"breadcrumbs" => array(
			"tab" => "sell_media_general_settings",
			"name" => "breadcrumbs",
			"title" => __("Breadcrumbs","sell_media"),
			"description" => __( "Show breadcrumb navigation on single entries.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 1,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"quick_view" => array(
			"tab" => "sell_media_general_settings",
			"name" => "quick_view",
			"title" => __("Quick View","sell_media"),
			"description" => __( "Show a quick view image overlay on archives.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 1,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"file_info" => array(
			"tab" => "sell_media_general_settings",
			"name" => "file_info",
			"title" => __("File Info","sell_media"),
			"description" => __( "Show file information (file size, file type, dimensions, keywords, etc.) on single entries.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 0,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"plugin_credit" => array(
			"tab" => "sell_media_general_settings",
			"name" => "plugin_credit",
			"title" => __("Plugin Credit","sell_media"),
			"description" => __( "Let your site visitors know you are using the Sell Media plugin?", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => 0,
			"valid_options" => array(
				0 => array(
					"name" => 0,
					"title" => __("No","sell_media")
					),
				1 => array(
					"name" => 1,
					"title" => __("Yes","sell_media"),
					)
			)
		),
		"post_type_slug" => array(
			"tab" => "sell_media_general_settings",
			"name" => "post_type_slug",
			"title" => __("Post Type Slug","sell_media"),
			'description' => sprintf( esc_html__( 'The post type slug creates the archive page that contains all of your products. You can change the slug here to whatever is most relevant to your store (for example &quot;photos&quot; or &quot;videos&quot;). By default, the slug is set to "items." Add this URL to your menu to create a link to your store. Currently, your products will appear at this url: %s', 'sell_media' ), '<a href="' . esc_url( get_post_type_archive_link( 'sell_media_item' ) ) . '" target="_blank">' . esc_url( get_post_type_archive_link( 'sell_media_item' ) ) . '</a>' ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "text",
			"default" => "",
			"sanitize" => "slug"
		),
		"order_by" => array(
			"tab" => "sell_media_general_settings",
			"name" => "order_by",
			"title" => __("Order By","sell_media"),
			"description" => __( "Choose the order of items for the archive pages.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "select",
			"default" => "date-desc",
			"valid_options" => array(
				"date-desc" => array(
					"name" => "date-desc",
					"title" => "Date Desc"
					),
				"date-asc" => array(
					"name" => "date-asc",
					"title" => "Date ASC",
					),
				"title-asc" => array(
					"name" => "title-asc",
					"title" => "Title ASC",
					),
				"title-desc" => array(
					"name" => "title-desc",
					"title" => "Title Desc",
					)
			)
		),
		"terms_and_conditions" => array(
			"tab" => "sell_media_general_settings",
			"name" => "terms_and_conditions",
			"title" => __("Terms and Conditions","sell_media"),
			"description" => __( "These &quot;Terms and Conditions&quot; will show up on the checkout page. Users must agree to these terms before completing their purchase.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"sanitize" => "html",
			"type" => "textarea",
			"default" => ""
		),
		"admin_columns" => array(
			"tab" => "sell_media_general_settings",
			"name" => "admin_columns",
			"title" => __("Show Columns","sell_media"),
			"description" => __( "Select the columns to show on the admin page &quot;All Products&quot; page.", "sell_media" ),
			"section" => "general_plugin_section_1",
			"since" => "1.0",
			"id" => "general_plugin_section_1",
			"type" => "checkbox",
			"default" => '',
			"valid_options" => array(
				"show_collection" => array(
					"name" => "show_collection",
					"title" => "Collections"
					),
				"show_license" => array(
					"name" => "show_license",
					"title" => "Licenses"
					),
				"show_creators" => array(
					"name" => "show_creators",
					"title" => "Creators"
					)
				)
		),
	);

		$options['default_price'] = array(
			"tab" => "sell_media_size_settings",
			"name" => "default_price",
			"title" => __("Original Price","sell_media"),
			"description" => __( "The original price of new items and bulk uploads. You can set unique prices by editing each individual item.", "sell_media" ),
			"section" => "size_price_plugin_section_1",
			"since" => "1.0",
			"id" => "size_price_plugin_section_1",
			"default" => "1",
			"sanitize" => "html",
			"type" => "text"
		);

		$options['hide_original_price'] = array(
			"tab" => "sell_media_size_settings",
			"name" => "hide_original_price",
			"title" => __("Hide Original Price","sell_media"),
			"description" => __( "Select 'Yes' to hide the original price above and rely solely on Price Groups (See below. Price Groups can only be used when selling images). Select 'No' if you're selling single file downloads or if you want to list the original price for each product. You can override this setting on a per-item basis.", "sell_media" ),
			"section" => "size_price_plugin_section_1",
			"since" => "1.0",
			"id" => "size_price_plugin_section_1",
			"type" => "select",
			"default" => "no",
			"valid_options" => array(
				"no" => array(
					"name" => "no",
					"title" => __("No","sell_media")
					),
				"yes" => array(
					"name" => "yes",
					"title" => __("Yes","sell_media"),
					)
			)
		);

	$other_options = array(
		"default_price_group" => array(
			"tab" => "sell_media_size_settings",
			"name" => "default_price_group",
			"title" => __("Default Image Price Group", "sell_media"),
			"description" => "This is the default price group that will be assigned to all newly uploaded images for sale. You can override this setting on a per-item basis.",
			"id" => "size_price_plugin_section_1",
			"section" => "size_price_plugin_section_1",
			"type" => "select",
			"default" => "",
			"valid_options" => sell_media_settings_price_group('price-group')
			),
		// Payment Tab
		"paypal_email" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "paypal_email",
			"title" => __("PayPal Email Address", "sell_media"),
			'description' => __( 'Add the email address associated with your PayPal account above.', 'sell_media' ),
			"default" => "",
			"section" => "payment_section_1",
			"since" => "1.0",
			"default" => "",
			"id" => "payment_section_1",
			"type" => "text",
			"sanitize" => "html"
			),
		"currency" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "currency",
			"title" => __("Currency","sell_media"),
			"description" => __("The currency in which you accept payment.", "sell_media"),
			"section" => "payment_section_1",
			"since" => "1.0.",
			"id" => "payment_section_1",
			"type" => "select",
			"default" => "USD",
			"valid_options" => sell_media_currencies()
			),
		"paypal_additional_test_email" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "paypal_additional_test_email",
			"title" => __("PayPal Additional Test Emails","sell_media"),
			"description" => __("This is useful when debugging PayPal. Enter a comma separated list of emails, and when a purchase is made the same email that is sent to the buyer will be sent to the recipients in the above list.","sell_media"),
			"default" => "",
			"section" => "payment_section_1",
			"since" => "1.0.",
			"id" => "payment_section_1",
			"type" => "text",
			"sanitize" => "html"
		),
		"tax" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "tax",
			"title" => __("Tax","sell_media"),
			"description" => "Check to charge tax. You must set your tax rates below.",
			"section" => "payment_section_1",
			"since" => "1.0",
			"id" => "payment_section_1",
			"default" => "",
			"type" => "checkbox",
			"valid_options" => array(
				"yes" => array(
					"name" => "yes",
					"title" => __("Yes, charge tax","sell_media")
				)
			)
		),
		"tax_display" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "tax_display",
			"title" => __( "Tax Display", "sell_media" ),
			"description" => __( "Select how you want to display taxes. Most users can leave this set to \"Exclusive\". Tax laws in certain countries, like Austrailia, require that prices of items are displayed with taxes included. If this scenerio applies to you, then select \"Inclusive\".", "sell_media" ),
			"section" => "payment_section_1",
			"since" => "1.0",
			"id" => "payment_section_1",
			"type" => "select",
			"default" => "exclusive",
			"valid_options" => array(
				"exclusive" => array(
					"name" => "exclusive",
					"title" => __( "Exclusive - Added at checkout", "sell_media" )
				),
				"inclusive" => array(
					"name" => "inclusive",
					"title" => __( "Inclusive - Added to item prices", "sell_media" )
				)
			)
		),
		"tax_rate" => array(
			"tab" => "sell_media_payment_settings",
			"name" => "tax_rate",
			"title" => __("Tax Rate","sell_media"),
			"description" => "Set your tax rates. This tax rate will be applied to all cart orders (use .05 for 5 percent, .10 for 10 percent, etc)",
			"section" => "payment_section_1",
			"since" => "1.0",
			"id" => "payment_section_1",
			"default" =>"",
			"type" => "text",
			"sanitize" => "html"
		),

		// Email
		"from_name" => array(
			"tab" => "email_plugin_tab",
			"name" => "from_name",
			"title" => __("From Name","sell_media"),
			"description" => __("The name associated with all outgoing email.","sell_media"),
			"section" => "email_section_1",
			"since" => "1.0.",
			"id" => "email_section_1",
			"type" => "text",
			"sanitize" => "html",
			"default" => get_option("blogname")
		),
		"from_email" => array(
			"tab" => "email_plugin_tab",
			"name" => "from_email",
			"title" => __("From Email","sell_media"),
			"description" => __("The email address used for all outgoing email.","sell_media"),
			"section" => "email_section_1",
			"since" => "1.0.",
			"id" => "email_section_1",
			"type" => "text",
			"sanitize" => "html",
			"default" => get_option("admin_email")
		),
		"success_email_subject" => array(
			"tab" => "email_plugin_tab",
			"name" => "success_email_subject",
			"title" => __("Email Subject","sell_media"),
			"description" => __("The email subject on successful purchase emails.","sell_media"),
			"section" => "email_section_1",
			"since" => "1.0.",
			"id" => "email_section_1",
			"type" => "text",
			"sanitize" => "html",
			"default" => __("Your Purchase","sell_media")
		),
		"success_email_body" => array(
			"tab" => "email_plugin_tab",
			"name" => "success_email_body",
			"title" => __("Email Body","sell_media"),
			"description" => __("This e-mail message is sent to your customers in case of successful and cleared payment. You can use the following email tags: {first_name}, {last_name}, {payer_email}, {download_links}, {payment_id}. These tags are replaced with the real data associated with each payment. Be sure to include the {download_links} tag, otherwise your buyers won't receive their download purchases. The {payment_id} tag can be used to display an invoice number.","sell_media"),
			"section" => "email_section_1",
			"since" => "1.0.",
			"id" => "email_section_1",
			"type" => "textarea",
			"sanitize" => "html",
			"default" => "Hi {first_name} {last_name},\n\nThanks for purchasing from my site. Here are your download links:\n\n{download_links}\n\nThanks!"
		),


		// Misc.
		"misc" => array(
			"tab" => "misc_plugin_tab",
			"name" => "misc",
			"title" => __( "Miscellaneous Settings", "sell_media" ),
			"description" => sprintf( __( "Settings for some of our %s will be shown here if active.", "sell_media" ), "<a href='https://graphpaperpress.com/extensions/sell-media/' target='_blank'>premium extensions</a>" ),
			"default" => "",
			"section" => "misc_section_1",
			"since" => "1.0.",
			"id" => "misc_section_1",
			"type" => "html",
			"valid_options" => ''
		)
	);
	$options = wp_parse_args( $other_options, $options );
	sell_media_register_plugin_options( apply_filters( 'sell_media_options', $options ) );

}
add_action( 'init', 'sell_media_init_settings' );
