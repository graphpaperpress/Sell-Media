<?php
/**
 * Sell Media Search.
 *
 * @package   SellMedia
 * @author    Thad Allender <support@graphpaperpress.com>
 * @license   GPL-2.0+
 * @link      http://graphpaperpress.com/plugins/sell-media/
 * @copyright 2016 Graph Paper Press
 */


/**
 * Class conflict with plugin with similar sql queries
 *
 * @package SellMedia
 * @author  Thad Allender <support@graphpaperpress.com>
 */
if ( class_exists( 'Media_Search_Enhanced' ) ) {
	return;
}

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package SellMedia
 * @author  Thad Allender <support@graphpaperpress.com>
 */
class SellMediaSearch {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.0.1
	 *
	 * @var     string
	 */
	const VERSION = '0.0.1';

	/**
	 * Instance of this class.
	 *
	 * @since    0.0.1
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.0.1
	 */
	public function __construct() {

		// Media Search filters
		add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 20 );

		// Add a media search form shortcode
		add_shortcode( 'sell_media_search', array( $this, 'form' ) );

		// Legacy add a media search form shortcode
		add_shortcode( 'sell_media_searchform', array( $this, 'form' ) );

		// Hook the image into the_excerpt
		add_filter( 'the_excerpt', array( $this, 'get_the_image' ) );

		// Change the permalinks at media search results page
		add_filter( 'attachment_link', array( $this, 'get_the_url' ), 10, 2 );

		// Filter the search form on search page to add post_type hidden field
		add_filter( 'get_search_form', array( $this, 'search_form_on_search' ) );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.0.1
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Set query clauses in the SQL statement
	 *
	 * @return array
	 *
	 * @since    0.0.1
	 */
	public static function posts_clauses( $pieces ) {

		global $wp_query, $wpdb;

		$vars = $wp_query->query_vars;
		if ( empty( $vars ) ) {
			$vars = ( isset( $_REQUEST['query'] ) ) ? $_REQUEST['query'] : array();
		}

		// Rewrite the where clause
		if ( ! empty( $vars['s'] ) && ( ( isset( $_REQUEST['action'] ) && 'query-attachments' == $_REQUEST['action'] ) || 'attachment' == $vars['post_type'] ) ) {
			$pieces['where'] = " AND $wpdb->posts.post_type = 'attachment' AND ($wpdb->posts.post_status = 'inherit' OR $wpdb->posts.post_status = 'private')";

			if ( class_exists( 'WPML_Media' ) ) {
				global $sitepress;
				//get current language
				$lang = $sitepress->get_current_language();
				$pieces['where'] .= $wpdb->prepare( " AND t.element_type='post_attachment' AND t.language_code = %s", $lang );
			}

			if ( ! empty( $vars['post_parent'] ) ) {
				$pieces['where'] .= " AND $wpdb->posts.post_parent = " . $vars['post_parent'];
			} elseif ( 0 === $vars['post_parent'] ) {
				// Get unattached attachments
				$pieces['where'] .= " AND $wpdb->posts.post_parent = 0";
			}

			if ( ! empty( $vars['post_mime_type'] ) ) {
				// Use esc_like to escape slash
				$like = '%' . $wpdb->esc_like( $vars['post_mime_type'] ) . '%';
				$pieces['where'] .= $wpdb->prepare( " AND $wpdb->posts.post_mime_type LIKE %s", $like );
			}

			if ( ! empty( $vars['m'] ) ) {
				$year = substr( $vars['m'], 0, 4 );
				$monthnum = substr( $vars['m'], 4 );
				$pieces['where'] .= $wpdb->prepare( " AND YEAR($wpdb->posts.post_date) = %d AND MONTH($wpdb->posts.post_date) = %d", $year, $monthnum );
			} else {
				if ( ! empty( $vars['year'] ) && 'false' != $vars['year'] ) {
					$pieces['where'] .= $wpdb->prepare( " AND YEAR($wpdb->posts.post_date) = %d", $vars['year'] );
				}

				if ( ! empty( $vars['monthnum'] ) && 'false' != $vars['monthnum'] ) {
					$pieces['where'] .= $wpdb->prepare( " AND MONTH($wpdb->posts.post_date) = %d", $vars['monthnum'] );
				}
			}

			// search for keyword "s"
			$like = '%' . $wpdb->esc_like( $vars['s'] ) . '%';
			$pieces['where'] .= $wpdb->prepare( " AND ( ($wpdb->posts.ID LIKE %s) OR ($wpdb->posts.post_title LIKE %s) OR ($wpdb->posts.guid LIKE %s) OR ($wpdb->posts.post_content LIKE %s) OR ($wpdb->posts.post_excerpt LIKE %s)", $like, $like, $like, $like, $like );
			$pieces['where'] .= $wpdb->prepare( " OR ($wpdb->postmeta.meta_key = '_wp_attachment_image_alt' AND $wpdb->postmeta.meta_value LIKE %s)", $like );
			$pieces['where'] .= $wpdb->prepare( " OR ($wpdb->postmeta.meta_key = '_wp_attached_file' AND $wpdb->postmeta.meta_value LIKE %s)", $like );

			// Get taxes for attachements
			$taxes = get_object_taxonomies( 'attachment' );
			if ( ! empty( $taxes ) ) {
				$pieces['where'] .= $wpdb->prepare( " OR (tter.slug LIKE %s) OR (ttax.description LIKE %s) OR (tter.name LIKE %s)", $like, $like, $like );
			}

			$pieces['where'] .= " )";

			$pieces['join'] .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id";

			// Get taxes for attachements
			$taxes = get_object_taxonomies( 'attachment' );
			if ( ! empty( $taxes ) ) {
				$on = array();
				foreach ( $taxes as $tax ) {
					$on[] = "ttax.taxonomy = '$tax'";
				}
				$on = '( ' . implode( ' OR ', $on ) . ' )';

				$pieces['join'] .= " LEFT JOIN $wpdb->term_relationships AS trel ON ($wpdb->posts.ID = trel.object_id) LEFT JOIN $wpdb->term_taxonomy AS ttax ON (" . $on . " AND trel.term_taxonomy_id = ttax.term_taxonomy_id) LEFT JOIN $wpdb->terms AS tter ON (ttax.term_id = tter.term_id) ";
			}

			$pieces['distinct'] = 'DISTINCT';

			$pieces['orderby'] = "$wpdb->posts.post_date DESC";
		}

		return $pieces;
	}

