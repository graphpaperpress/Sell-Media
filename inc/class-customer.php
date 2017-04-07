<?php

/**
 * Customer Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaCustomer {

	private $settings;

	public function __construct() {
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
	public function insert( $email = null, $first_name = null, $last_name = null ) {

		$email = sanitize_email( $email );

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) || username_exists( $email ) || email_exists( $email ) ) {
			return;
		}

		if ( $email ) {

			$password = wp_generate_password( 16, false );

			$userdata = array(
				'user_login'    => $email,
				'user_email'    => $email,
				'first_name'    => $first_name,
				'last_name'     => $last_name,
				'role'          => 'sell_media_customer',
				'user_pass'     => $password, // When creating an user, `user_pass` is expected.
			);

			// add the user
			$user_id = wp_insert_user( $userdata );

			// email the user with password request
			if ( ! is_wp_error( $user_id ) ) {
				$user_info = get_userdata( $user_id );
				do_action( 'wp_signon', $user_info->user_login );
				$secure_cookie = ( is_ssl() ) ? true : false;
				wp_set_auth_cookie( $user_id , true, $secure_cookie );
				wp_set_current_user( $user_id );
				wp_new_user_notification( $user_id, null, $notify = 'both' );

				// hook for when new users are created
				do_action( 'sell_media_after_insert_user', $user_id, $email, $first_name, $last_name );

				return true;
			}
		}
		return false;
	}
}
