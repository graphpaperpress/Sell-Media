<?php
/**
 * SellMediaUpdater provides license checks and updates to Sell Media addons.
 *
 * @package Sell Media
 * @author Thad Allender
 * @url https://graphpaperpress.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Sell media updater class.
 */
class SellMediaUpdater {

	/**
	 * The plugin prefix. Configured through the class's constructor.
	 *
	 * @var String  The prefix.
	 */
	private $prefix;

	/**
	 * The Graph Paper Press website url. Configured through the class's constructor.
	 *
	 * @var String  The url.
	 */
	private $home;

	/**
	 * The API endpoint. Configured through the class's constructor.
	 *
	 * @var String  The API endpoint.
	 */
	private $api_endpoint;

	/**
	 * The product id (slug) used for this product on the License Manager site.
	 * Configured through the class's constructor.
	 *
	 * @var int     The product id of the related product in the license manager.
	 */
	private $product_id;

	/**
	 * The name of the product using this class. Configured in the class's constructor.
	 *
	 * @var int     The name of the plugin using this class.
	 */
	private $product_name;

	/**
	 * The text domain of the plugin using this class.
	 * Populated in the class's constructor.
	 *
	 * @var string  The text domain of the plugin.
	 */
	private $text_domain;

	/**
	 * The absolute path to the plugin's main file. Only applicable when using the class with a plugin.
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Initializes the license manager client.
	 *
	 * @param string $product_id     The text id (slug) of the product on the license manager site.
	 * @param string $product_name   The name of the product, used for menus.
	 * @param string $text_domain    Plugin text domain, used for localizing the settings screens.
	 * @param string $plugin_file    The full path to the plugin's main file (only for plugins).
	 */
	public function __construct( $product_id, $product_name, $text_domain, $plugin_file = '' ) {

		// Store setup data.
		$this->prefix = 'gpp';
		$this->home = 'https://graphpaperpress.com';
		$this->api_endpoint = $this->home . '/api/license-manager/v1/';
		$this->product_id = $product_id;
		$this->product_name = $product_name;
		$this->text_domain = $text_domain;
		$this->type = 'plugin';
		$this->plugin_file = $plugin_file;

		// Add actions required for the class's functionality.
		// NOTE: Everything should be done through actions and filters.
		if ( is_admin() ) {
			// Add the menu screen for inserting license information.
			// add_action( 'admin_menu', array( $this, 'add_license_settings_page' ) );
			// add_action( 'admin_init', array( $this, 'add_license_settings_fields' ) );
			// Add settings tabs on admin.
			add_action( 'init', array( $this, 'register_settings' ) );

			// Add a nag text for reminding the user to save the license information.
			add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );

			// Check for updates (for plugins).
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );

			// Showing plugin information.
			add_filter( 'plugins_api', array( $this, 'plugins_api_handler' ), 10, 3 );

