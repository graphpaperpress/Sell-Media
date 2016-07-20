<?php

/**
 * Customer Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

Class SellMediaCustomer {

	private $settings;

	public function __construct(){
		$this->settings = sell_media_get_plugin_options();
	}

	/**
	* Insert a new customer
	*
	* @param $email (string)
	* @param $first_name (string)
	* @param $last_name (string)
	* @return $user_id (int)
	*
	*/
	public function insert( $email=null, $first_name=null, $last_name=null ){

		if ( $email && email_exists( $email ) == false ) {
			$userdata = array(
				'user_login'    => $email,
				'user_email'    => $email,
				'first_name'    => $first_name,
				'last_name'     => $last_name,
				'role'          => 'sell_media_customer',
				'user_pass'     => NULL // When creating an user, `user_pass` is expected.
			);

			// add the user
			$user_id = wp_insert_user( $userdata );

			// email the user with password request
			if ( ! is_wp_error( $user_id ) ) {
				wp_new_user_notification( $user_id, null, $notify = 'both' );

				// log the user in automatically
				// $this->signon( $email, $password );

				// hook for when new users are created
				do_action( 'sell_media_after_insert_user', $user_id, $email, $first_name, $last_name );
			}

			return false;
		}
	}

	/**
	* Auto login user
	*
	* @param $user_id
	* @return (bool)
	* @todo get password, automatically log user in
	*
	*/
	public function signon( $email=null, $password=null ){

		if ( ! is_user_logged_in() && email_exists( $email ) ) {

			$creds = array();
			$creds['user_login'] = $email;
			$creds['user_password'] = $password;
			$creds['remember'] = true;
			$user = wp_signon( $creds, false );

			if ( is_wp_error( $user  ) ) {
				return;
			} else {
				wp_set_current_user( $user->ID, $user->user_login );
				wp_set_auth_cookie( $user->ID, true, false );
				do_action( 'wp_login', $user->user_login );
			}
		}
	}

}