<?php

/**
 * Mail
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaMail {

	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->settings = sell_media_get_plugin_options();

		add_filter( 'wp_mail_from', array( &$this, 'from_email' ) );
		add_filter( 'wp_mail_from_name', array( &$this, 'from_name' ) );
	}

	/**
	 * Change the from email address to user defined setting in Sell Media or the admin_email
	 * 
	 * @return string the new email address
	 */
	public function from_email() {
		$email = ( $this->settings->from_email ) ? $this->settings->from_email : get_bloginfo( 'admin_email' );

		return $email;
	}

	/**
	 * Change the from name from WordPress to user defined setting in Sell Media or the site name
	 * @return string the new from name
	 */
	public function from_name() {
		$name = ( $this->settings->from_name ) ? $this->settings->from_name : get_bloginfo( 'name' );

    	return $name;
	}

}