			// Activation and Deactivation hooks.
			add_action( 'upgrader_process_complete', array( $this, 'activation' ) );
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}
	}

	/**
	 * On plugin activation, deactive old GPP updater plugins.
	 */
	public function activation() {

		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( array( '/gpp-theme-updates/gpp-theme-updates.php', '/gpp-plugin-updates/gpp-plugin-updates.php' ) );
		$this->delete_transients();
	}

	/**
	 * On plugin deactivation, delete option and cache.
	 */
	public function deactivation() {

		delete_option( $this->get_settings_field_name() );
		$this->delete_transients();
	}

	/**
	 * Delete transients so updates work as expected.
	 */
	private function delete_transients() {

		delete_transient( $this->prefix . '_license_cache' );
		delete_transient( 'update_plugins' );
	}

	/**
	 * Register updater settings.
	 * @return void
	 */
	function register_settings() {

		// Updater Tab.
		$updater_settings_tab = array(
			'name' => 'sell_media_updater_settings',
			'title' => __( 'License', 'sell_media' ),
			'sections' => array(
				'updater_license_section_1' => array(
					'name' => 'updater_license_section_1',
					'title' => __( 'License', 'sell_media' ),
					'description' => $this->get_license_status(),
				),
			),
		);
		sell_media_register_plugin_option_tab( apply_filters( 'sell_media_updater_tab', $updater_settings_tab ) );

		// The following example shows you how to register theme options and assign them to tabs and sections.
		$options = array(
			$this->prefix . '_license_email' => array(
				'tab' => 'sell_media_updater_settings',
				'name' => $this->prefix . '_license_email',
				'title' => __( 'License E-mail Address', $this->text_domain ),
				'description' => '',
				'section' => 'updater_license_section_1',
				'since' => '1.0',
				'id' => 'updater_license_section_1',
				'type' => 'text',
				'default' => '',
				'sanitize' => 'html',
			),
			$this->prefix . 'license_key' => array(
				'tab' => 'sell_media_updater_settings',
				'name' => $this->prefix . '_license_key',
				'title' => __( 'License Key', $this->text_domain ),
				'description' => '',
				'section' => 'updater_license_section_1',
				'since' => '1.0',
				'id' => 'updater_license_section_1',
				'type' => 'text',
				'default' => '',
				'sanitize' => 'html',
			),
		);

		sell_media_register_plugin_options( apply_filters( 'sell_media_options', $options ) );

	}
	
	/**
	 * If the license has not been configured properly, display an admin notice.
	 */
	public function show_admin_notices() {
		$options = $this->get_license_key();
		$status = $this->get_license_info();
		if ( ! $options ) : ?>
			<div class="error">
				<p>
					<?php esc_html_e( 'Please enter your email and license key to enable updates to plugins from Graph Paper Press.', $this->text_domain ); ?>
					<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . $this->get_settings_page_slug() ) ); ?>">
						<?php esc_html_e( 'Complete the setup now.', $this->text_domain ); ?>
					</a>
				</p>
			</div>
		<?php elseif ( $this->is_api_error( $status ) ) : ?>
			<div class="settings-error error">
				<p>
					<?php
						printf(
							wp_kses(
								__( 'Your <a href="%1$s">license key</a> for Graph Paper Press plugins has expired or is invalid. Please <a href="%2$s" target="_blank">renew your license</a> to re-enable automatic updates.', $this->text_domain ),
								array( 'a' => array( 'href' => array(), '_target' => array(), 'class' => array() ) )
							),
							esc_url( admin_url( 'options-general.php?page=' . $this->get_settings_page_slug() ) ),
							esc_url( $this->home . '/pricing/?action=renewal' )
						);
					?>
				</p>
			</div>
		<?php else :
			return false;
		endif;
	}


	//
	// CHECKING FOR UPDATES.
	//
	/**
	 * The filter that checks if there are updates to the plugin using the WP License Manager API.
	 *
	 * @param mixed $transient 	The transient used for WordPress plugin updates.
	 *
	 * @return mixed        	The transient with our (possible) additions.
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$info = $this->is_update_available();
		if ( false !== $info ) {
			// Plugin update.
			$plugin_slug = plugin_basename( $this->plugin_file );

			$transient->response[ $plugin_slug ] = (object) array(
				'new_version' => $info->version,
				'package'     => $info->package_url,
				'slug'        => $plugin_slug,
			);
		}

		return $transient;
	}

	/**
	 * Checks the license manager to see if there is an update available for this theme.
	 *
	 * @return object|bool    If there is an update, returns the license information.
	 *                      Otherwise returns false.
	 */
	public function is_update_available() {
		$license_info = $this->get_license_info();

		if ( $this->is_api_error( $license_info ) ) {
			return false;
		}

		if ( version_compare( $license_info->version, $this->get_local_version(), '>' ) ) {
			return $license_info;
		}

		return false;
	}

	/**
	 * Calls the License Manager API to get the license information for the current product.
	 *
	 * @return object|bool   The product data, or false if API call fails.
	 */
	public function get_license_info() {

		$transient = $this->prefix . '_license_cache';

		// Get from transient cache.
		if ( ( $info = get_transient( $transient ) ) === false ) {
			print_r( $this->get_license_key() );
			$options = get_option( $this->get_settings_field_name() );
			if ( ! isset( $options['email'] ) || ! isset( $options['license_key'] ) ) {
				// User hasn't saved the license to settings yet. No use making the call.
				return false;
			}

			$info = $this->call_api(
				'info',
				array(
					'p' => $this->product_id,
					'e' => $options['email'],
					'l' => $options['license_key'],
				)
			);

			set_transient( $transient, $info, 3600 );
		}

		return $info;
	}

	/**
	 * A function for the WordPress "plugins_api" filter. Checks if the user is requesting information about the current plugin and returns its details if needed.
	 *
	 * This function is called before the Plugins API checks for plugin information on WordPress.org.
	 *
	 * @param bool|object $res       The result object, or false (= default value).
	 * @param string      $action         The Plugins API action. We're interested in 'plugin_information'.
	 * @param array       $args            The Plugins API parameters.
	 *
	 * @return object   The API response.
	 */
	public function plugins_api_handler( $res, $action, $args ) {
		if ( 'plugin_information' === $action  ) {

			// If the request is for this plugin, respond to it.
			if ( isset( $args->slug ) && $args->slug === plugin_basename( $this->plugin_file ) ) {
				$info = $this->get_license_info();

				$res = (object) array(
					'name'          => isset( $info->name ) ? $info->name : '',
					'version'       => $info->version,
					'slug'          => $args->slug,
					'download_link' => $info->package_url,

					'tested'        => isset( $info->tested ) ? $info->tested : '',
					'requires'      => isset( $info->requires ) ? $info->requires : '',
					'last_updated'  => isset( $info->last_updated ) ? $info->last_updated : '',
					'homepage'      => isset( $info->description_url ) ? $info->description_url : '',

					'sections'      => array(
						'description' => $info->description,
					),

					'banners'       => array(
						'low'  => isset( $info->banner_low ) ? $info->banner_low : '',
						'high' => isset( $info->banner_high ) ? $info->banner_high : '',
					),

					'external'      => true,
				);

				// Add change log tab if the server sent it.
				if ( isset( $info->changelog ) ) {
					$res['sections']['changelog'] = $info->changelog;
				}

				return $res;
			}
		}

		// Not our request, let WordPress handle this.
		return false;
	}


	//
	// HELPER FUNCTIONS FOR ACCESSING PROPERTIES.
	//
	/**
	 * Get name of the settings field storing all license manager settings.
	 *
	 * @return string   The name of the settings field storing all license manager settings.
	 */
	protected function get_settings_field_name() {
		return $this->prefix . '-license-settings';
	}

	/**
	 * Get slug id of the licenses settings page.
	 *
	 * @return string   The slug id of the licenses settings page.
	 */
	protected function get_settings_page_slug() {
		return $this->prefix . '-licenses';
	}

	/**
	 * Get plugin version of the local installation.
	 *
	 * @return string   The plugin version of the local installation.
	 */
	private function get_local_version() {

		$plugin_data = get_plugin_data( $this->plugin_file, false );

		return $plugin_data['Version'];
	}

	/**
	 * Get the license keys set in wp-admin.
	 *
	 * @return array $key
	 */
	private function get_license_key() {
		// First, check if configured in wp-config.php.
		$license_email = ( defined( 'GPP_LICENSE_EMAIL' ) ) ? GPP_LICENSE_EMAIL : '';
		$license_key = ( defined( 'GPP_LICENSE_KEY' ) ) ? GPP_LICENSE_KEY : '';

		// If not found, look up from database.
		if ( empty( $license_key ) || empty( $license_key ) ) {
			$settings = sell_media_get_plugin_options();
			$license_email = '';
			$license_key = '';

			if (
				! empty( $settings )
				&& isset( $settings->gpp_license_email )
				&& is_email( $settings->gpp_license_email )
				&& isset( $settings->gpp_license_key )
				&& '' !== $settings->gpp_license_key
			) {

				$license_email = $settings->gpp_license_email;
				$license_key = $settings->gpp_license_key;

			}
		}

		if ( strlen( $license_email ) > 0 && strlen( $license_key ) >= 8 ) {
			return array( 'key' => $license_key, 'email' => $license_email );
		}

		// No license key found.
		return false;
	}

	//
	// API HELPER FUNCTIONS.
	//
	/**
	 * Makes a call to the WP License Manager API.
	 *
	 * @param string $action     The API method to invoke on the license manager site.
	 * @param array  $params     The parameters for the API call.
	 *
	 * @return          array   The API response.
	 */
	private function call_api( $action, $params ) {
		$url = $this->api_endpoint . $action;

		// Append parameters for GET request.
		$url .= '?' . http_build_query( $params );

		// Send the request.
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$result = json_decode( $response_body );

		return $result;
	}

	/**
	 * Checks the API response to see if there was an error.
	 *
	 * @param mixed|object $response     The API response to verify.
	 *
	 * @return bool     True if there was an error. Otherwise false.
	 */
	private function is_api_error( $response ) {
		if ( false === $response ) {
			return true;
		}

		if ( ! is_object( $response ) ) {
			return true;
		}

		if ( isset( $response->error ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Prints the current license status.
	 * This helps users know when the license is expired on the settings page.
	 */
	private function get_license_status() {

		if ( ! $this->get_license_key() ) {
			$msg = sprintf( wp_kses( __( '<a href="%s" target="_blank">Get your license keys here</a> and paste them below to enable automatic updates.', $this->text_domain ), array( 'a' => array( 'href' => array(), 'target' => array(), 'class' => array() ) ) ), esc_url( $this->home . '/dashboard/' ) );
			return $msg;
		}

		$license_status = $this->get_license_info();
		if ( $this->is_api_error( $license_status ) ) {
			$msg = sprintf( wp_kses( __( 'Your license key for Graph Paper Press plugins has expired or is invalid. Please <a href="%s" target="_blank">renew your license</a> to re-enable automatic updates.', $this->text_domain ), array( 'a' => array( 'href' => array(), 'target' => array(), 'class' => array() ) ) ), esc_url( $this->home . '/pricing/?action=renewal' ) );
		} else {
			$msg = '<span class="dashicons dashicons-yes" style="color:green;"></span> ' . __( 'Your license is valid and your account is active.', $this->text_domain );
		}

		return $msg;
	}
}
