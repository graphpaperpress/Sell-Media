<?php

/**
 * Tax migrate Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SellMediaTaxMetaMigrate {

	/**
	 * Class constructer
	 */
	public function __construct() {
	}

	/**
	 * Run migration process
	 */
	public function run() {
		$version = sell_media_version();

		if ( $version && $version > SELL_MEDIA_VERSION ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'taxonomymeta';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			return;
		}

		if ( is_multisite() ) {
			$sites = wp_get_sites();
			if ( ! empty( $sites ) ) {
				foreach ( $sites as $key => $site ) {
					$table_prefix = $this->table_prefix( absint( $site['blog_id'] ) );
					$migrate = $this->migrate( $table_prefix );
				}
			}
		} else {
			$table_prefix = $this->table_prefix();
			$migrate = $this->migrate( $table_prefix );
		}
	}

	private function migrate( $table_prefix ) {
		if ( '' === $table_prefix ) {
			return false;
		}

		global $wpdb;
		$metas = $this->get_all_meta( $table_prefix );
		$drop_table = true;

		if ( ! empty( $metas ) ) {
			foreach ( $metas as $key => $meta ) {
				$result = $wpdb->insert(
					$table_prefix . 'termmeta',
					array(
						'term_id' => $meta->taxonomy_id,
						'meta_key' => $meta->meta_key,
						'meta_value' => $meta->meta_value,
					),
					array(
						'%d',
						'%s',
						'%s',
					)
				);

				if ( false === $result ) {
					$drop_table = false;
				}
			}
		}

		if ( $drop_table ) {
			$this->delete_meta_table( $table_prefix );
		}
	}

	/**
	 * Get old tax metas.
	 * @return object Old tax metas
	 */
	private function get_all_meta( $table_prefix ) {
		global $wpdb;
		$sql = 'SELECT * FROM `' . $table_prefix . 'taxonomymeta`';
		$metas = $wpdb->get_results( $sql );
		return $metas;
	}

	/**
	 * Delete old meta table.
	 * @return mixed Query output.
	 */
	private function delete_meta_table( $table_prefix ) {
		global $wpdb;
		$sql = 'DROP TABLE `' . $table_prefix . 'taxonomymeta`';
		return $wpdb->query( $sql );
	}

	private function table_prefix( $blog_id = null ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$base_prefix = $wpdb->base_prefix;

		if ( ! is_multisite() || is_null( $blog_id ) ||  1 === $blog_id ) {
			return $prefix;
		}

		$prefix .= $blog_id . '_';
		return $prefix;
	}
}
