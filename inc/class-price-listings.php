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
		add_action( 'admin_menu', array( $this, 'add_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'sell_media_after_options_meta_box', array( $this, 'editor_fields' ), 10, 1 );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * Check the current screen and set tabs
	 *
	 * @return void
	 */
	function current_screen() {
		global $pricelists_page;
		$screen = get_current_screen();

		// only load tabs on pricelists page and sell_media_item add/edit pages
		if ( $screen->id === $pricelists_page || $screen->id === 'sell_media_item' ) {
			$tabs = $this->get_tabs();
			$this->current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : key( $tabs );
			do_action( 'sell_media_price_listings_run', $this->current_tab );
		}
	}

	/**
	 * Add submenu for price list.
	 */
	function add_submenu() {
		global $pricelists_page;

		$pricelists_page = add_submenu_page( $this->parent_slug, __( 'Pricelists', 'sell_media' ), __( 'Pricelists', 'sell_media' ), 'manage_options', $this->menu_slug, array( $this, 'pricelists_page' ) );
		add_action( "load-{$pricelists_page}", array( $this, 'load_pricelists_page' ) );
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
	 * Content for pricelists page.
	 *
	 * @return void
	 */
	function pricelists_page() {
		$current_screen = get_current_screen();
		?>

		<div class="wrap sell-media-price-listings-wrap">
			<h2><?php _e( 'Pricelists', 'sell_media' ); ?></h2>
			<?php
			$tabs = $this->get_tabs();
			if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) {
				echo '<div class="updated" ><p>';
				_e( $tabs[ $this->current_tab ]['tab_title'] . ' pricelist updated.', 'sell_media' );
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
				<?php do_action( 'sell_media_pricelists_before_form', $this->current_tab, $url ); ?>
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
				<?php do_action( 'sell_media_pricelists_after_form', $this->current_tab, $url ); ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Load pricelists page.
	 *
	 * @return void
	 */
	function load_pricelists_page() {
		$redirect_url = admin_url( $this->parent_slug );
		do_action( 'sell_meida_load_pricelists_page', $redirect_url );
		if ( isset( $_POST["sell-media-price-list-submit"] ) && 'true' === $_POST["sell-media-price-list-submit"] ) {
			check_admin_referer( 'sell-media-price-list-page' );
			$url_parameters['page'] = $this->menu_slug;
			$url_parameters['updated'] = 'true';
			if ( isset( $_GET['tab'] ) ) {
				$url_parameters['tab'] = $_GET['tab'];
			}
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

	/**
	 * The fields shown on the add new item page
	 *
	 * @return html
	 */
	function editor_fields( $post_id ) {
		?>

		<div id="sell-media-price-group-field" class="sell-media-field">
			<label for="sell-media-price-group"><?php _e( 'Pricelist for downloads', 'sell_media' ); ?></label>
			<?php
				$args = array(
					'show_option_none' => __( 'None', 'sell_media' ),
					'option_none_value' => 0,
					'name' => 'sell_media_price_group',
					'id' => 'sell-media-price-group',
					'class' => 'sell-media-price-group',
					'taxonomy' => 'price-group',
					'hierarchical' => true,
					'depth' => 1,
					'hide_empty' => false,
					'selected' => sell_media_get_item_price_group( $post_id, 'price-group' )
				);
				wp_dropdown_categories( $args );
			?>
			<span class="desc">
				<span id="sell-media-edit-pricelist-link-wrap">
					<?php printf( __( '<a data-href="%1$s" id="">Edit</a>', 'sell_media' ), admin_url() . 'edit.php?post_type=sell_media_item&page=pricelists&term_parent=' ); ?> |
				</span>
				<?php printf( __( '<a href="%1$s">Add New</a>', 'sell_media' ), admin_url() . 'edit.php?post_type=sell_media_item&page=pricelists' ); ?></span>
		</div>
	<?php }
}
