<?php
/**
 * Cart class.
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart class.
 */
class SellMediaCart {
	/**
	 * Cart id/ name.
	 *
	 * @var string
	 */
	private $cart_id;

	/**
	 * Limit of item in cart.
	 *
	 * @var integer
	 */
	private $item_limit = 0;

	/**
	 * Limit of quantity per item.
	 *
	 * @var integer
	 */
	private $quantity_limit = 99;

	/**
	 * Cart items.
	 *
	 * @var array
	 */
	private $items = array();

	/**
	 * Cart item attributes.
	 *
	 * @var array
	 */
	private $attributes = array();

	/**
	 * Cart errors.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Initialize shopping cart.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->cart_id = 'sell_media_cart';

		// Read cart data on load.
		add_action( 'plugins_loaded', array( $this, 'read_cart_onload' ), 1 );
	}

	/**
	 * Read cart items on load.
	 *
	 * @return void
	 */
	function read_cart_onload() {
		$this->read();
	}

	/**
	 * Get errors.
	 *
	 * @return array An array of errors occured
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Get last error
	 *
	 * @return string The last error occured
	 */
	public function get_last_error() {
		return end( $this->errors );
	}

	/**
	 * Get list of items in cart.
	 *
	 * @return array An array of items in the cart.
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Set the maximum quantity per item accepted in cart.
	 *
	 * @param int $qty Quantity to set.
	 * @return boolean
	 */
	public function setQuantityLimit( $qty ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::setQuantityLimit($qty): $qty must be integer.';
			return false;
		}

		$this->quantity_limit = $qty;

