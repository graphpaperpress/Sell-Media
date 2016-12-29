<?php
/**
 * Create price listing page.
 *
 * @package Sell Media
 */

/**
 * Sell Media price listings class.
 */
class Sell_Media_Price_Listings {
	/**
	 * Parent slug.
	 *
	 * @var string
	 */
	private $parent_slug = 'edit.php?post_type=sell_media_item';

	/**
	 * Price list slug.
	 *
	 * @var string
	 */
	private $menu_slug = 'pricelists';

	/**
	 * Current tab.
	 *
	 * @var string
	 */
	private $current_tab;

	/**
	 * Constructor method.
	 */
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the functionality.
	 *
	 * @return void
	 */
	function init() {
		$tabs = $this->get_tabs();
		$this->current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $tabs );
		do_action( 'sell_media_price_listings_run', $this->current_tab );
		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add submenu for price list.
	 */
	function add_submenu() {
		$settings_page = add_submenu_page( $this->parent_slug, __( 'Pricelists', 'sell_media' ), __( 'Pricelists', 'sell_media' ), 'manage_options', $this->menu_slug, array( $this, 'settings_page' ) );
		add_action( "load-{$settings_page}", array( $this, 'load_settings_page' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		$current_screen = get_current_screen();
		if ( ! isset( $current_screen->id ) || 'sell_media_item_page_' . $this->menu_slug !== $current_screen->id ) {
			return;
		}
		wp_enqueue_script( 'sell-media-parsley', plugins_url( 'js/parsley.min.js' , dirname( __FILE__ ) ), array( 'jquery' ), false, true );
		wp_enqueue_script( 'sell-media-price-listings', plugins_url( 'js/sell-media-price-listings.js' , dirname( __FILE__ ) ), array( 'jquery', 'sell-media-parsley', 'wp-util' ), false, true );
		$translation_array = apply_filters( 'sell_media_price_listings_localize_data', array() );
		wp_localize_script( 'sell-media-price-listings', 'sell_media_price_listings', $translation_array );

		wp_enqueue_style( 'sell-media-price-listings', plugins_url( 'css/sell_media_price_listings.css', dirname( __FILE__ ) ) );
	}
	/**
	 * Content for setting page.
	 *
	 * @return void
	 */
	function settings_page() {
		$current_screen = get_current_screen();
		?>

		<div class="wrap sell-media-price-listings-wrap">
			<h2><?php _e( 'Pricelists', 'sell_media' ); ?></h2>
			<?php
			$tabs = $this->get_tabs();
			if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
				echo '<div class="updated" ><p>';
				_e( $tabs[ $this->current_tab ]['tab_title'] . ' price lists are updated successfully.', 'sell_media' );
				echo '</p></div>';
			}

			$this->display_tabs( $this->current_tab );
			$url_parameters['page'] = $this->menu_slug;
			if ( isset( $_GET['tab'] ) ) {
				$url_parameters['tab'] = $_GET['tab'];
			}
			$url = admin_url( $this->parent_slug );
			$url = add_query_arg( $url_parameters, $url );
			?>
			<div id="poststuff">
				<?php do_action( 'sell_media_pricelists_before_form' ); ?>
				<form method="post" action="<?php echo esc_url( $url ); ?>" id="sell-media-pricelist-form">
					<?php
					wp_nonce_field( 'sell-media-price-list-page' );
					if ( isset( $current_screen->parent_file ) && $this->parent_slug === $current_screen->parent_file && $_GET['page'] === $this->menu_slug ) {
						if ( isset( $tabs[ $this->current_tab ]['content_callback'] ) ) {
							call_user_func( $tabs[ $this->current_tab ]['content_callback'], $this->current_tab );
						}
					}
					?>
				</form>
				<?php do_action( 'sell_media_pricelists_after_form' ); ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Load settings page.
	 *
	 * @return void
	 */
	function load_settings_page() {
		if ( isset( $_POST["sell-media-price-list-submit"] ) && 'true' === $_POST["sell-media-price-list-submit"] ) {
			check_admin_referer( 'sell-media-price-list-page' );
			$url_parameters['page'] = $this->menu_slug;
			$url_parameters['updated'] = 'true';
			if ( isset( $_GET['tab'] ) ) {
				$url_parameters['tab'] = $_GET['tab'];
			}
			$redirect_url = admin_url( $this->parent_slug );
			$redirect_url = add_query_arg( $url_parameters, $redirect_url );
			do_action( 'sell_media_price_listing_save', $redirect_url );
			wp_redirect( $redirect_url );
			exit();
		}
	}

	/**
	 * Display tabs.
	 *
	 * @param  string $current Current active tab.
	 * @return void
	 */
	function display_tabs( $current = null ) {
		$tabs = $this->get_tabs();
		if ( empty( $tabs ) ) {
			return;
		}
		$first_tab = key( $tabs );
		$current = is_null( $current ) ? $first_tab : $current;
		$output = '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $slug => $tab ) {
			$class = ( $slug === $current ) ? ' nav-tab-active' : '';
			$url = $this->parent_slug . '&page=' . $this->menu_slug . '&tab=' . $slug;
			$output .= "<a class='nav-tab$class' href='$url'>" . $tab['tab_title'] . '</a>';
		}
		$output .= '</h2>';
		echo wp_kses_post( $output );
	}

	/**
	 * Get tab lists.
	 *
	 * @return array Lists of tabs.
	 */
	function get_tabs() {
		return apply_filters( 'sell_media_price_listing_tabs', array() );
	}
}