	/**
	 * Create search form
	 *
	 * @return string Media search form
	 *
	 * @since 0.0.1
	 */
	public function form( $form = '' ) {

		$s = get_query_var( 's' );

		$placeholder = ( empty( $s ) ) ? apply_filters( 'sell_media_search_form_placeholder', __( 'Search Media...', 'sell_media' ) ) : $s;

		if ( empty( $form ) )
			$form = get_search_form( false );

		$form = preg_replace( "/(form.*class=\")(.\S*)\"/", '$1$2 ' . apply_filters( 'sell_media_search_form_class', 'sell_media-search-form' ) . '"', $form );
		$form = preg_replace( "/placeholder=\"(.\S)*\"/", 'placeholder="' . $placeholder . '"', $form );
		$form = str_replace( '</form>', '<input type="hidden" name="post_type" value="attachment" /></form>', $form );

		$result = apply_filters( 'sell_media_search_form', $form );

		return $result;

	}

	/**
	 * Get the attachment image and hook into the_excerpt
	 *
	 * @param  string $excerpt The excerpt HTML
	 * @return string          The hooked excerpt HTML
	 *
	 * @since  0.0.1
	 */
	public function get_the_image( $excerpt ) {

		global $post;

		if ( ! is_admin() && is_search() && 'attachment' === $post->post_type ) {
			$params = array(
				'attachment_id' => $post->ID,
				'size' => apply_filters( 'sell_media_thumbnail', 'medium' ),
				'icon' => false,
				'attr' => array(),
				);
			$params = apply_filters( 'sell_media_get_attachment_image_params', $params );
			extract( $params );

			$html = '';
			$clickable = apply_filters( 'sell_media_is_image_clickable', true );
			if ( $clickable ) {
				$html .= '<a href="' . get_attachment_link( $attachment_id ) . '"';
				$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $post, $size );
				$attr = array_map( 'esc_attr', $attr );
				foreach ( $attr as $name => $value ) {
					$html .= " $name=" . '"' . $value . '"';
				}
				$html .= '>';
			}

			$html .= wp_get_attachment_image( $attachment_id, $size, $icon, $attr );

			if ( $clickable ) {
				$html .= '</a>';
			}

			$excerpt .= $html;
		}

		return $excerpt;

	}

	/**
	 * Add filter to hook into the attachment URL
	 *
	 * @param  string $link    The attachment's permalink.
	 * @param  int $post_id Attachment ID.
	 * @return string          The attachment's permalink.
	 *
	 * @since 0.0.1
	 */
	public function get_the_url( $link, $post_id ) {

		if ( ! is_admin() && is_search() ) {
			$link = apply_filters( 'sell_media_get_attachment_url', $link, $post_id );
		}

		return $link;
	}

	/**
	 * Filter the search form on search page to add post_type hidden field
	 *
	 * @param  string $form The search form.
	 * @return string The filtered search form
	 *
	 * @since 0.0.1
	 */
	public function search_form_on_search( $form ) {

		if ( is_search() && is_main_query() && isset( $_GET['post_type'] ) && 'attachment' === $_GET['post_type'] ) {
			$form = $this->form( $form );
		}

		return $form;
	}

}
