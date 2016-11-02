<?php

/**
 * Collections
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collections
 *
 * @since 1.8.5
 * @return void
 */
function sell_media_collection_password_check( $query ) {

	if ( is_admin() || ! $query->is_main_query() ) {
		return $query;
	}

	// JetPack Infinite Scroll fix
	if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'infinite-scroll' ) ) {
		return $query;
	}

	if ( ! empty( $_GET['sell_media_advanced_search_flag'] ) ) {
		return;
	}

	/**
	 * Check if "collections" is present in query vars
	 */
	if ( ! empty( $query->query_vars['collection'] ) ) {
		$term_obj = get_term_by( 'slug', $query->query_vars['collection'], 'collection' );
		if ( $term_obj ) {
			$term_id = $term_obj->term_id;
		}
	}

	/**
	 * Check if this is a single sell_media_item page
	 * note is_singular('sell_media_item') does not work here
	 */
	elseif ( is_single() && ! empty( $query->query['post_type'] )
		&& 'sell_media_item' == $query->query['post_type']
		&& ! empty( $query->query['sell_media_item'] ) ) {
		global $wpdb;

		/**
		 * build an array of terms that are password protected
		 */
		foreach ( get_terms( 'collection' ) as $term_obj ) {
			$password = get_term_meta( $term_obj->term_id, 'collection_password', true );
			if ( $password ) {
				$exclude_term_ids[] = $term_obj->term_id;
			}
		}

		/**
		 * Apparently none of our globals are set and the post_id is not in $query
		 * so we run this query to get our post_id
		 */
		$post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE `post_name` LIKE '{$query->query['sell_media_item']}' AND post_type LIKE 'sell_media_item';");

		/**
		 * Determine if this post has the given term and the term has a password
		 * if it does we set our term_id to the password protected term
		 */
		if ( ! empty( $exclude_term_ids ) ) {
			foreach ( $exclude_term_ids as $t ) {
				if ( has_term( $t, 'collection', $post_id ) && get_term_meta( $t, 'collection_password', true ) ) {
					$term_id = $t;
					$message = __( 'This item is password protected', 'sell_media' );
				}
			}
		}
	}

	/**
	 * Filter out posts that are in password protected collections from our archive pages
	 * We need to check additional post_type since this will pass as true for nav_menu_item
	 */
	elseif ( is_post_type_archive( 'sell_media_item' )
		&& ! empty( $query->query['post_type'] ) && 'sell_media_item' == $query->query['post_type']
		|| is_home()
		|| is_tax()
		|| is_page()
		|| is_single()
		) {

		/**
		 * build an array of terms that are password protected
		 */
		foreach ( get_terms( 'collection' ) as $term_obj ) {
			$password = get_term_meta( $term_obj->term_id, 'collection_password', true );
			if ( $password ) {
				$exclude_term_ids[] = $term_obj->term_id;
			}
		}

		if ( ! empty( $exclude_term_ids ) ) {
			// echo 'exclude these ids: ';
			$tax_query = array(
					 'relation' => 'AND',
					 array(
						 'taxonomy' => 'collection',
						 'field' => 'id',
						 'terms' => $exclude_term_ids,
						 'operator' => 'NOT IN',
						 )
					 );
		}

		if ( isset( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}
	}

	/**
	 * Just set our term_id to null.
	 */
	else {
		$term_id = null;
	}

	/**
	 * If we have a term ID check if this term is password protected
	 */
	if ( ! empty( $term_id ) ) {

		/**
		 * get the password for the collection
		 */
		$password = get_term_meta( $term_id, 'collection_password', true );
		if ( empty( $password ) ) {
			$child_term = get_term( $term_id, 'collection' );
			$parent_term = get_term( $child_term->parent, 'collection' );
			if ( ! empty( $parent_term->term_id ) ) {
				$password = get_term_meta( $parent_term->term_id, 'collection_password', true );
			} else {
				$password = null;
			}
		}

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		/**
		 * Since we do not have a "logout link" and can't rely on
		 * "garbage collection", we end our session after 30 minutes.
		 */
		if ( isset( $_SESSION['sell_media']['recent_activity'] ) &&
			( time() - $_SESSION['sell_media']['recent_activity'] > ( 30 * 60 ) ) ) {
			session_destroy();
			session_unset();
		}
		$_SESSION['sell_media']['recent_activity'] = time(); // the start of the session.

		if ( ! empty( $password ) ) {
			if ( ! empty( $_POST['collection_password'] ) && $_POST['collection_password'] == $password
				|| ! empty( $_SESSION['sell_media']['collection_password'][$term_id] )
				|| ! empty( $_SESSION['sell_media']['collection_password'][$term_id] )
				&& $_SESSION['sell_media']['collection_password'][$term_id] == $password ) {

				if ( empty( $_SESSION['sell_media']['collection_password'][$term_id] ) ) 
					$_SESSION['sell_media']['collection_password'][$term_id] = $_POST['collection_password'];

				return $query;
			} else {
				$custom = locate_template( 'collection-password.php' );
				if ( empty( $custom ) ) {
					load_template( SELL_MEDIA_PLUGIN_DIR . '/themes/collection-password.php' );
					exit();
				} else {
					load_template( $custom );
				}
			}
		}
	} else {
		return $query;
	}
}
add_action( 'pre_get_posts', 'sell_media_collection_password_check' );