		return true;
	}

	/**
	 * Set the maximum of item accepted in cart
	 *
	 * @param int $limit Item limit.
	 * @return boolean
	 */
	public function setItemLimit( $limit ) {
		if ( ! $this->isInteger( $limit ) ) {
			$this->errors[] = 'Cart::setItemLimit($limit): $limit must be integer.';
			return false;
		}

		$this->item_limit = $limit;

		return true;
	}

	/**
	 * Add an item to cart.
	 *
	 * @param int   $id    An unique ID for the item.
	 * @param int   $price Price of item.
	 * @param int   $qty   Quantity of item.
	 * @param array $attrs Item attributes.
	 * @return boolean
	 */
	public function add( $id, $price = 0, $qty = 1, $attrs = array() ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::add($qty): $qty must be integer.';
			return false;
		}

		if ( $this->item_limit > 0 && count( $this->items ) >= $this->item_limit ) {
			$this->clear(); }

		$cart_item_id = ( isset( $attrs['item_attachment'] ) && '' !== $attrs['item_attachment'] )? $id . '_' . $attrs['item_attachment'] : $id;
		$cart_item_id = ( isset( $attrs['item_license'] ) && '' !== $attrs['item_license'] )? $cart_item_id . '_' . $attrs['item_license'] : $cart_item_id;
		$cart_item_id = ( isset( $attrs['item_pgroup'] ) && '' !== $attrs['item_pgroup'] )? $cart_item_id . '_' . $attrs['item_pgroup'] : $cart_item_id;

		// Add product id.
		$this->items[ $cart_item_id ]['item_id'] = $id;

		// Add quantity.
		$this->items[ $cart_item_id ]['qty'] = (isset( $this->items[ $cart_item_id ]['qty'] )) ? ($this->items[ $cart_item_id ]['qty'] + $qty) : $qty;
		$this->items[ $cart_item_id ]['qty'] = ($this->items[ $cart_item_id ]['qty'] > $this->quantity_limit) ? $this->quantity_limit : $this->items[ $cart_item_id ]['qty'];

		// Add product price.
		$this->items[ $cart_item_id ]['price'] = $price;

		foreach ( $attrs as $key => $attr ) {
			$this->items[ $cart_item_id ][ $key ] = $attr;
		}

		$this->write();
		return true;
	}

	/**
	 * Add extra attributes to item in cart.
	 *
	 * @param integer $id ID of targeted item.
	 * @param string  $key Name of the attribute.
	 * @param string  $value Value of the attribute.
	 * @return boolean Result as true/false
	 */
	public function setAttribute( $id, $key = '', $value = '' ) {
		if ( ! isset( $this->items[ $id ] ) ) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Item #' . $id . ' does not exist.';
			return false;
		}

		if ( empty( $key ) || empty( $value ) ) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Invalid value for $key or $value.';
			return false;
		}

		$this->attributes[ $id ][ $key ] = $value;
		$this->write();

		return true;
	}

	/**
	 * Remove an attribute from an item.
	 *
	 * @param integer $id ID of targeted item.
	 * @param string  $key Name of the attribute.
	 * @return void
	 */
	public function unsetAttribute( $id, $key ) {
		unset( $this->attributes[ $id ][ $key ] );
	}

	/**
	 * Get item attribute by key.
	 *
	 * @param integer $id ID of targeted item.
	 * @param string  $key Name of the attribute.
	 * @return string Value of the attribute
	 */
	public function getAttribute( $id, $key ) {
		if ( ! isset( $this->attributes[ $id ][ $key ] ) ) {
			$this->errors[] = 'Cart::getAttribute($id, $key): The attribute does not exist.';
			return false;
		}

		return $this->attributes[ $id ][ $key ];
	}

	/**
	 * Update item quantity.
	 *
	 * @param  int   $cart_item_id  ID of targed item.
	 * @param  int   $qty          Quantity.
	 * @param  array $attr         Attributes of item.
	 * @return boolean
	 */
	public function update( $cart_item_id, $qty, $attr = array() ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::update($cart_item_id, $qty): $qty must be integer.';
			return false;
		}

		if ( $qty < 1 ) {
			return $this->remove( $cart_item_id );
		}

		// Update quantity.
		$this->items[ $cart_item_id ]['qty'] = ($qty > $this->quantity_limit) ? $this->quantity_limit : $qty;

		$this->write();

		return true;
	}

	/**
	 * Get cart qty.
	 *
	 * @return int
	 */
	public function getQty() {
		$items = $this->items;

		if ( empty( $items ) ) {
			return 0;
		}

		$qty = 0;
		foreach ( $items as $key => $item ) {
			if ( ! empty( $item['qty'] ) ) {
				$qty += $item['qty'];
			}
		}
		return (int) $qty;
	}

	/**
	 * Get cart subtotal.
	 *
	 * @param  boolean $formatted Get Formated subtotal.
	 * @return mixed
	 */
	public function getSubtotal( $formatted = true ) {
		$items = $this->items;
		if ( empty( $items ) ) {
			return 0;
		}
		$subtotal = 0;
		foreach ( $items as $key => $item ) {
			$subtotal += $item['price'] * $item['qty'];
		}
		if ( $formatted ) {
			return number_format( $subtotal, 2 );
		}

		return sprintf( '%0.2f', $subtotal );
	}

	/**
	 * Remove item from cart.
	 *
	 * @param integer $id ID of targeted item.
	 */
	public function remove( $id ) {
		unset( $this->items[ $id ] );
		unset( $this->attributes[ $id ] );

		$this->write();
	}

	/**
	 * Clear all items in the cart.
	 */
	public function clear() {
		$this->items = array();
		$this->attributes = array();
		$this->write();
	}

	/**
	 * Wipe out cart session and cookie.
	 */
	public function destroy() {
		$this->items = array();
		$this->attributes = array();
		$this->write();
	}

	/**
	 * Check if a string is integer.
	 *
	 * @param string $int String to validate.
	 * @return boolean
	 */
	private function isInteger( $int ) {
		return preg_match( '/^[0-9]+$/', $int );
	}

	/**
	 * Read items from cart session.
	 */
	private function read() {
		$cart_attributes_session_name = $this->cart_id . '_attributes';
		$cart_items = Sell_Media()->session->get( $this->cart_id );
		$list_attribute = Sell_Media()->session->get( $cart_attributes_session_name );
		if ( ! empty( $cart_items ) ) {
			foreach ( $cart_items as $id => $item ) {
				if ( empty( $item ) ) {
					continue;
				}
				$this->items[ $id ] = $item;
			}
		}

		$attributes = @explode( ';', $list_attribute );
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				if ( ! strpos( $attribute, ',' ) ) {
					continue;
				}
				list($id, $key, $value) = @explode( ',', $attribute );
				$this->attributes[ $id ][ $key ] = $value;
			}
		}
	}

	/**
	 * Write changes to cart session.
	 */
	private function write() {
		$cart_attributes_session_name = $this->cart_id . '_attributes';
		$items = array();

		$total_cart_qty = 0;
		foreach ( $this->items as $id => $item ) {
			if ( ! $id ) {
				continue;
			}

			$items[ $id ] = $item;
			$total_cart_qty += $item['qty'];
		}

		$cart_items = Sell_Media()->session->set( $this->cart_id, $items );

		$attributes = '';
		foreach ( $this->attributes as $id => $attributes ) {
			if ( ! $id ) {
				continue; }

			foreach ( $attributes as $key => $value ) {
				$attributes .= $id . ',' . $key . ',' . $value . ';';
			}
		}

		$cart_attributes = Sell_Media()->session->set( $cart_attributes_session_name, rtrim( $attributes, ';' ) );

		$sell_media_cart_info['qty'] = $total_cart_qty;
		$sell_media_cart_info['subtotal'] = $this->getSubtotal();

		// Cookie data to enable data info in js.
		setcookie( 'sell_media_cart_info', wp_json_encode( $sell_media_cart_info ), time() + 604800, '/' );
	}
}
