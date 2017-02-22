<?php
/**
 * Wrapper for WP Session Manager.
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Session Manager wraper.
 */
class SellMediaSession {
	/**
	 * Holds session data.
	 *
	 * @var array
	 */
	private $session;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Let users change the session cookie name.
		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'sell_media_session' );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			include sprintf( '%s/inc/libraries/wp-session/class-recursive-arrayaccess.php', plugin_dir_path( dirname( __FILE__ ) ) );
		}

		if ( ! class_exists( 'WP_Session_Utils' ) ) {
			include sprintf( '%s/inc/libraries/wp-session/class-wp-session-utils.php', plugin_dir_path( dirname( __FILE__ ) ) );
		}

		if ( ! class_exists( 'WP_Session' ) ) {
			include sprintf( '%s/inc/libraries/wp-session/class-wp-session.php', plugin_dir_path( dirname( __FILE__ ) ) );
			include sprintf( '%s/inc/libraries/wp-session/wp-session.php', plugin_dir_path( dirname( __FILE__ ) ) );
		}

		if ( empty( $this->session ) ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}
	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since 1.5
	 * @return array
	 */
	public function init() {
		$this->session = WP_Session::get_instance();
		return $this->session;
	}

	/**
	 * Get session data.
	 *
	 * @param  string $key session data key.
	 * @return mixed      session data.
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set data in session.
	 *
	 * @param string $key   session data key.
	 * @param mixed  $value  session data.
	 * @return mixed
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );
		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		return $this->session[ $key ];
	}
}
